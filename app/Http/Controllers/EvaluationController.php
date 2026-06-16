<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvaluationRequest;
use App\Models\AcademicYear;
use App\Models\Evaluation;
use App\Models\EvaluationDetail;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
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

    public function index(Request $request)
    {
        $teacherId = $this->getTeacherId();

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $scheduleIds = Schedule::where('teacher_id', $teacherId)
            ->where('academic_year_id', $activeYear->id) // tambah
            ->whereHas('subject')
            ->whereHas('classroom')
            ->selectRaw('MIN(id) as id')
            ->groupBy('teacher_id', 'subject_id', 'classroom_id')
            ->pluck('id');

        $schedules = Schedule::with(['subject', 'classroom'])
            ->whereIn('id', $scheduleIds)
            ->get()
            ->map(function ($schedule) use ($teacherId, $activeYear) {
                $schedule->setAttribute('all_evaluations', Evaluation::where('subject_id', $schedule->subject_id)
                    ->where('classroom_id', $schedule->classroom_id)
                    ->where('teacher_id', $teacherId)
                    ->where('academic_year_id', $activeYear->id)
                    ->latest()
                    ->take(5)
                    ->get());

                return $schedule;
            });

        return view('guru.nilai.nilai', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($schedule_id)
    {
        if (! $schedule_id) {
            return redirect()->route('guru.evaluations.index')->with('error', 'Silahkan pilih kelas terlebih dahulu.');
        }

        $schedule = \App\Models\Schedule::with(['subject', 'classroom'])->findOrFail($schedule_id);

        $students = Student::where('classroom_id', $schedule->classroom_id)
            ->orderBy('nama', 'asc')
            ->get();

        return view('guru.nilai.input', compact('schedule', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EvaluationRequest $request)
    {
        $teacherId = $this->getTeacherId();
        $validated = $request->validated();
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return back()->with('error', 'Tahun ajaran aktif belum diatur oleh admin.');
        }

        $schedule = Schedule::findOrFail($validated['schedule_id']);

        $evaluation = Evaluation::updateOrCreate(
            [
                'subject_id' => $validated['subject_id'],
                'classroom_id' => $schedule->classroom_id,
                'teacher_id' => $teacherId,
                'jenis' => $validated['jenis'],
                'nama_penilaian' => $validated['nama_penilaian'],
                'academic_year_id' => $activeYear->id,
            ],
            [
                'schedule_id' => $validated['schedule_id'],
                'tanggal' => $validated['tanggal'],
                'semester' => $activeYear->semester,
            ]
        );

        foreach ($validated['penilaian'] as $studentId => $data) {
            if (isset($data['nilai']) && $data['nilai'] !== '') {
                EvaluationDetail::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'student_id' => $studentId,
                    ],
                    ['nilai' => $data['nilai']]
                );
            }
        }

        return redirect()->route('guru.evaluations.index')
            ->with('success', 'Nilai siswa berhasil di input.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $evaluation = Evaluation::withTrashed()
            ->with(['details' => fn ($q) => $q->with('student')->orderBy(
                Student::select('nama')->whereColumn('students.id', 'evaluation_details.student_id')->limit(1)
            ), 'subject', 'classroom'])
            ->findOrFail($id);

        return view('guru.nilai.show', compact('evaluation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evaluation $evaluation)
    {
        $evaluation->load(['subject', 'classroom']);
        $students = Student::where('classroom_id', $evaluation->classroom_id)
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($student) use ($evaluation) {
                $detail = EvaluationDetail::where('evaluation_id', $evaluation->id)
                    ->where('student_id', $student->id)
                    ->first();

                $student->setAttribute('nilai_saat_ini', $detail ? $detail->nilai : null);

                return $student;
            });

        return view('guru.nilai.edit', compact('evaluation', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EvaluationRequest $request, Evaluation $evaluation)
    {
        DB::transaction(function () use ($request, $evaluation) {
            $evaluation->update($request->only(['nama_penilaian', 'tanggal', 'jenis']));

            $inputPenilaian = $request->input('penilaian', []);

            foreach ($inputPenilaian as $studentId => $data) {
                if (isset($data['nilai']) && $data['nilai'] !== '') {
                    EvaluationDetail::updateOrCreate(
                        [
                            'evaluation_id' => $evaluation->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'nilai' => $data['nilai'],
                        ]
                    );
                }
            }
        });

        return redirect()->route('guru.evaluations.show', $evaluation->id)
            ->with('success', 'Data dan nilai siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $evaluation = Evaluation::findOrFail($id);
            $evaluation->details()->each(fn ($d) => $d->delete());
            $evaluation->delete();

            return redirect()->route('guru.evaluations.index')
                ->with('success', 'Data penilaian berhasil dipindahkan ke tempat sampah.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: '.$e->getMessage());
        }
    }

    public function trash()
    {
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

        $trashedEvaluations = Evaluation::onlyTrashed()
            ->with(['subject', 'classroom', 'details'])
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id)) // tambah
            ->latest('deleted_at')
            ->get();

        return view('guru.nilai.trash', compact('trashedEvaluations'));
    }

    public function forceDeleteEvaluation($id)
    {
        $evaluation = Evaluation::onlyTrashed()->findOrFail($id);
        $evaluation->details()->forceDelete();
        $evaluation->forceDelete();

        return back()->with('success', 'Data penilaian dihapus permanen.');
    }
}
