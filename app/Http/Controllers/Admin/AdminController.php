<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'weekly');
        $selectedYearId = $request->get('academic_year_id');

        $allYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeYear = $selectedYearId
            ? AcademicYear::find($selectedYearId)
            : AcademicYear::where('is_active', true)->first();

        // Pindah date range ke helper method
        [$startDate, $endDate] = $this->getDateRange($filter);

        $today = now()->toDateString();
        $daftarHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hariIni = $daftarHari[now()->dayOfWeek];

        // Keaktifan guru
        $keaktifanGuru = DB::table('teachers')
            ->leftJoin('schedules', function ($join) use ($activeYear) {
                $join->on('schedules.teacher_id', '=', 'teachers.id');

                if ($activeYear) {
                    $join->where('schedules.academic_year_id', $activeYear->id);
                }
            })
            ->leftJoin('attendances', function ($join) use ($startDate, $endDate, $activeYear) {
                $join->on('attendances.schedule_id', '=', 'schedules.id')
                    ->whereBetween('attendances.tanggal', [$startDate, $endDate]);

                if ($activeYear) {
                    $join->where('attendances.academic_year_id', $activeYear->id);
                }
            })
            ->selectRaw('
        teachers.id,
        teachers.nama_guru,
        COUNT(DISTINCT attendances.id) as total_absen,
        COUNT(DISTINCT schedules.id) as total_jadwal
    ')
            ->groupBy('teachers.id', 'teachers.nama_guru')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {

                $start = \Carbon\Carbon::parse($startDate);
                $end = \Carbon\Carbon::parse($endDate);

                $minggu = max(1, $start->diffInWeeks($end) + 1);

                $expectedPertemuan = $item->total_jadwal * $minggu;

                $persentase = $expectedPertemuan > 0
                    ? (int) min(100, round(($item->total_absen / $expectedPertemuan) * 100))
                    : 0;

                $statusLabel = match (true) {
                    $persentase >= 80 => 'Aktif',
                    $persentase >= 50 => 'Cukup',
                    default => 'Bermasalah',
                };

                $statusColor = match (true) {
                    $persentase >= 80 => 'success',
                    $persentase >= 50 => 'warning',
                    default => 'danger',
                };

                $item->persentase = $persentase;
                $item->statusLabel = $statusLabel;
                $item->statusColor = $statusColor;

                return $item;
            })
            ->sortByDesc('persentase')
            ->values();

        // ==========================
        // PAGINATION MANUAL
        // ==========================

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 10;

        $currentItems = $keaktifanGuru
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $teachers = new LengthAwarePaginator(
            $currentItems,
            $keaktifanGuru->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // ==========================
        // TOP PERFORMER
        // ==========================

        $topPerformers = $keaktifanGuru
            ->take(5)
            ->values();
        // ✅ FIX: guru aktif = persentase >= 80, bukan sekadar total_absen > 0
        $totalGuruAktif = $keaktifanGuru->where('persentase', '>=', 80)->count();
        $totalGuru = $keaktifanGuru->count();

        // ✅ Top 5 performer — ambil dari collection yang sudah di-sort
        $topPerformer = $keaktifanGuru->take(5);

        // ✅ Guru bermasalah untuk insight
        $guruTermalas = $keaktifanGuru
            ->where('total_jadwal', '>', 0)
            ->sortBy('persentase')
            ->first();

        // Jadwal belum absen
        $jadwalBelumAbsen = DB::table('schedules')
            ->join('teachers', 'teachers.id', '=', 'schedules.teacher_id')
            ->join('subjects', 'subjects.id', '=', 'schedules.subject_id')
            ->join('classrooms', 'classrooms.id', '=', 'schedules.classroom_id')
            ->leftJoin('attendances', function ($join) use ($today) {
                $join->on('attendances.schedule_id', '=', 'schedules.id')
                    ->where('attendances.tanggal', $today);
            })
            ->whereNull('attendances.id')
            ->where('schedules.hari', $hariIni)
            ->when($activeYear, fn ($q) => $q->where('schedules.academic_year_id', $activeYear->id))
            ->select(
                'schedules.id',
                'teachers.nama_guru',
                'subjects.nama_mapel',
                'classrooms.tingkat',
                'classrooms.paralel',
                'schedules.jam_mulai',
                'schedules.jam_habis'
            )
            ->orderBy('schedules.jam_mulai')
            ->get();

        // Trend chart
        $trendHarian = DB::table('attendances')
            ->join('schedules', 'schedules.id', '=', 'attendances.schedule_id')
            ->whereBetween('attendances.tanggal', [$startDate, $endDate])
            ->when($activeYear, fn ($q) => $q->where('attendances.academic_year_id', $activeYear->id))
            ->selectRaw('
            DATE(attendances.tanggal) as tanggal,
            COUNT(DISTINCT schedules.teacher_id) as total_guru
        ')
            ->groupBy('attendances.tanggal')
            ->orderBy('attendances.tanggal')
            ->get();

        $chartLabels = $trendHarian->pluck('tanggal')
            ->map(fn ($t) => \Carbon\Carbon::parse($t)->translatedFormat('d M'))
            ->values();
        $chartData = $trendHarian->pluck('total_guru')->values();

        // ✅ Insight chart
        $rataRataGuru = $chartData->count() > 0 ? round($chartData->avg()) : 0;
        $hariTerAktif = $trendHarian->sortByDesc('total_guru')->first();
        $hariTerAktifLabel = $hariTerAktif
            ? \Carbon\Carbon::parse($hariTerAktif->tanggal)->translatedFormat('l, d M')
            : null;

        return view('admin.dashboard', compact(
            'filter',
            'keaktifanGuru',
            'totalGuruAktif',
            'totalGuru',
            'jadwalBelumAbsen',
            'chartLabels',
            'chartData',
            'allYears',
            'activeYear',
            'topPerformer',
            'guruTermalas',
            'rataRataGuru',
            'hariTerAktifLabel',
            'teachers'
        ));
    }

    // ✅ Tambah helper — konsisten dengan DashboardController guru
    private function getDateRange(string $filter): array
    {
        return match ($filter) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'monthly' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'semester' => now()->month >= 7
                ? [now()->year.'-07-01', now()->year.'-12-31']
                : [now()->year.'-01-01', now()->year.'-06-30'],
            default => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
        };
    }
}
