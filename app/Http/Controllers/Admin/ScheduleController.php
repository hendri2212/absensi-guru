<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Models\AcademicYear;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    // ambil data jadwal serta nama kelas dan nama guru nya
    public function index()
    {
        $classrooms = DB::table('classrooms')
            ->orderBy('tingkat')
            ->orderBy('paralel')
            ->get()
            ->groupBy('tingkat');

        $teachers = DB::table('teachers')
            ->whereNull('deleted_at')
            ->orderBy('nama_guru')
            ->get();

        $subjects = DB::table('subjects')
            ->whereNull('deleted_at')
            ->orderBy('nama_mapel')
            ->get();

        return view('admin.jadwal.index', compact('classrooms', 'teachers', 'subjects'));
    }

    // show → filter hari + jadwal
    public function show($id)
    {
        $hariMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $hari = request('hari', $hariMap[now()->dayOfWeek]);
        $classroom = DB::table('classrooms')->where('id', $id)->first();

        $activeYear = DB::table('academic_years')->where('is_active', 1)->first();

        if (! $activeYear) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $schedules = DB::table('schedules')
            ->join('classrooms', 'schedules.classroom_id', '=', 'classrooms.id')
            ->join('teachers', 'schedules.teacher_id', '=', 'teachers.id')
            ->join('subjects', 'schedules.subject_id', '=', 'subjects.id')
            ->select(
                'schedules.*',
                'classrooms.tingkat',
                'classrooms.paralel',
                'teachers.nama_guru',
                'subjects.nama_mapel'
            )
            ->where('schedules.classroom_id', $id)
            ->where('schedules.hari', $hari)
            ->where('schedules.academic_year_id', $activeYear->id)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $teachers = DB::table('teachers')->get();
        $subjects = DB::table('subjects')->get();

        return view('admin.jadwal.show', compact('schedules', 'classroom', 'teachers', 'subjects', 'hari'));
    }

    public function create()
    {
        $classrooms = DB::table('classrooms')->get();
        $teachers = DB::table('teachers')->get();
        $subjects = DB::table('subjects')->get();

        return view('admin.jadwal.create', compact('classrooms', 'teachers', 'subjects'));
    }

    public function store(ScheduleRequest $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
        }

        $validated = $request->validated();

        DB::table('schedules')->insert([
            'classroom_id' => $validated['classroom_id'],
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'hari' => $validated['hari'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_habis' => $validated['jam_habis'],
            'academic_year_id' => $activeYear->id,
            'semester' => $activeYear->semester,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $classrooms = DB::table('classrooms')->get();
        $teachers = DB::table('teachers')->get();
        $subjects = DB::table('subjects')->get();

        return view('admin.jadwal.index', compact('schedule', 'classrooms', 'teachers', 'subjects'));
    }

    public function update(ScheduleRequest $request, $id)
    {
        $validated = $request->validated();
        $schedule = Schedule::findOrFail($id);

        if ($schedule->attendances()->exists() || $schedule->evaluations()->exists()) {
            return redirect()->back()->with('error', 'Jadwal tidak dapat diubah karena sudah memiliki data absensi atau penilaian.');
        }

        $schedule->update($validated);

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);

        if ($schedule->attendances()->exists() || $schedule->evaluations()->exists()) {
            return redirect()->back()->with('error', 'Jadwal tidak dapat dihapus karena masih memiliki data absensi atau penilaian.');
        }

        $schedule->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
