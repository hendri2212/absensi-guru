# 📚 Aplikasi Absensi Guru

Aplikasi manajemen absensi dan penilaian siswa berbasis web untuk sekolah menengah (SMP/MTs). Dibangun dengan Laravel 11, Bootstrap 5, dan MySQL.

---

## 🧱 Tech Stack

| Layer      | Teknologi                                        |
| ---------- | ------------------------------------------------ |
| Backend    | Laravel 11 (PHP)                                 |
| Database   | MySQL                                            |
| Frontend   | Blade Templating + Bootstrap 5 + Vite            |
| Auth       | Session-based (LoginController + RoleMiddleware) |
| API        | Laravel Sanctum                                  |
| CSS Custom | `public/css/app-custom.css`                      |
| PWA        | `manifest.json` + `sw.js`                        |

---

## 📁 Struktur Project

```
absensi-guru/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AcademicYearController.php
│   │   │   │   ├── AdminController.php
│   │   │   │   ├── ClassroomController.php      ← CRUD kelas + promote massal
│   │   │   │   ├── ScheduleController.php
│   │   │   │   ├── StudentController.php        ← CRUD siswa + pindah individual
│   │   │   │   ├── SubjectController.php
│   │   │   │   └── TeacherController.php
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── AttendanceController.php         ← Absensi + showStudent
│   │   │   ├── AttendanceDetailController.php
│   │   │   ├── AuthController.php
│   │   │   ├── ClassroomController.php          ← View guru (beda dari Admin/)
│   │   │   ├── DashboardController.php
│   │   │   ├── EvaluationController.php
│   │   │   ├── EvaluationDetailController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── ScheduleController.php           ← View guru (beda dari Admin/)
│   │   │   └── SchoolController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   ├── Requests/
│   │   │   ├── AcademicYearRequest.php
│   │   │   ├── AttendanceRequest.php
│   │   │   ├── ClassroomRequest.php
│   │   │   ├── EvaluationRequest.php
│   │   │   ├── ScheduleRequest.php
│   │   │   ├── StudentRequest.php
│   │   │   ├── SubjectRequest.php
│   │   │   └── TeacherRequest.php
│   │   └── Resources/
│   │       └── EvaluationResource.php
│   ├── Models/
│   │   ├── AcademicYear.php
│   │   ├── Attendance.php
│   │   ├── AttendanceDetail.php
│   │   ├── Classroom.php
│   │   ├── Evaluation.php
│   │   ├── EvaluationDetail.php        ← SoftDeletes
│   │   ├── Schedule.php
│   │   ├── School.php
│   │   ├── Student.php                 ← Tambah: status (aktif|lulus|keluar), scope aktif/lulus
│   │   ├── StudentClassHistory.php     ← BARU: riwayat pindah kelas
│   │   ├── Subject.php
│   │   ├── Teacher.php
│   │   └── User.php
│   ├── Policies/
│   │   ├── EvaluationPolicy.php
│   │   └── StudentPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── View/Components/
│       └── Navbar.php
├── database/
│   ├── migrations/
│   │   ├── ..._create_students_table.php
│   │   ├── ..._add_status_to_students_table.php         ← BARU
│   │   └── ..._create_student_class_histories_table.php ← BARU
│   └── seeders/
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── guru/         (index, create, edit)
│       │   ├── jadwal/       (index, show)
│       │   ├── kelas/
│       │   │   ├── index.blade.php      ← Tombol Kenaikan Kelas
│       │   │   ├── students.blade.php   ← Modal pindah individual
│       │   │   ├── promote.blade.php    ← BARU: mapping kenaikan massal
│       │   │   └── promote-preview.blade.php ← BARU: preview sebelum eksekusi
│       │   ├── mapel/        (index)
│       │   ├── tahun-ajaran/ (index)
│       │   └── dashboard.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── components/
│       │   ├── footer.blade.php
│       │   ├── head.blade.php
│       │   ├── modal-detail-siswa.blade.php
│       │   ├── navbar.blade.php
│       │   └── scripts.blade.php       ← Semua JS global di sini (tidak di blade individual)
│       ├── guru/
│       │   ├── absensi/
│       │   │   ├── index.blade.php
│       │   │   ├── input-absen.blade.php
│       │   │   ├── absen-create.blade.php
│       │   │   ├── edit-absen.blade.php
│       │   │   ├── history.blade.php
│       │   │   ├── history-detail.blade.php
│       │   │   └── student-detail.blade.php  ← Tab Absensi + Tab Nilai
│       │   ├── kelas/        (index, show)
│       │   ├── nilai/        (nilai, input, edit, show, trash)
│       │   ├── profile/      (index)
│       │   ├── rekap/        (kelas)
│       │   └── dashboard.blade.php
│       └── layouts/
│           └── app.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── public/
    ├── css/app-custom.css
    └── img/
```

---

## 🗃️ Model & Relasi

| Model                 | Tabel                     | Keterangan                                        |
| --------------------- | ------------------------- | ------------------------------------------------- |
| `User`                | `users`                   | Akun login, memiliki role                         |
| `Teacher`             | `teachers`                | Data profil guru, relasi ke User                  |
| `School`              | `schools`                 | Data sekolah                                      |
| `AcademicYear`        | `academic_years`          | Tahun ajaran + semester                           |
| `Classroom`           | `classrooms`              | Data kelas/rombel                                 |
| `Subject`             | `subjects`                | Mata pelajaran                                    |
| `Student`             | `students`                | Data siswa — ada `status`, `no_telp_ortu`         |
| `StudentClassHistory` | `student_class_histories` | Riwayat pindah/naik kelas/lulus                   |
| `Schedule`            | `schedules`               | Jadwal mengajar (guru + mapel + kelas + semester) |
| `Attendance`          | `attendances`             | Header absensi per sesi                           |
| `AttendanceDetail`    | `attendance_details`      | Detail absensi per siswa                          |
| `Evaluation`          | `evaluations`             | Header penilaian (ada `classroom_id`)             |
| `EvaluationDetail`    | `evaluation_details`      | Detail nilai per siswa                            |

### Catatan penting:

- `attendances` dan `evaluations` punya kolom `academic_year_id` dan `semester`
- `schedules` juga punya kolom `semester`
- `students` tidak punya `user_id` (sudah dihapus via migrasi)
- `students` punya `status` enum: `aktif | lulus | keluar` (default: `aktif`)
- `student_class_histories.jenis` enum: `naik_kelas | pindah | lulus | keluar`
- `evaluations` punya `classroom_id`
- `EvaluationDetail` dan `Student` menggunakan SoftDeletes

---

## 🗺️ Routes

### Admin (`/admin` — middleware `role:Admin`)

```php
// Dashboard
GET  /admin/dashboard

// Resource: guru, kelas, mapel, jadwal
Route::resource('guru', TeacherController::class)
Route::resource('kelas', ClassroomController::class)
Route::resource('mapel', SubjectController::class)
Route::resource('jadwal', ScheduleController::class)

// Manajemen Siswa per Kelas
GET    /admin/kelas/{kelas_id}/students           → kelas.students.index
POST   /admin/kelas/{kelas_id}/students           → kelas.students.store
POST   /admin/kelas/{kelas_id}/students/import    → students.import
PUT    /admin/kelas/{kelas_id}/students/{id}      → kelas.students.update
DELETE /admin/kelas/{kelas_id}/students/{id}      → kelas.students.destroy
POST   /admin/kelas/{kelas_id}/students/{id}/move → kelas.students.move  ← pindah individual

// Kenaikan Kelas Massal (harus sebelum resource kelas!)
GET  /admin/kelas/promote                         → kelas.promote
POST /admin/kelas/promote/preview                 → kelas.promote.preview
POST /admin/kelas/promote/execute                 → kelas.promote.execute

// Tahun Ajaran
GET  /admin/academic-year                         → tahun-ajaran.index
POST /admin/academic-year                         → tahun-ajaran.store
POST /admin/academic-year/{id}/activate           → tahun-ajaran.activate
```

### Guru (`/guru`)

```php
// Profile
GET /guru/profile
PUT /guru/profile/password

// Dashboard & Kelas
GET /guru/dashboard
GET /guru/kelas                                   → guru.kelas.index
GET /guru/kelas/{id}                              → guru.kelas.show
GET /guru/rekap/{classroom_id}                    → guru.rekap.kelas
GET /guru/siswa/{id}                              → guru.siswa.detail  ← tab absensi + nilai

// Absensi
GET  /guru/absensi
GET  /guru/absensi/input/{schedule_id}
POST /guru/absensi/store
GET  /guru/absensi/{schedule_id}/edit
POST /guru/absensi/{schedule_id}/update
GET  /guru/absensi/{schedule_id}/history
GET  /guru/absensi/{schedule_id}/history/{attendance_id}

// Penilaian
GET    /guru/penilaian
GET    /guru/evaluations/trash
POST   /guru/evaluations/{id}/restore
DELETE /guru/evaluations/{id}/force-delete
GET    /guru/evaluations/create/{schedule_id}
Route::resource('evaluations', EvaluationController::class)->except(['create'])
```

---

## ✅ Fitur

- [x] Login & autentikasi (role: `admin` & `guru`)
- [x] Dashboard admin & dashboard guru
- [x] Manajemen data guru (CRUD)
- [x] Manajemen data siswa + import dari CSV
- [x] Manajemen mata pelajaran (CRUD)
- [x] Manajemen jadwal mengajar
- [x] Manajemen tahun ajaran
- [x] Manajemen kelas
- [x] Input & edit absensi harian
- [x] History absensi & detail per siswa
- [x] Rekap absensi per kelas
- [x] Input & edit penilaian (nilai)
- [x] History/riwayat penilaian
- [x] Trash penilaian (soft delete + restore)
- [x] Profile guru
- [x] Data sekolah
- [x] API endpoint (Sanctum)
- [x] PWA (manifest.json + sw.js)
- [x] **Detail siswa** — tab Absensi + tab Nilai dalam satu halaman
- [x] **Kenaikan kelas massal** — mapping per kelas, preview, eksekusi
- [x] **Pindah kelas individual** — pindah, lulus, atau keluar per siswa
- [x] **Riwayat pindah kelas** — tercatat di `student_class_histories`
- [x] **Status siswa** — `aktif | lulus | keluar`

---

## 👤 Role & Hak Akses

### Admin

- Akses semua route prefix `/admin`
- CRUD guru, siswa, mapel, jadwal, kelas, tahun ajaran
- Proses kenaikan kelas massal & pindah individual
- Lihat semua rekap dan data

### Guru

- Dashboard, absensi, nilai, kelas, rekap, profil sendiri
- Hanya kelola data di jadwal yang diampu
- Lihat detail siswa (absensi + nilai) di kelasnya

**Middleware:** `RoleMiddleware` → `app/Http/Middleware/RoleMiddleware.php`
**Policy:** `EvaluationPolicy`, `StudentPolicy`

---

## ⚙️ Konvensi Kode

- **Controller Admin** → `app/Http/Controllers/Admin/`
- **Controller Guru** → `app/Http/Controllers/` (root)
- **Validasi** → Form Request `app/Http/Requests/`
- **View** → `views/admin/` dan `views/guru/` sesuai role
- **Blade naming** → Bahasa Indonesia untuk folder view
- **Model naming** → Bahasa Inggris
- **Layout utama** → `resources/views/layouts/app.blade.php`
- **Komponen reusable** → `resources/views/components/`
- **Semua JS global** → `components/scripts.blade.php` (tidak boleh inline di blade individual)
- **CSS custom** → `public/css/app-custom.css`

---

## 🔑 Hal Penting

1. **Filter utama selalu `academic_year_id` + `semester`** — semua query absensi, penilaian, dan jadwal harus difilter keduanya
2. **Ada dua `ClassroomController`** — `Admin/ClassroomController` untuk CRUD + promote, `ClassroomController` (root) untuk view guru
3. **Ada dua `ScheduleController`** — idem
4. **`Attendance`** = header sesi, detail per siswa di `AttendanceDetail`
5. **`Evaluation`** = header penilaian per kelas/mapel, detail nilai per siswa di `EvaluationDetail`
6. **Route kenaikan kelas harus dideklarasikan SEBELUM `Route::resource('kelas')`** — supaya `/kelas/promote` tidak ditangkap sebagai `{id}`
7. **`students.status`** = `aktif` (default) | `lulus` | `keluar` — query siswa aktif pakai scope `->aktif()`
8. **Ganti tahun ajaran** tidak mereset data siswa/guru — hanya absensi, nilai, jadwal yang fresh karena filter `academic_year_id`
9. **Alur kenaikan kelas**: Admin buka halaman promote → pilih tujuan tiap kelas → preview → eksekusi → history tercatat otomatis
10. **JS tab persistence** di `student-detail` pakai `data-student-id` di container div, dibaca lewat `dataset.studentId` di `scripts.blade.php` — tidak pakai PHP variable di dalam JS

---

## 🗄️ Migrasi Terbaru (Tambahan)

```php
// 1. Tambah kolom status di tabel students
Schema::table('students', function (Blueprint $table) {
    $table->enum('status', ['aktif', 'lulus', 'keluar'])->default('aktif')->after('classroom_id');
});

// 2. Tabel baru student_class_histories
Schema::create('student_class_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignId('from_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
    $table->foreignId('to_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
    $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
    $table->enum('jenis', ['naik_kelas', 'pindah', 'lulus', 'keluar']);
    $table->text('keterangan')->nullable();
    $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

---

## 🚀 Setup

```bash
# Clone & install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Build assets
npm run dev      # development
npm run build    # production

# Jalankan
php artisan serve
```

---

## 📬 API

Base URL: `/api`
Auth: Laravel Sanctum (Bearer Token)

Tersedia Postman collection di folder `postman/` dan `.postman/`.

---

## 📝 Catatan Desain

- **Tidak ada pivot `classroom_student`** — siswa tetap punya satu `classroom_id` aktif. Riwayat kelas disimpan di `student_class_histories`.
- **Ganti tahun ajaran ≠ reset siswa** — data siswa dan posisi kelas tidak berubah saat tahun ajaran baru dibuat/diaktifkan.
- **Kelas 9 lulus** → status siswa diset `lulus`, data tetap ada di database, tidak dihapus.
- **Query siswa aktif** selalu pakai `->where('status', 'aktif')` atau scope `->aktif()`.
