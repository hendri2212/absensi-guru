<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapNilaiExport implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    protected $scheduleId;
    protected $teacherId;
    protected $headerRow = 6;


    public function __construct($scheduleId, $teacherId)
    {
        $this->scheduleId = $scheduleId;
        $this->teacherId  = $teacherId;
    }

    public function array(): array
    {
        $schedule = Schedule::with(['subject', 'classroom'])->findOrFail($this->scheduleId);
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Ambil semua evaluasi milik guru ini di schedule ini, urut by tanggal
        $evaluations = DB::table('evaluations')
            ->where('schedule_id', $this->scheduleId)
            ->where('teacher_id', $this->teacherId)
            ->whereNull('deleted_at')
            ->when(
                $activeYear,
                fn($q) => $q
                    ->where('academic_year_id', $activeYear->id)
                    ->where('semester', $activeYear->semester)
            )
            ->orderBy('tanggal', 'asc')
            ->get();

        // Ambil siswa aktif di kelas ini
        $students = Student::where('classroom_id', $schedule->classroom_id)
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil rekap absensi per siswa
        $absensi = DB::table('attendance_details')
            ->join('attendances', 'attendance_details.attendance_id', '=', 'attendances.id')
            ->where('attendances.schedule_id', $this->scheduleId)
            ->whereNull('attendances.deleted_at')
            ->when(
                $activeYear,
                fn($q) => $q
                    ->where('attendances.academic_year_id', $activeYear->id)
                    ->where('attendances.semester', $activeYear->semester)
            )
            ->select(
                'attendance_details.student_id',
                'attendance_details.status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('attendance_details.student_id', 'attendance_details.status')
            ->get()
            ->groupBy('student_id');

        // Ambil nilai per siswa per evaluasi
        $nilaiRaw = DB::table('evaluation_details')
            ->join('evaluations', 'evaluation_details.evaluation_id', '=', 'evaluations.id')
            ->where('evaluations.schedule_id', $this->scheduleId)
            ->whereNull('evaluations.deleted_at')
            ->whereNull('evaluation_details.deleted_at')
            ->when(
                $activeYear,
                fn($q) => $q
                    ->where('evaluations.academic_year_id', $activeYear->id)
                    ->where('evaluations.semester', $activeYear->semester)
            )
            ->select(
                'evaluation_details.student_id',
                'evaluation_details.evaluation_id',
                'evaluation_details.nilai'
            )
            ->get()
            ->groupBy('student_id');

        // ── Build rows ────────────────────────────────────

        // Baris 1: Info
        $mapel   = $schedule->subject->nama_mapel ?? '-';
        $kelas   = $schedule->classroom->tingkat . '-' . $schedule->classroom->paralel;
        $tahun   = $activeYear ? $activeYear->tahun . ' ' . $activeYear->semester : '-';

        $rows = [];
        $rows[] = ["REKAP NILAI & ABSENSI"];
        $rows[] = ["Mata Pelajaran : {$mapel}"];
        $rows[] = ["Kelas          : {$kelas}"];
        $rows[] = ["Tahun Ajaran   : {$tahun}"];
        $rows[] = ['']; // baris kosong (string kosong, bukan array kosong)

        // Tentukan baris header secara dinamis, bukan hardcode
        $this->headerRow = count($rows) + 1;

        // Header kolom
        $header = ['No', 'Nama Siswa', 'Hadir', 'Izin', 'Sakit', 'Alpa'];
        foreach ($evaluations as $ev) {
            $tgl     = \Carbon\Carbon::parse($ev->tanggal)->format('d/m/y');
            $header[] = "{$ev->nama_penilaian}\n({$ev->jenis} {$tgl})";
        }
        $rows[] = $header;

        // Data siswa
        foreach ($students as $idx => $student) {
            $abs = $absensi->get($student->id, collect());

            $hadir = $abs->where('status', 'Hadir')->sum('total');
            $izin  = $abs->where('status', 'Izin')->sum('total');
            $sakit = $abs->where('status', 'Sakit')->sum('total');
            $alpa  = $abs->where('status', 'Alpa')->sum('total');

            $row = [$idx + 1, $student->nama, $hadir, $izin, $sakit, $alpa];

            $nilaiSiswa = $nilaiRaw->get($student->id, collect());
            foreach ($evaluations as $ev) {
                $detail = $nilaiSiswa->firstWhere('evaluation_id', $ev->id);
                $row[]  = $detail ? $detail->nilai : '-';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Rekap Nilai';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 28,
            'C' => 8,
            'D' => 8,
            'E' => 8,
            'F' => 8,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                $headerRow = $this->headerRow;

                // Merge judul
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style info rows
                $sheet->getStyle("A2:A4")->getFont()->setBold(true);

                // Style header tabel
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(40);

                // Border seluruh tabel
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Zebra stripe data rows
                for ($r = $headerRow + 1; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F3F4F6']
                            ],
                        ]);
                    }
                }

                // Center kolom angka
                $sheet->getStyle("C{$headerRow}:{$lastCol}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Auto width kolom evaluasi (C dst)
                foreach (range('G', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setWidth(14);
                }
            },
        ];
    }
}
