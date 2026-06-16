<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. SCHOOL
            |--------------------------------------------------------------------------
            */

            DB::table('schools')->insertOrIgnore([
                'id' => 1,
                'nama_sekolah' => 'SMPN 1 KOTABARU',
                'alamat' => 'Jl. Sampel No. 1',
                'no_telp' => '081234567890',
                'email' => 'smpn1ktb@gmail.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 2. ACADEMIC YEARS
            |--------------------------------------------------------------------------
            */

            $tahunAjaran = [
                ['tahun' => '2024/2025', 'semester' => 'Ganjil', 'is_active' => false],
                ['tahun' => '2024/2025', 'semester' => 'Genap',  'is_active' => false],
                ['tahun' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false],
                ['tahun' => '2025/2026', 'semester' => 'Genap',  'is_active' => true],
            ];

            $academicYears = [];

            foreach ($tahunAjaran as $ta) {
                $existing = DB::table('academic_years')
                    ->where('tahun', $ta['tahun'])
                    ->where('semester', $ta['semester'])
                    ->first();

                if ($existing) {
                    $academicYears[] = $existing;
                } else {
                    $id = DB::table('academic_years')->insertGetId([
                        'tahun' => $ta['tahun'],
                        'semester' => $ta['semester'],
                        'is_active' => $ta['is_active'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $academicYears[] = (object) [
                        'id' => $id,
                        'tahun' => $ta['tahun'],
                        'semester' => $ta['semester'],
                        'is_active' => $ta['is_active'],
                    ];
                }
            }

            $activeYear = collect($academicYears)->firstWhere('is_active', true);

            /*
            |--------------------------------------------------------------------------
            | 3. ADMIN
            |--------------------------------------------------------------------------
            */

            DB::table('users')->insertOrIgnore([
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4. TEACHERS — total 20 guru
            | - 3 akun testing khusus (guru_a, guru_b, guru_c)
            | - 17 guru random (guru01–guru17)
            | - Kelas VIII & IX sengaja tidak ada jadwal/walas → guru bisa tes bikin sendiri
            |--------------------------------------------------------------------------
            */

            $namaDepanLaki = [
                'Ahmad',
                'Budi',
                'Eko',
                'Fajar',
                'Gilang',
                'Hendra',
                'Ivan',
                'Joko',
                'Luthfi',
                'Maulana',
                'Reza',
                'Sandi',
                'Taufik',
                'Wahyu',
                'Zainal',
            ];

            $namaDepanPerempuan = [
                'Aini',
                'Bunga',
                'Citra',
                'Desi',
                'Ella',
                'Fani',
                'Gita',
                'Hana',
                'Indah',
                'Julia',
                'Lisa',
                'Maya',
                'Nisa',
                'Putri',
                'Rani',
            ];

            $namaBelakang = [
                'Santoso',
                'Rahayu',
                'Fauzi',
                'Lestari',
                'Pratama',
                'Hidayat',
                'Susanto',
                'Wijaya',
                'Kurniawan',
                'Setiawan',
                'Nugroho',
                'Purnama',
                'Saputra',
                'Handoko',
                'Wibowo',
                'Suryanto',
                'Gunawan',
                'Hartono',
                'Siregar',
                'Nasution',
            ];

            $gelar = ['S.Pd', 'M.Pd', 'S.Pd.I', 'M.Pd.I'];

            $agamaList = array_merge(
                array_fill(0, 70, 'Islam'),
                array_fill(0, 15, 'Kristen'),
                array_fill(0, 8, 'Katolik'),
                array_fill(0, 5, 'Hindu'),
                array_fill(0, 2, 'Buddha')
            );

            $nipUsed = [];

            $generateNip = function (Carbon $tglLahir, string $jk, int $urut) use (&$nipUsed): string {
                $jkDigit = $jk === 'L' ? '1' : '2';
                $nip = substr($tglLahir->format('Ymd').$jkDigit.str_pad($urut, 6, '0', STR_PAD_LEFT), 0, 18);

                while (in_array($nip, $nipUsed)) {
                    $urut++;
                    $nip = substr($tglLahir->format('Ymd').$jkDigit.str_pad($urut, 6, '0', STR_PAD_LEFT), 0, 18);
                }

                $nipUsed[] = $nip;

                return $nip;
            };

            $teacherIds = [];

            // --- 3 akun guru testing ---
            $testTeachers = [
                ['username' => 'guru_a', 'nama_guru' => 'Andi Pratama, S.Pd',  'jk' => 'L', 'agama' => 'Islam'],
                ['username' => 'guru_b', 'nama_guru' => 'Budi Santoso, S.Pd',  'jk' => 'L', 'agama' => 'Kristen'],
                ['username' => 'guru_c', 'nama_guru' => 'Citra Rahayu, S.Pd',  'jk' => 'P', 'agama' => 'Islam'],
            ];

            foreach ($testTeachers as $idx => $tt) {
                $user = DB::table('users')->where('username', $tt['username'])->first();

                if (! $user) {
                    $userId = DB::table('users')->insertGetId([
                        'username' => $tt['username'],
                        'password' => Hash::make('guru123'),
                        'role' => 'Guru',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $userId = $user->id;
                }

                $teacher = DB::table('teachers')->where('user_id', $userId)->first();

                if ($teacher) {
                    $teacherIds[] = $teacher->id;
                } else {
                    $tgl = Carbon::create(1985 + $idx, 1 + $idx, 10 + $idx);
                    $teacherIds[] = DB::table('teachers')->insertGetId([
                        'user_id' => $userId,
                        'nama_guru' => $tt['nama_guru'],
                        'nip' => $generateNip($tgl, $tt['jk'], $idx + 1),
                        'jk' => $tt['jk'],
                        'agama' => $tt['agama'],
                        'tgl_lahir' => $tgl->toDateString(),
                        'alamat' => 'Jl. Guru No. '.($idx + 1),
                        'no_telp' => '0812345678'.str_pad($idx, 2, '0', STR_PAD_LEFT),
                        'school_id' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // --- 17 guru random (guru01–guru17) ---
            for ($i = 1; $i <= 17; $i++) {
                $jk = $i % 2 === 0 ? 'L' : 'P';
                $username = 'guru'.str_pad($i, 2, '0', STR_PAD_LEFT);

                $namaDepan = $jk === 'L'
                    ? $namaDepanLaki[array_rand($namaDepanLaki)]
                    : $namaDepanPerempuan[array_rand($namaDepanPerempuan)];

                $namaGuru = $namaDepan.' '.$namaBelakang[array_rand($namaBelakang)].', '.$gelar[array_rand($gelar)];

                $user = DB::table('users')->where('username', $username)->first();

                if (! $user) {
                    $userId = DB::table('users')->insertGetId([
                        'username' => $username,
                        'password' => Hash::make('guru123'),
                        'role' => 'Guru',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $userId = $user->id;
                }

                $teacher = DB::table('teachers')->where('user_id', $userId)->first();

                if ($teacher) {
                    $teacherIds[] = $teacher->id;
                } else {
                    $tgl = Carbon::create(rand(1970, 1995), rand(1, 12), rand(1, 28));
                    $teacherIds[] = DB::table('teachers')->insertGetId([
                        'user_id' => $userId,
                        'nama_guru' => $namaGuru,
                        'nip' => $generateNip($tgl, $jk, $i + 10),
                        'jk' => $jk,
                        'agama' => $agamaList[array_rand($agamaList)],
                        'tgl_lahir' => $tgl->toDateString(),
                        'alamat' => 'Jl. Guru No. '.rand(1, 200),
                        'no_telp' => '08'.rand(100000000, 999999999),
                        'school_id' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 5. SUBJECTS
            |--------------------------------------------------------------------------
            */

            $subjects = [
                'Matematika',
                'IPA',
                'IPS',
                'PKN',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'Agama Islam',
                'Seni Budaya',
                'PJOK',
                'Prakarya',
            ];

            $subjectIds = [];

            foreach ($subjects as $subject) {
                $existing = DB::table('subjects')->where('nama_mapel', $subject)->first();

                if ($existing) {
                    $subjectIds[] = $existing->id;
                } else {
                    $subjectIds[] = DB::table('subjects')->insertGetId([
                        'nama_mapel' => $subject,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 6. CLASSROOMS
            | - Hanya VII A–H (8 kelas) yang dibuat dengan walas & jadwal
            | - VIII & IX sengaja kosong → guru bisa tes tambah kelas sendiri
            |--------------------------------------------------------------------------
            */

            $paralelList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            $classroomIds = [];

            foreach ($paralelList as $idx => $paralel) {
                $existing = DB::table('classrooms')
                    ->where('tingkat', 'VII')
                    ->where('paralel', $paralel)
                    ->first();

                if ($existing) {
                    $classroomIds[] = $existing->id;
                } else {
                    $classroomIds[] = DB::table('classrooms')->insertGetId([
                        'tingkat' => 'VII',
                        'paralel' => $paralel,
                        'walas_id' => $teacherIds[$idx % count($teacherIds)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 7. STUDENTS — 25 siswa per kelas
            |--------------------------------------------------------------------------
            */

            $agamaSiswa = array_merge(
                array_fill(0, 80, 'Islam'),
                array_fill(0, 10, 'Kristen'),
                array_fill(0, 5, 'Katolik'),
                array_fill(0, 3, 'Hindu'),
                array_fill(0, 2, 'Buddha')
            );

            $studentIds = [];
            $nisCounter = 0;

            foreach ($classroomIds as $classroomId) {
                $studentIds[$classroomId] = [];
                $studentBatch = [];

                for ($i = 1; $i <= 25; $i++) {
                    $jk = $i % 2 === 0 ? 'L' : 'P';

                    $namaDepan = $jk === 'L'
                        ? $namaDepanLaki[array_rand($namaDepanLaki)]
                        : $namaDepanPerempuan[array_rand($namaDepanPerempuan)];

                    $nisCounter++;
                    $nis = '2025'.str_pad($nisCounter, 4, '0', STR_PAD_LEFT);

                    $studentBatch[] = [
                        'nama' => $namaDepan.' '.$namaBelakang[array_rand($namaBelakang)],
                        'nis' => $nis,
                        'jk' => $jk,
                        'agama' => $agamaSiswa[array_rand($agamaSiswa)],
                        'tgl_lahir' => Carbon::create(rand(2010, 2012), rand(1, 12), rand(1, 28))->toDateString(),
                        'alamat' => 'Jl. Siswa No. '.rand(1, 100),
                        'no_telp' => '08'.rand(100000000, 999999999),
                        'no_telp_ortu' => '08'.rand(100000000, 999999999),
                        'classroom_id' => $classroomId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('students')->insert($studentBatch);

                $studentIds[$classroomId] = DB::table('students')
                    ->where('classroom_id', $classroomId)
                    ->orderBy('id')
                    ->pluck('id')
                    ->toArray();
            }

            /*
            |--------------------------------------------------------------------------
            | 8. SCHEDULES — hanya untuk tahun ajaran aktif & kelas VII
            | - Anti-bentrok: 1 guru tidak bisa ngajar di 2 kelas di hari+jam yang sama
            |--------------------------------------------------------------------------
            */

            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            $jamList = [
                ['jam_mulai' => '07:30', 'jam_habis' => '09:00'],
                ['jam_mulai' => '09:00', 'jam_habis' => '10:30'],
                ['jam_mulai' => '10:30', 'jam_habis' => '12:00'],
                ['jam_mulai' => '13:00', 'jam_habis' => '14:30'],
            ];

            $scheduleIds = [];
            $teacherSlotUsed = [];

            // Hanya seed jadwal untuk tahun ajaran aktif
            foreach ($classroomIds as $classroomId) {
                $scheduleIds[$classroomId] = [];

                foreach ($hariList as $hari) {
                    foreach ($jamList as $jam) {

                        // 20% slot kosong (realistis)
                        if (rand(1, 100) <= 20) {
                            continue;
                        }

                        // Pilih guru yang belum ngajar di slot ini
                        $shuffledTeachers = $teacherIds;
                        shuffle($shuffledTeachers);
                        $assignedTeacher = null;

                        foreach ($shuffledTeachers as $tid) {
                            $slotKey = "{$tid}_{$activeYear->id}_{$hari}_{$jam['jam_mulai']}";
                            if (! isset($teacherSlotUsed[$slotKey])) {
                                $assignedTeacher = $tid;
                                $teacherSlotUsed[$slotKey] = true;
                                break;
                            }
                        }

                        if (! $assignedTeacher) {
                            continue;
                        }

                        $scheduleId = DB::table('schedules')->insertGetId([
                            'academic_year_id' => $activeYear->id,
                            'semester' => $activeYear->semester,
                            'teacher_id' => $assignedTeacher,
                            'subject_id' => $subjectIds[array_rand($subjectIds)],
                            'classroom_id' => $classroomId,
                            'hari' => $hari,
                            'jam_mulai' => $jam['jam_mulai'],
                            'jam_habis' => $jam['jam_habis'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $scheduleIds[$classroomId][] = $scheduleId;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 9. ATTENDANCES — hanya hari kerja, tidak melewati hari ini
            |--------------------------------------------------------------------------
            */

            $statusList = [
                'Hadir',
                'Hadir',
                'Hadir',
                'Hadir',
                'Hadir',
                'Hadir',
                'Izin',
                'Sakit',
                'Alpa',
            ];

            $hariToDay = [
                'Senin' => Carbon::MONDAY,
                'Selasa' => Carbon::TUESDAY,
                'Rabu' => Carbon::WEDNESDAY,
                'Kamis' => Carbon::THURSDAY,
                'Jumat' => Carbon::FRIDAY,
                'Sabtu' => Carbon::SATURDAY,
            ];

            $today = Carbon::today();

            foreach ($scheduleIds as $classroomId => $scheduleList) {
                foreach ($scheduleList as $scheduleId) {
                    $schedule = DB::table('schedules')->find($scheduleId);

                    for ($week = 0; $week < 8; $week++) {
                        if (rand(1, 100) <= 15) {
                            continue;
                        }

                        $tanggal = $today->copy()
                            ->subWeeks($week)
                            ->startOfWeek(Carbon::MONDAY)
                            ->addDays($hariToDay[$schedule->hari] - Carbon::MONDAY);

                        if ($tanggal->isAfter($today)) {
                            continue;
                        }

                        $attendanceId = DB::table('attendances')->insertGetId([
                            'schedule_id' => $scheduleId,
                            'tanggal' => $tanggal->toDateString(),
                            'academic_year_id' => $schedule->academic_year_id,
                            'semester' => $schedule->semester,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $details = [];
                        foreach ($studentIds[$classroomId] as $studentId) {
                            $details[] = [
                                'attendance_id' => $attendanceId,
                                'student_id' => $studentId,
                                'status' => $statusList[array_rand($statusList)],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        DB::table('attendance_details')->insert($details);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 10. EVALUATIONS
            |--------------------------------------------------------------------------
            */

            $jenisEval = ['Tugas', 'UH', 'UTS', 'UAS'];

            foreach ($scheduleIds as $classroomId => $scheduleList) {
                foreach ($scheduleList as $scheduleId) {
                    $schedule = DB::table('schedules')->find($scheduleId);

                    foreach ($jenisEval as $jenis) {
                        $evaluationId = DB::table('evaluations')->insertGetId([
                            'schedule_id' => $scheduleId,
                            'subject_id' => $schedule->subject_id,
                            'classroom_id' => $classroomId,
                            'teacher_id' => $schedule->teacher_id,
                            'academic_year_id' => $schedule->academic_year_id,
                            'semester' => $schedule->semester,
                            'jenis' => $jenis,
                            'nama_penilaian' => $jenis.' '.rand(1, 3),
                            'tanggal' => now()->subDays(rand(1, 120))->toDateString(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $details = [];
                        foreach ($studentIds[$classroomId] as $studentId) {
                            $roll = rand(1, 100);
                            $nilai = match (true) {
                                $roll <= 10 => rand(40, 59),
                                $roll <= 80 => rand(60, 85),
                                default => rand(86, 100),
                            };

                            $details[] = [
                                'evaluation_id' => $evaluationId,
                                'student_id' => $studentId,
                                'nilai' => $nilai,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        DB::table('evaluation_details')->insert($details);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SUMMARY
            |--------------------------------------------------------------------------
            */

            $this->command->info('');
            $this->command->info('✅ Seeder selesai!');
            $this->command->info('');
            $this->command->info('=== DATA YANG DI-SEED ===');
            $this->command->info('Kelas    : VII A – VII H (8 kelas, 25 siswa/kelas = 200 siswa)');
            $this->command->info('Guru     : 20 guru (3 testing + 17 random)');
            $this->command->info('Jadwal   : Hanya kelas VII, tahun ajaran aktif (2025/2026 Genap)');
            $this->command->info('');
            $this->command->info('=== YANG BISA DITES MANUAL ===');
            $this->command->info('[ ] Tambah kelas VIII & IX');
            $this->command->info('[ ] Tambah jadwal untuk kelas VIII & IX');
            $this->command->info('[ ] Tambah guru baru');
            $this->command->info('[ ] Absensi manual lewat aplikasi');
            $this->command->info('');
            $this->command->info('=== AKUN LOGIN ===');
            $this->command->info('Admin    → username: admin   | password: admin123');
            $this->command->info('Guru A   → username: guru_a  | password: guru123  (Andi Pratama – Islam – L)');
            $this->command->info('Guru B   → username: guru_b  | password: guru123  (Budi Santoso – Kristen – L)');
            $this->command->info('Guru C   → username: guru_c  | password: guru123  (Citra Rahayu – Islam – P)');
            $this->command->info('Guru lain→ username: guru01 s/d guru17 | password: guru123');
            $this->command->info('');
        });
    }
}
