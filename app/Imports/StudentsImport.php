<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class StudentsImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    protected $kelasId;
    public int $berhasil = 0;
    public int $gagal = 0;

    public function __construct($kelasId)
    {
        $this->kelasId = $kelasId;
    }

    public function model(array $row)
    {
        if (empty($row['nama']) || empty($row['nis'])) {
            $this->gagal++;
            return null;
        }

        if (Student::where('nis', (string) $row['nis'])->exists()) {
            $this->gagal++;
            return null;
        }

        // Handle tgl_lahir — bisa string atau angka serial Excel
        $tglLahir = $row['tgl_lahir'];
        if (is_numeric($tglLahir)) {
            $tglLahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglLahir)->format('Y-m-d');
        }

        $this->berhasil++;

        return new Student([
            'nama'         => $row['nama'],
            'nis'          => (string) $row['nis'],
            'jk'           => $row['jk'],
            'agama'        => $row['agama'],
            'tgl_lahir'    => $tglLahir,
            'alamat'       => $row['alamat'],
            'no_telp'      => $row['no_telp'] ?? null,
            'no_telp_ortu' => $row['no_telp_ortu'] ?? null,
            'classroom_id' => $this->kelasId,
        ]);
    }
}
