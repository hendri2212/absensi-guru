<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
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
        // Rapikan spasi: trim depan/belakang, dan collapse spasi ganda di tengah jadi satu
        $row = array_map(function ($v) {
            if (is_string($v)) {
                $v = trim($v);
                $v = preg_replace('/\s+/', ' ', $v);
            }
            return $v;
        }, $row);

        if (empty($row['nama']) || empty($row['nis'])) {
            $this->gagal++;
            return null;
        }

        // Validasi no_telp — wajib angka saja (boleh diawali +), panjang wajar
        if (!empty($row['no_telp']) && !preg_match('/^\+?[0-9]{8,15}$/', $row['no_telp'])) {
            $this->gagal++;
            return null;
        }

        // Handle tgl_lahir — bisa string atau angka serial Excel
        $tglLahir = $row['tgl_lahir'];
        if (is_numeric($tglLahir)) {
            $tglLahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglLahir)->format('Y-m-d');
        }

        // Validasi tanggal lahir — harus tanggal valid & tidak di masa depan
        try {
            $tglLahirCarbon = \Carbon\Carbon::parse($tglLahir);
            if ($tglLahirCarbon->isFuture()) {
                $this->gagal++;
                return null;
            }
        } catch (\Exception $e) {
            $this->gagal++;
            return null;
        }

        $this->berhasil++;

        return new Student([
            'nama'         => $row['nama'],
            'nis'          => (string) $row['nis'],
            'jk'           => $row['jk'],
            'agama'        => $row['agama'],
            'tgl_lahir'    => $tglLahirCarbon->format('Y-m-d'),
            'alamat'       => $row['alamat'],
            'no_telp'      => $row['no_telp'] ?? null,
            'no_telp_ortu' => $row['no_telp_ortu'] ?? null,
            'classroom_id' => $this->kelasId,
        ]);
    }
}
