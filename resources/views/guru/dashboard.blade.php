@extends('layouts.app')
@section('title', 'Dashboard Guru')

@section('content')
    <div class="container py-4 pb-5 mb-4">

        {{-- Header --}}
        <div class="card border-0 rounded-3 mb-3 bg-gradient-header shadow-md">
            <div class="card-body px-4 py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Dashboard Guru</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            Halo, {{ Auth::user()->teacher->nama_guru ?? Auth::user()->name }}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select class="form-select form-select-sm w-auto"
                            onchange="window.location.href='?filter={{ $filter }}&academic_year_id='+this.value">
                            @foreach ($allYears as $y)
                                <option value="{{ $y->id }}"
                                    {{ $selectedYear && $selectedYear->id == $y->id ? 'selected' : '' }}>
                                    {{ $y->tahun }} - {{ $y->semester }}
                                    {{ $y->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="btn-group">
                            @foreach (['today' => 'Hari Ini', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'semester' => 'Semester'] as $key => $label)
                                <a href="?filter={{ $key }}&academic_year_id={{ $selectedYear?->id }}"
                                    class="btn btn-sm {{ $filter == $key ? 'btn-light' : 'btn-outline-light' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if (!$isActiveYear)
                    <div class="mt-3">
                        <span class="badge bg-warning-subtle text-warning px-3 py-2">
                            <i class="bi bi-eye me-1"></i>Mode Baca — {{ $selectedYear->tahun }}
                            {{ $selectedYear->semester }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3 alert-success-custom" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <div class="small">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ① STAT CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="text-muted fw-medium">Total Kelas</small>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-primary mb-0">{{ $totalKelas }}</h3>
                        <small class="text-muted">kelas diajar</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="text-muted fw-medium">Total Siswa</small>
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-info mb-0">{{ $totalSiswa }}</h3>
                        <small class="text-muted">siswa aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="text-muted fw-medium">Total Alpa</small>
                            <div class="stat-icon bg-danger-subtle text-danger">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-danger mb-0">{{ $stats['alpa'] }}</h3>
                        <small class="text-muted">pertemuan</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="text-muted fw-medium">Kehadiran Global</small>
                            <div class="stat-icon bg-{{ $persenColor }}-subtle text-{{ $persenColor }}">
                                <i class="bi bi-percent"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-{{ $persenColor }} mb-0">{{ $persenGlobal }}%</h3>
                        <small class="text-muted">dari {{ $totalSemua }} total</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ② JADWAL HARI INI --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-calendar-check text-primary me-2"></i>Jadwal Hari Ini
                    </h6>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </small>
                </div>
            </div>
            @if ($jadwalHariIni->isEmpty())
                <div class="card-body text-center py-4">
                    <i class="bi bi-calendar-x text-muted opacity-50 d-block mb-2 fs-2"></i>
                    <small class="text-muted">Tidak ada jadwal mengajar hari ini.</small>
                </div>
            @else
                <div class="card-body p-0">
                    @foreach ($jadwalHariIni as $jadwal)
                        <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="jadwal-time text-center flex-shrink-0">
                                <div class="fw-bold text-primary small lh-1">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}
                                </div>
                                <div class="text-muted jadwal-time-end">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_habis)->format('H:i') }}
                                </div>
                            </div>
                            <div class="jadwal-divider bg-primary rounded-pill flex-shrink-0"></div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark small">{{ $jadwal->subject->nama_mapel }}</div>
                                <small class="text-muted">
                                    Kelas {{ $jadwal->classroom->tingkat }}-{{ $jadwal->classroom->paralel }}
                                </small>
                            </div>
                            @if ($isActiveYear)
                                <a href="{{ route('guru.absensi.create', $jadwal->id) }}"
                                    class="btn btn-sm btn-light border flex-shrink-0">
                                    <i class="bi bi-arrow-right text-primary"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ③ WARNING SISWA ALPA --}}
        @if ($lowAttendanceStudents->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4 border-danger-left">
                <div class="card-header bg-danger-subtle border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Siswa dengan Alpa Tertinggi
                        <span class="badge bg-danger ms-2">{{ $lowAttendanceStudents->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach ($lowAttendanceStudents as $s)
                        @php
                            $namaKelas = $s->student->classroom
                                ? $s->student->classroom->tingkat . '-' . $s->student->classroom->paralel
                                : '-';
                        @endphp
                        <div
                            class="d-flex justify-content-between align-items-center px-4 py-3
                {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-semibold text-dark small">{{ $s->student->nama }}</div>
                                <small class="text-muted">Kelas {{ $namaKelas }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger-subtle text-danger fw-bold">
                                    {{ $s->total_alpa }}x Alpa
                                </span>
                                @if ($s->total_tidak_hadir > $s->total_alpa)
                                    <div>
                                        <small class="text-muted">
                                            +{{ $s->total_tidak_hadir - $s->total_alpa }} izin/sakit
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ④ CHART --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-graph-up text-primary me-2"></i>Statistik Kehadiran Siswa
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="chart-container-sm">
                    <canvas id="absensiChart" data-stats='@json($stats)'></canvas>
                </div>
            </div>
        </div>

        {{-- ⑤ REKAP PER KELAS --}}
        @if ($rekapKelas->isNotEmpty())

            {{-- Insight kelas bermasalah --}}
            @if ($kelasTermburuk && $kelasTermburuk->persen < 80)
                <div class="alert alert-warning-custom rounded-3 mb-3 py-2 px-3">
                    <small class="text-warning-emphasis">
                        <i class="bi bi-flag-fill me-1"></i>
                        <strong>{{ $kelasTermburuk->nama_kelas }}</strong> memiliki kehadiran terendah —
                        <strong>{{ $kelasTermburuk->persen }}%</strong>. Perlu perhatian.
                    </small>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-building text-primary me-2"></i>Rekap Per Kelas
                </h6>
                <small class="text-muted">{{ $rekapKelas->count() }} kelas</small>
            </div>

            @foreach ($rekapKelas as $rk)
                <a href="{{ route('guru.rekap.kelas', [$rk->classroom_id, 'filter' => $filter]) }}"
                    class="card border-0 shadow-sm rounded-3 mb-2 text-decoration-none d-block">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="kelas-avatar bg-primary-subtle text-primary fw-bold">
                                    {{ $rk->paralel }}
                                </div>
                                <span class="fw-semibold text-dark">{{ $rk->nama_kelas }}</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-{{ $rk->color }} small">{{ $rk->persen }}%</span>
                                <i class="bi bi-chevron-right text-muted small ms-1"></i>
                            </div>
                        </div>
                        <div class="progress mb-2 progress-thin">
                            <div class="progress-bar bg-{{ $rk->color }}" style="width:{{ $rk->persen }}%"></div>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge rounded-pill bg-success-subtle text-success">H: {{ $rk->hadir }}</span>
                            <span class="badge rounded-pill bg-primary-subtle text-primary">I: {{ $rk->izin }}</span>
                            <span class="badge rounded-pill bg-warning-subtle text-warning">S: {{ $rk->sakit }}</span>
                            <span class="badge rounded-pill bg-danger-subtle text-danger">A: {{ $rk->alpa }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        @else
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body py-5 text-center">
                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-50 text-muted"></i>
                    <p class="text-muted small mb-0">Belum ada data absensi untuk periode ini.</p>
                </div>
            </div>
        @endif

    </div>
@endsection
