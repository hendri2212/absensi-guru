<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AttendanceDetail;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getTeacherId()
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Silahkan login kembali.');
        }

        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();

        return $teacher ? $teacher->id : abort(403, 'User tidak terhubung ke data Guru.');
    }

    private function getDateRange(string $filter): array
    {
        return match ($filter) {
            'today' => [Carbon::now()->toDateString(), Carbon::now()->toDateString()],
            'weekly' => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
            'monthly' => [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()],
            'semester' => $this->getSemesterRange(),
            default => [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()],
        };
    }

    private function getSemesterRange(): array
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        return $month >= 7
            ? [$year . '-07-01', $year . '-12-31']
            : [$year . '-01-01', $year . '-06-30'];
    }

    public function guruDashboard(Request $request)
    {
        $teacherId = $this->getTeacherId();
        $filter = $request->get('filter', 'weekly');
        [$startDate, $endDate] = $this->getDateRange($filter);

        // Tahun akademik
        $allYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->get('academic_year_id'))
            : $activeYear;
        $isActiveYear = $selectedYear && $activeYear && $selectedYear->id === $activeYear->id;

        // Base condition reusable — hindari duplikasi closure
        $attendanceFilter = function ($q) use ($startDate, $endDate, $selectedYear) {
            $q->whereBetween('tanggal', [$startDate, $endDate]);
            if ($selectedYear) {
                $q->where('academic_year_id', $selectedYear->id);
            }
        };

        // Stats absensi
        $rawStats = AttendanceDetail::whereHas('attendance.schedule', fn($q) => $q->where('teacher_id', $teacherId))
            ->whereHas('attendance', $attendanceFilter)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stats = [
            'hadir' => $rawStats['Hadir'] ?? 0,
            'izin' => $rawStats['Izin'] ?? 0,
            'sakit' => $rawStats['Sakit'] ?? 0,
            'alpa' => $rawStats['Alpa'] ?? 0,
        ];

        // Kalkulasi global — pindah dari blade ke sini
        $totalSemua = array_sum($stats);
        $persenGlobal = $totalSemua > 0 ? round(($stats['hadir'] / $totalSemua) * 100) : 0;
        $persenColor = $persenGlobal >= 80 ? 'success' : ($persenGlobal >= 70 ? 'primary' : ($persenGlobal >= 60 ? 'warning' : 'danger'));

        // Siswa alpa tertinggi
        $lowAttendanceStudents = AttendanceDetail::whereHas('attendance.schedule', fn($q) => $q->where('teacher_id', $teacherId))
            ->whereIn('status', ['Alpa', 'Sakit', 'Izin'])
            ->whereHas('attendance', $attendanceFilter)
            ->select(
                'student_id',
                DB::raw('count(*) as total_tidak_hadir'),
                DB::raw("SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as total_alpa")
            )
            ->with([
                'student:id,nama,classroom_id',
                'student.classroom:id,tingkat,paralel',
            ])
            ->groupBy('student_id')
            ->orderByDesc('total_alpa')
            ->take(5)
            ->get();

        // Rekap per kelas
        $rekapKelas = DB::table('classrooms')
            ->join('schedules', 'schedules.classroom_id', '=', 'classrooms.id')
            ->join('attendances', 'attendances.schedule_id', '=', 'schedules.id')
            ->join('attendance_details', 'attendance_details.attendance_id', '=', 'attendances.id')
            ->where('schedules.teacher_id', $teacherId)
            ->whereBetween('attendances.tanggal', [$startDate, $endDate])
            ->when($selectedYear, fn($q) => $q->where('attendances.academic_year_id', $selectedYear->id))
            ->selectRaw("
            classrooms.id as classroom_id,
            classrooms.tingkat,
            classrooms.paralel,
            CONCAT(classrooms.tingkat, '-', classrooms.paralel) as nama_kelas,
            SUM(CASE WHEN attendance_details.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN attendance_details.status = 'Izin'  THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN attendance_details.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN attendance_details.status = 'Alpa'  THEN 1 ELSE 0 END) as alpa
        ")
            ->groupBy('classrooms.id', 'classrooms.tingkat', 'classrooms.paralel')
            ->get()
            ->map(function ($rk) {
                $total = $rk->hadir + $rk->izin + $rk->sakit + $rk->alpa;
                $rk->total = $total;
                $rk->persen = $total > 0 ? round(($rk->hadir / $total) * 100) : 0;
                $rk->color = $rk->persen >= 80 ? 'success' : ($rk->persen >= 70 ? 'primary' : ($rk->persen >= 50 ? 'warning' : 'danger'));

                return $rk;
            });

        // Kelas bermasalah — pindah dari blade
        $kelasTermburuk = $rekapKelas->sortBy('persen')->first();

        // Jadwal hari ini
        $hariIni = Carbon::now()->locale('id')->dayName;
        $jadwalHariIni = Schedule::with(['subject:id,nama_mapel', 'classroom:id,tingkat,paralel'])
            ->where('teacher_id', $teacherId)
            ->where('hari', $hariIni)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Totals
        $totalKelas = Schedule::where('teacher_id', $teacherId)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->distinct('classroom_id')
            ->count('classroom_id');

        $totalSiswa = DB::table('students')
            ->whereIn('classroom_id', function ($q) use ($teacherId, $activeYear) {
                $q->select('classroom_id')
                    ->from('schedules')
                    ->where('teacher_id', $teacherId)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->distinct();
            })
            ->whereNull('deleted_at')
            ->count();

        return view('guru.dashboard', compact(
            'stats',
            'filter',
            'lowAttendanceStudents',
            'rekapKelas',
            'kelasTermburuk',
            'allYears',
            'selectedYear',
            'isActiveYear',
            'jadwalHariIni',
            'totalKelas',
            'totalSiswa',
            'totalSemua',
            'persenGlobal',
            'persenColor',
        ));
    }

    public function rekapKelas(Request $request, $classroom_id)
    {
        $teacherId = $this->getTeacherId();
        $filter = $request->get('filter', 'weekly');

        [$startDate, $endDate] = $this->getDateRange($filter);

        $classroom = Classroom::findOrFail($classroom_id);
        $activeYear = AcademicYear::where('is_active', true)->first();

        $rekapSiswa = DB::table('students')
            ->leftJoin('attendance_details', 'attendance_details.student_id', '=', 'students.id')
            ->leftJoin('attendances', function ($join) use ($startDate, $endDate, $activeYear) {
                $join->on('attendances.id', '=', 'attendance_details.attendance_id')
                    ->whereBetween('attendances.tanggal', [$startDate, $endDate]);
                if ($activeYear) {
                    $join->where('attendances.academic_year_id', $activeYear->id);
                }
            })
            ->leftJoin('schedules', function ($join) use ($teacherId) {
                $join->on('schedules.id', '=', 'attendances.schedule_id')
                    ->where('schedules.teacher_id', $teacherId);
            })
            ->where('students.classroom_id', $classroom_id)
            ->selectRaw("
            students.id,
            students.nama,
            students.nis,
            SUM(CASE WHEN attendance_details.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN attendance_details.status = 'Izin' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN attendance_details.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN attendance_details.status = 'Alpa' THEN 1 ELSE 0 END) as alpa
        ")
            ->groupBy('students.id', 'students.nama', 'students.nis')
            ->orderBy('students.nama')
            ->get();

        return view('guru.rekap.kelas', compact('classroom', 'rekapSiswa', 'filter'));
    }

    public function listClasses()
    {
        $teacherId = $this->getTeacherId();

        $classrooms = Classroom::where('walas_id', $teacherId)
            ->orWhereHas('schedules', fn($q) => $q->where('teacher_id', $teacherId))
            ->orderBy('tingkat', 'asc')
            ->orderBy('paralel', 'asc')
            ->withCount('students')
            ->get();

        return view('guru.kelas.index', compact('classrooms'));
    }

    public function showClassroom($id)
    {
        $classroom = Classroom::with(['students' => fn($q) => $q->orderBy('nama', 'asc')])->findOrFail($id);

        $teacherId = Auth::user()->teacher->id ?? \App\Models\Teacher::where('user_id', Auth::id())->value('id');

        $schedule = Schedule::where('classroom_id', $classroom->id)
            ->where('teacher_id', $teacherId)
            ->first();

        return view('guru.kelas.show', compact('classroom', 'schedule'));
    }

    public function penilaianIndex()
    {
        $teacherId = $this->getTeacherId();

        $schedules = Schedule::with(['subject', 'classroom'])
            ->where('teacher_id', $teacherId)
            ->get();

        return view('guru.nilai.nilai', compact('schedules'));
    }
}
