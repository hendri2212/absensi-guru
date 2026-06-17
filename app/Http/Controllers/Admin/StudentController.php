<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index($kelas_id)
    {
        $kelas = Classroom::findOrFail($kelas_id);
        $students = Student::where('classroom_id', $kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->paginate(20);

        $allClasses = Classroom::orderBy('tingkat')->orderBy('paralel')->get();

        return view('admin.kelas.students', compact('kelas', 'students', 'allClasses'));
    }

    public function store(Request $request, $kelas_id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'nis' => 'required|max:10|unique:students,nis',
            'jk' => 'required|in:L,P',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            'no_telp' => 'nullable|string',
            'no_telp_ortu' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        Student::create(array_merge($validator->validated(), ['classroom_id' => $kelas_id]));

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, $kelas_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'nis' => 'required|max:10|unique:students,nis,' . $id,
            'jk' => 'required|in:L,P',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            'no_telp' => 'nullable|string',
            'no_telp_ortu' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        Student::findOrFail($id)->update($validator->validated());

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy($kelas_id, $id)
    {
        $student = Student::findOrFail($id);

        if ($student->attendances()->exists() || $student->evaluations()->exists()) {
            return redirect()->back()->with('error', 'Siswa tidak dapat dihapus karena masih memiliki data absensi atau nilai.');
        }

        $student->delete();

        return redirect()->back()->with('success', 'Data siswa berhasil dihapus.');
    }

    public function import(Request $request, $kelas_id)
    {
        $request->validate([
            'file_siswa' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new StudentsImport($kelas_id);
        Excel::import($import, $request->file('file_siswa'));

        $berhasil = $import->berhasil;
        $gagal    = $import->gagal + count($import->failures()) + count($import->errors());

        return redirect()->back()->with('success', "Import selesai: {$berhasil} berhasil, {$gagal} dilewati.");
    }

    public function downloadTemplate($kelas_id = null)
    {
        return Excel::download(new class implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithEvents,
            \Maatwebsite\Excel\Concerns\WithColumnWidths {

            public function array(): array
            {
                return [
                    ['nama', 'nis', 'jk', 'agama', 'tgl_lahir', 'alamat', 'no_telp', 'no_telp_ortu'],
                    ['Budi Santoso', '1234567890', 'L', 'Islam', '2010-01-15', 'Jl. Merdeka No 1', '081234567890', '081234567891'],
                    ['Siti Rahayu', '1234567891', 'P', 'Islam', '2010-05-20', 'Jl. Sudirman No 5', '081234567892', '081234567893'],
                ];
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 25, // nama
                    'B' => 15, // nis
                    'C' => 8,  // jk
                    'D' => 12, // agama
                    'E' => 15, // tgl_lahir
                    'F' => 30, // alamat
                    'G' => 18, // no_telp
                    'H' => 18, // no_telp_ortu
                ];
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();

                        // Style header
                        $sheet->getStyle('A1:H1')->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '2563EB'],
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);

                        // Style contoh data
                        $sheet->getStyle('A2:H3')->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'EFF6FF'],
                            ],
                            'font' => ['color' => ['rgb' => '6B7280'], 'italic' => true],
                        ]);

                        // Border
                        $sheet->getStyle('A1:H3')->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => 'D1D5DB'],
                                ],
                            ],
                        ]);

                        // Note di baris 5
                        $sheet->setCellValue('A5', '* Hapus baris contoh (baris 2 dan 3) sebelum upload');
                        $sheet->getStyle('A5')->applyFromArray([
                            'font' => ['italic' => true, 'color' => ['rgb' => 'DC2626']],
                        ]);

                        $sheet->setCellValue('A6', '* Kolom jk diisi L (Laki-laki) atau P (Perempuan)');
                        $sheet->getStyle('A6')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');

                        $sheet->setCellValue('A7', '* Kolom tgl_lahir format YYYY-MM-DD (contoh: 2010-01-15)');
                        $sheet->getStyle('A7')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');

                        $sheet->setCellValue('A8', '* Kolom agama diisi: Islam, Kristen, Katolik, Hindu, Buddha, Khonghucu');
                        $sheet->getStyle('A8')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');
                    },
                ];
            }
        }, 'template_import_siswa.xlsx');
    }

    // ══════════════════════════════════════════════════
    // PINDAH KELAS INDIVIDUAL
    // ══════════════════════════════════════════════════

    /**
     * Pindah satu siswa ke kelas lain atau set lulus/keluar
     */
    public function moveStudent(Request $request, $kelas_id, $id)
    {
        $request->validate([
            'action' => 'required|in:pindah,lulus,keluar',
            'to_classroom_id' => 'required_if:action,pindah|nullable|exists:classrooms,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $student = Student::findOrFail($id);
        $activeYear = AcademicYear::where('is_active', true)->first();
        $adminId = Auth::id();

        DB::transaction(function () use ($request, $student, $activeYear, $adminId, $kelas_id) {
            $action = $request->action;

            if ($action === 'pindah') {
                StudentClassHistory::create([
                    'student_id' => $student->id,
                    'from_classroom_id' => $kelas_id,
                    'to_classroom_id' => $request->to_classroom_id,
                    'academic_year_id' => $activeYear?->id,
                    'jenis' => 'pindah',
                    'keterangan' => $request->keterangan,
                    'processed_by' => $adminId,
                ]);

                $student->update(['classroom_id' => $request->to_classroom_id]);
            } else {
                // lulus atau keluar
                StudentClassHistory::create([
                    'student_id' => $student->id,
                    'from_classroom_id' => $kelas_id,
                    'to_classroom_id' => null,
                    'academic_year_id' => $activeYear?->id,
                    'jenis' => $action,
                    'keterangan' => $request->keterangan,
                    'processed_by' => $adminId,
                ]);

                $student->update(['status' => $action]);
            }
        });

        $labels = ['pindah' => 'dipindahkan', 'lulus' => 'ditandai lulus', 'keluar' => 'ditandai keluar'];

        return redirect()->back()->with('success', "Siswa {$student->nama} berhasil {$labels[$request->action]}.");
    }
}
