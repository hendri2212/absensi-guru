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

        return redirect()->back()->with('success', 'Kelas ' . $data['tingkat'] . '-' . $data['paralel'] . ' berhasil dibuat.');
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

    private function tujuanValidUntukTingkat(string $tingkat): string|null
    {
        return match ($tingkat) {
            'VII'  => 'VIII',
            'VIII' => 'IX',
            'IX'   => 'lulus',
            default => null,
        };
    }

    /**
     * Validasi mapping: pastikan setiap from_classroom punya tujuan yang sesuai tingkatnya
     */
    private function validateMapping(array $mapping, \Illuminate\Database\Eloquent\Collection $classes): array
    {
        $errors = [];

        foreach ($mapping as $fromId => $toValue) {
            if (! $toValue) continue;

            $from = $classes->get($fromId);
            if (! $from) continue;

            $tujuanValid = $this->tujuanValidUntukTingkat($from->tingkat);

            if ($tujuanValid === 'lulus') {
                if ($toValue !== 'lulus') {
                    $errors[] = "Kelas {$from->tingkat}-{$from->paralel} hanya bisa → Lulus.";
                }
            } elseif ($tujuanValid !== null) {
                // toValue harus berupa ID kelas dengan tingkat yang sesuai
                $toKelas = $classes->get($toValue);
                if (! $toKelas || $toKelas->tingkat !== $tujuanValid) {
                    $errors[] = "Kelas {$from->tingkat}-{$from->paralel} hanya bisa pindah ke tingkat {$tujuanValid}.";
                }
            } else {
                $errors[] = "Tingkat {$from->tingkat} tidak dikenali untuk kenaikan kelas.";
            }
        }

        return $errors;
    }

    public function promoteIndex()
    {
        $classes = Classroom::withCount(['students' => fn($q) => $q->where('status', 'aktif')])
            ->orderBy('tingkat')
            ->orderBy('paralel')
            ->get();

        $activeYear = AcademicYear::where('is_active', true)->first();

        // Kelas tujuan yang valid per tingkat
        $tingkatMap = ['VII' => 'VIII', 'VIII' => 'IX'];

        $classesGrouped = $classes->groupBy('tingkat');

        // [classroom_id => Collection of target classrooms | 'lulus' | null]
        $allowedTargets = $classes->mapWithKeys(function ($kelas) use ($tingkatMap, $classesGrouped) {
            $targetTingkat = $tingkatMap[$kelas->tingkat] ?? null;

            return [
                $kelas->id => $targetTingkat
                    ? $classesGrouped->get($targetTingkat, collect())
                    : ($kelas->tingkat === 'IX' ? 'lulus' : null),
            ];
        });

        return view('admin.kelas.promote', compact('classes', 'activeYear', 'allowedTargets'));
    }

    public function promotePreview(Request $request)
    {
        $request->validate([
            'mapping'      => 'required|array',
            'mapping.*'    => 'nullable|string',
            'tidak_naik'   => 'nullable|array',
            'tidak_naik.*' => 'integer',
        ]);

        $classes    = Classroom::withCount(['students' => fn($q) => $q->where('status', 'aktif')])->get()->keyBy('id');
        $activeYear = AcademicYear::where('is_active', true)->first();
        $tidakNaik  = $request->input('tidak_naik', []);

        $errors = $this->validateMapping($request->mapping, $classes);
        if (! empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $preview = [];
        foreach ($request->mapping as $fromId => $toValue) {
            if (! $toValue) continue;

            $from = $classes->get($fromId);
            if (! $from) continue;

            $allStudents = Student::where('classroom_id', $fromId)->where('status', 'aktif')->get();
            if ($allStudents->isEmpty()) continue;

            $isLulus = $toValue === 'lulus';
            $toKelas = (! $isLulus) ? $classes->get($toValue) : null;

            $preview[] = [
                'from'         => $from,
                'to_value'     => $toValue,
                'to'           => $toKelas,
                'lulus'        => $isLulus,
                'students'     => $allStudents->whereNotIn('id', $tidakNaik)->values(),
                'dikecualikan' => $allStudents->whereIn('id', $tidakNaik)->values(),
            ];
        }

        return view('admin.kelas.promote-preview', compact('preview', 'activeYear'));
    }

    public function promoteExecute(Request $request)
    {
        $request->validate([
            'mapping'      => 'required|array',
            'mapping.*'    => 'nullable|string',
            'tidak_naik'   => 'nullable|array',
            'tidak_naik.*' => 'integer',
        ]);

        $classes    = Classroom::all()->keyBy('id');
        $activeYear = AcademicYear::where('is_active', true)->first();
        $adminId    = Auth::id();
        $tidakNaik  = $request->input('tidak_naik', []);

        $errors = $this->validateMapping($request->mapping, $classes);
        if (! empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        DB::transaction(function () use ($request, $activeYear, $adminId, $tidakNaik) {
            foreach ($request->mapping as $fromId => $toValue) {
                if (! $toValue) continue;

                $students = Student::where('classroom_id', $fromId)
                    ->where('status', 'aktif')
                    ->whereNotIn('id', $tidakNaik) // ← skip yang dikecualikan
                    ->get();

                foreach ($students as $student) {
                    if ($toValue === 'lulus') {
                        StudentClassHistory::create([
                            'student_id'        => $student->id,
                            'from_classroom_id' => $fromId,
                            'to_classroom_id'   => null,
                            'academic_year_id'  => $activeYear?->id,
                            'jenis'             => 'lulus',
                            'keterangan'        => 'Proses kenaikan kelas massal',
                            'processed_by'      => $adminId,
                        ]);
                        $student->update([
                            'status'       => 'lulus',
                            'classroom_id' => null, // ← ini yang kurang
                        ]);
                    } else {
                        StudentClassHistory::create([
                            'student_id'        => $student->id,
                            'from_classroom_id' => $fromId,
                            'to_classroom_id'   => $toValue,
                            'academic_year_id'  => $activeYear?->id,
                            'jenis'             => 'naik_kelas',
                            'keterangan'        => 'Proses kenaikan kelas massal',
                            'processed_by'      => $adminId,
                        ]);
                        $student->update(['classroom_id' => $toValue]);
                    }
                }
            }
        });

        return redirect()->route('admin.kelas.index')->with('success', 'Proses kenaikan kelas berhasil dieksekusi.');
    }
}
