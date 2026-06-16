<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\School::create([
            'nama_sekolah' => 'SMPN 1 KOTABARU',
            'alamat' => 'Jl. M. Alwi No.158, Kec. Pulau Laut Utara, Kab. Kotabaru, Prov. Kalimantan Selatan',
            'no_telp' => '028228976',
            'email' => 'smpn1ktb@gmail.com',
        ]);
    }
}
