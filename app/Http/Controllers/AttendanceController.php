<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\EvaluationDetail;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
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

    // ===============================
    // HALAMAN UTAMA ABSENSI
    // ===============================
    public function absensiIndex(Request $request)
    {
        $teacherId = $this->getTeacherId();
        $daftarHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $selectedDay = $request->get('hari', $daftarHari[date('w')]);
        $isToday = $selectedDay === $daftarHari[date('w')];
        $today = now()->toDateString();

        $activeYear = AcademicYear::where('is_active', true)->first(); // pindah ke atas
        if (! $activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $schedules = Schedule::with(['subject', 'classroom'])
            ->where('teacher_id', $teacherId)
            ->where('hari', $selectedDay)
            ->where('academic_year_id', $activeYear->id)
            ->orderBy('jam_mulai', 'asc')
            ->get()
            ->map(function ($item) use ($today) {
                $item->setAttribute('sudah_absen', $item->attendances()
                    ->where('tanggal', $today)
                    ->exists());

                return $item;
            });

        $recent_attendances = Attendance::with(['schedule.subject', 'schedule.classroom'])
            ->withCount([
                'details as h' => fn ($q) => $q->where('status', 'Hadir'),
                'details as i' => fn ($q) => $q->where('status', 'Izin'),
                'details as s' => fn ($q) => $q->where('status', 'Sakit'),
                'details as a' => fn ($q) => $q->where('status', 'Alpa'),
            ])
            ->whereHas('schedule', fn ($q) => $q->where('teacher_id', $teacherId))
            ->where('academic_year_id', $activeYear->id)
            ->latest('tanggal')
            ->take(5)
            ->get();

        return view('guru.absensi.index', compact('schedules', 'recent_attendances', 'selectedDay', 'isToday'));
    }

    public function createAbsensi($schedule_id)
    {
        $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);
        $students = Student::where('classroom_id', $schedule->classroom_id)
            ->orderBy('nama', 'asc')
            ->get();

        return view('guru.absensi.input-absen', compact('schedule', 'students'));
    }

    public function showStudent(Request $request, $id)
    {
        $teacherId = $this->getTeacherId();
        $student = Student::with([
            'classroom:id,tingkat,paralel',
        ])->findOrFail($id);

        $allYears = AcademicYear::orderBy('id', 'desc')->get();
        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->get('academic_year_id'))
            : AcademicYear::where('is_active', true)->first();

        $statusFilter = $request->get('status'); // null = semua

        // Query utama — eager load semua relasi yang dibutuhkan blade
        $attendanceHistory = AttendanceDetail::where('student_id', $id)
            ->whereHas('attendance', function ($q) use ($selectedYear) {
                if ($selectedYear) {
                    $q->where('academic_year_id', $selectedYear->id);
                }
            })
            ->whereHas('attendance.schedule', fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->with([
                'attendance:id,tanggal,schedule_id,academic_year_id',
                'attendance.schedule:id,subject_id,classroom_id,jam_mulai,jam_habis',
                'attendance.schedule.subject:id,nama_mapel',
                'attendance.schedule.classroom:id,tingkat,paralel',
            ])
            ->orderByDesc(
                DB::table('attendances')
                    ->select('tanggal')
                    ->whereColumn('attendances.id', 'attendance_details.attendance_id')
                    ->limit(1)
            )
            ->paginate(15)
            ->withQueryString(); // pertahankan ?status=, ?academic_year_id=

        // Summary selalu dari semua data tanpa filter status
        $summaryRaw = AttendanceDetail::where('student_id', $id)
            ->whereHas('attendance', function ($q) use ($selectedYear) {
                if ($selectedYear) {
                    $q->where('academic_year_id', $selectedYear->id);
                }
            })
            ->whereHas('attendance.schedule', fn ($q) => $q->where('teacher_id', $teacherId))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $summary = [
            'Hadir' => $summaryRaw['Hadir'] ?? 0,
            'Izin' => $summaryRaw['Izin'] ?? 0,
            'Sakit' => $summaryRaw['Sakit'] ?? 0,
            'Alpa' => $summaryRaw['Alpa'] ?? 0,
        ];

        // Kalkulasi insight — pindah dari blade
        $totalSemua = array_sum($summary);
        $persenHadir = $totalSemua > 0 ? round(($summary['Hadir'] / $totalSemua) * 100) : 0;
        $persenColor = $persenHadir >= 80 ? 'success' : ($persenHadir >= 70 ? 'primary' : ($persenHadir >= 60 ? 'warning' : 'danger'));

        $insightText = match (true) {
            $persenHadir >= 90 => 'Kehadiran sangat baik',
            $persenHadir >= 70 => 'Kehadiran baik',
            $persenHadir >= 60 => 'Perlu perhatian',
            default => 'Kehadiran bermasalah',
        };
        $insightColor = match (true) {
            $persenHadir >= 90 => 'success',
            $persenHadir >= 70 => 'primary',
            $persenHadir >= 60 => 'warning',
            default => 'danger',
        };

        // Badge status dominan
        $statusDominan = $summary['Hadir'] > ($totalSemua / 2) ? 'disiplin' : 'perhatian';
        $statusDominanColor = $statusDominan === 'disiplin' ? 'success' : 'danger';
        $statusDominanLabel = $statusDominan === 'disiplin' ? 'Siswa Disiplin' : 'Perlu Perhatian';

        // Query nilai siswa per mata pelajaran, filter by tahun ajaran
        $evaluationDetails = EvaluationDetail::where('student_id', $id)
            ->whereHas('evaluation', function ($q) use ($selectedYear, $teacherId) {
                $q->where('teacher_id', $teacherId);
                if ($selectedYear) {
                    $q->where('academic_year_id', $selectedYear->id);
                }
            })
            ->with([
                'evaluation:id,subject_id,classroom_id,jenis,nama_penilaian,tanggal,academic_year_id',
                'evaluation.subject:id,nama_mapel',
                'evaluation.classroom:id,tingkat,paralel',
            ])
            ->orderByDesc(
                \App\Models\Evaluation::select('tanggal')
                    ->whereColumn('evaluations.id', 'evaluation_details.evaluation_id')
                    ->limit(1)
            )
            ->get();

        // Rekapitulasi nilai per mapel
        $nilaiPerMapel = $evaluationDetails
            ->groupBy(fn ($d) => $d->evaluation?->subject->nama_mapel ?? 'Lainnya')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'rata' => round($group->avg('nilai'), 1),
                'maks' => $group->max('nilai'),
                'min' => $group->min('nilai'),
                'mapel' => $group->first()->evaluation?->subject->nama_mapel ?? '-',
            ]);

        return view('guru.absensi.student-detail', compact(
            'student',
            'attendanceHistory',
            'summary',
            'allYears',
            'selectedYear',
            'statusFilter',
            'totalSemua',
            'persenHadir',
            'persenColor',
            'insightText',
            'insightColor',
            'statusDominanColor',
            'statusDominanLabel',
            'evaluationDetails',
            'nilaiPerMapel'
        ));
    }

    public function editAbsensi($schedule_id)
    {
        $schedule = Schedule::with(['classroom', 'subject'])->findOrFail($schedule_id);
        $students = Student::where('classroom_id', $schedule->classroom_id)
            ->orderBy('nama', 'asc')
            ->get();

        $attendance = Attendance::where('schedule_id', $schedule_id)
            ->where('tanggal', now()->toDateString())
            ->firstOrFail();

        $statusMap = $attendance->details->pluck('status', 'student_id');

        return view('guru.absensi.edit-absen', compact('schedule', 'students', 'attendance', 'statusMap'));
    }

    public function updateAbsensi(AttendanceRequest $request, $schedule_id)
    {
        $validated = $request->validated();

        $attendance = Attendance::where('schedule_id', $schedule_id)
            ->where('tanggal', $validated['tanggal'])
            ->firstOrFail();

        DB::transaction(function () use ($validated, $attendance) {
            foreach ($validated['absensi'] as $item) {
                $attendance->details()->updateOrCreate(
                    ['student_id' => $item['student_id']],
                    ['status' => ucfirst($item['status'])]
                );
            }
        });

        return redirect()->route('guru.absensi')->with('success', 'Absensi berhasil diperbarui');
    }

    public function apiIndex()
    {
        $attendance = Attendance::with([
            'schedule.teacher',
            'schedule.subject',
            'details.student',
        ])->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Data riwayat absen ditemukan',
            'data' => $attendance,
        ]);
    }

    public function store(AttendanceRequest $request)
    {
        $validated = $request->validated();

        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif. Hubungi admin.');
        }

        DB::transaction(function () use ($validated, $activeYear) {
            $attendance = Attendance::updateOrCreate(
                [
                    'schedule_id' => $validated['schedule_id'],
                    'tanggal' => $validated['tanggal'],
                ],
                [
                    'academic_year_id' => $activeYear->id,
                    'semester' => $activeYear->semester,
                    'updated_at' => now(),
                ]
            );

            foreach ($validated['absensi'] as $item) {
                $attendance->details()->updateOrCreate(
                    ['student_id' => $item['student_id']],
                    ['status' => ucfirst($item['status'])]
                );
            }
        });

        return redirect()->route('guru.absensi')->with('success', 'Absensi berhasil disimpan');
    }

    public function show(Attendance $attendance)
    {
        return response()->json([
            'status' => true,
            'data' => $attendance->load([
                'schedule.teacher',
                'schedule.subject',
                'details.student',
            ]),
        ]);
    }

    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $attendance) {
            $attendance->update([
                'schedule_id' => $validated['schedule_id'],
                'tanggal' => $validated['tanggal'],
            ]);

            $attendance->details()->delete();

            foreach ($validated['absensi'] as $item) {
                $attendance->details()->create([
                    'student_id' => $item['student_id'],
                    'status' => $item['status'],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Absensi berhasil diperbarui',
                'data' => $attendance->load('details.student'),
            ]);
        });
    }

    public function historyAbsensi($schedule_id)
    {
        $schedule = Schedule::with(['subject', 'classroom'])->findOrFail($schedule_id);

        $this->getTeacherId() === $schedule->teacher_id
            ? null
            : abort(403, 'Anda tidak memiliki akses ke jadwal ini');

        $allYears = AcademicYear::orderBy('id', 'desc')->get();

        $selectedYearId = request('academic_year_id');
        $selectedYear = $selectedYearId
            ? AcademicYear::find($selectedYearId)
            : AcademicYear::where('is_active', true)->first();

        $histories = Attendance::where('schedule_id', $schedule_id)
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->withCount([
                'details as h' => fn ($q) => $q->where('status', 'Hadir'),
                'details as i' => fn ($q) => $q->where('status', 'Izin'),
                'details as s' => fn ($q) => $q->where('status', 'Sakit'),
                'details as a' => fn ($q) => $q->where('status', 'Alpa'),
            ])
            ->orderBy('tanggal')
            ->get();

        return view('guru.absensi.history', compact('schedule', 'histories', 'allYears', 'selectedYear'));
    }

    public function historyDetail($schedule_id, $attendance_id)
    {
        $schedule = Schedule::with(['subject', 'classroom'])->findOrFail($schedule_id);
        $attendance = Attendance::with(['details.student'])->findOrFail($attendance_id);

        return view('guru.absensi.history-detail', compact('schedule', 'attendance'));
    }
}
