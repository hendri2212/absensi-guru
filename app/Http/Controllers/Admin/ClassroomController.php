<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    public function index()
    {
        $classes = DB::table('classrooms')
            ->leftJoin('teachers', 'classrooms.walas_id', '=', 'teachers.id')
            ->select('classrooms.*', 'teachers.nama_guru')
            ->whereNull('classrooms.deleted_at')
            ->orderBy('tingkat', 'asc')
            ->orderBy('paralel', 'asc')
            ->get();

        $teachers = DB::table('teachers')->whereNull('deleted_at')->get();

        return view('admin.kelas.index', compact('classes', 'teachers'));
    }

    public function store(ClassroomRequest $request)
    {
        $data = $request->validated();

        DB::table('classrooms')->insert([
            'tingkat' => $data['tingkat'],
            'paralel' => $data['paralel'],
            'walas_id' => $data['walas_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Kelas '.$data['tingkat'].'-'.$data['paralel'].' berhasil dibuat.');
    }

    public function import(Request $request, $kelas_id)
    {
        $request->validate([
            'file_siswa' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file_siswa');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle);

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) < 7) {
                    continue;
                }

                Student::create([
                    'nama' => $data[0],
                    'nis' => $data[1],
                    'jk' => $data[2],
                    'agama' => $data[3],
                    'tgl_lahir' => $data[4],
                    'alamat' => $data[5],
                    'no_telp' => $data[6] ?? null,
                    'no_telp_ortu' => $data[7] ?? null,
                    'classroom_id' => $kelas_id,
                ]);
            }
            DB::commit();

            return redirect()->back()->with('success', 'Data siswa berhasil diimport.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal import: Pastikan NIS unik dan format benar.');
        } finally {
            fclose($handle);
        }
    }

    public function update(ClassroomRequest $request, $id)
    {
        $data = $request->validated();

        DB::table('classrooms')
            ->where('id', $id)
            ->update([
                'tingkat' => $data['tingkat'],
                'paralel' => $data['paralel'],
                'walas_id' => $data['walas_id'],
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy($id)
    {
        $hasStudents = DB::table('students')->where('classroom_id', $id)->exists();
        $hasSchedules = DB::table('schedule')->where('classroom_id', $id)->exists();

        if ($hasStudents || $hasSchedules) {
            return redirect()->back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki siswa atau jadwal aktif');
        }

        DB::table('classrooms')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus');
    }

    // ══════════════════════════════════════════════════
    // KENAIKAN KELAS
    // ══════════════════════════════════════════════════

    /**
     * Halaman kenaikan kelas — preview mapping tiap kelas ke tujuan
     */
    public function promoteIndex()
    {
        $classes = Classroom::withCount(['students' => fn ($q) => $q->where('status', 'aktif')])
            ->orderBy('tingkat')
            ->orderBy('paralel')
            ->get();

        $activeYear = AcademicYear::where('is_active', true)->first();

        return view('admin.kelas.promote', compact('classes', 'activeYear'));
    }

    /**
     * Preview hasil mapping sebelum dieksekusi
     * POST — kirim array mapping: [from_classroom_id => to_classroom_id | 'lulus']
     */
    public function promotePreview(Request $request)
    {
        $request->validate([
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string', // bisa id kelas atau 'lulus'
        ]);

        $classes = Classroom::withCount(['students' => fn ($q) => $q->where('status', 'aktif')])->get()->keyBy('id');
        $allClasses = Classroom::orderBy('tingkat')->orderBy('paralel')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();

        $preview = [];
        foreach ($request->mapping as $fromId => $toValue) {
            // Lewati jika tidak dipilih
            if (! $toValue) {
                continue;
            }

            $from = $classes->get($fromId);
            if (! $from) {
                continue;
            }

            $students = Student::where('classroom_id', $fromId)->where('status', 'aktif')->get();
            if ($students->isEmpty()) {
                continue;
            }

            $isLulus = $toValue === 'lulus';
            $toKelas = (! $isLulus) ? $classes->get($toValue) : null;

            // Jika bukan lulus tapi kelas tujuan tidak ditemukan, skip
            if (! $isLulus && ! $toKelas) {
                continue;
            }

            $preview[] = [
                'from' => $from,
                'to_value' => $toValue,
                'to' => $toKelas,
                'lulus' => $isLulus,
                'students' => $students,
            ];
        }

        return view('admin.kelas.promote-preview', compact('preview', 'allClasses', 'activeYear'));
    }

    /**
     * Eksekusi kenaikan kelas massal
     */
    public function promoteExecute(Request $request)
    {
        $request->validate([
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $adminId = Auth::id();

        DB::transaction(function () use ($request, $activeYear, $adminId) {
            foreach ($request->mapping as $fromId => $toValue) {
                if (! $toValue) {
                    continue;
                } // skip = tidak diproses

                $students = Student::where('classroom_id', $fromId)
                    ->where('status', 'aktif')
                    ->get();

                foreach ($students as $student) {
                    if ($toValue === 'lulus') {
                        // Catat history lulus
                        StudentClassHistory::create([
                            'student_id' => $student->id,
                            'from_classroom_id' => $fromId,
                            'to_classroom_id' => null,
                            'academic_year_id' => $activeYear?->id,
                            'jenis' => 'lulus',
                            'keterangan' => 'Proses kenaikan kelas massal',
                            'processed_by' => $adminId,
                        ]);

                        $student->update(['status' => 'lulus']);
                    } else {
                        // Pindah ke kelas tujuan
                        StudentClassHistory::create([
                            'student_id' => $student->id,
                            'from_classroom_id' => $fromId,
                            'to_classroom_id' => $toValue,
                            'academic_year_id' => $activeYear?->id,
                            'jenis' => 'naik_kelas',
                            'keterangan' => 'Proses kenaikan kelas massal',
                            'processed_by' => $adminId,
                        ]);

                        $student->update(['classroom_id' => $toValue]);
                    }
                }
            }
        });

        return redirect()->route('admin.kelas.index')->with('success', 'Proses kenaikan kelas berhasil dieksekusi.');
    }
}
