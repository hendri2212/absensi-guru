@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
    <div class="container py-4 pb-5 mb-4">

        {{-- Header --}}
        <div class="card border-0 rounded-3 mb-3 bg-gradient-header shadow">
            <div class="card-body px-4 py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Dashboard Admin</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            {{ $totalGuruAktif }} dari {{ $totalGuru }} guru aktif &mdash;
                            <span class="fw-semibold">{{ ucfirst($filter) }}</span>
                            <span class="opacity-50 ms-2">· {{ now()->format('H:i') }}</span>
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select class="form-select form-select-sm w-auto"
                            onchange="window.location.href='?filter={{ $filter }}&academic_year_id='+this.value">
                            @foreach ($allYears as $y)
                                <option value="{{ $y->id }}"
                                    {{ $activeYear && $activeYear->id == $y->id ? 'selected' : '' }} style="color:#000;">
                                    {{ $y->tahun }} - {{ $y->semester }}
                                    {{ $y->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="btn-group">
                            @foreach (['today' => 'Hari Ini', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'semester' => 'Semester'] as $key => $label)
                                <a href="?filter={{ $key }}&academic_year_id={{ $activeYear?->id }}"
                                    class="btn btn-sm {{ $filter == $key ? 'btn-light' : 'btn-outline-light' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ① STAT CARDS --}}
        <div class="row g-3 mb-4">
            @php
                $statCards = [
                    ['label' => 'Total Guru', 'value' => $totalGuru, 'icon' => 'bi-people-fill', 'color' => 'primary'],
                    [
                        'label' => 'Guru Aktif',
                        'value' => $totalGuruAktif,
                        'icon' => 'bi-person-check-fill',
                        'color' => 'success',
                    ],
                    [
                        'label' => 'Belum Aktif',
                        'value' => $totalGuru - $totalGuruAktif,
                        'icon' => 'bi-person-dash-fill',
                        'color' => 'danger',
                    ],
                    [
                        'label' => 'Belum Absen',
                        'value' => $jadwalBelumAbsen->count(),
                        'icon' => 'bi-clock-fill',
                        'color' => 'warning',
                    ],
                ];
            @endphp
            @foreach ($statCards as $card)
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <small class="text-muted fw-medium">{{ $card['label'] }}</small>
                                <div class="stat-icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }}">
                                    <i class="bi {{ $card['icon'] }}"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-{{ $card['color'] }} mb-0">{{ $card['value'] }}</h3>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ② INSIGHT ALERT --}}
        @if ($jadwalBelumAbsen->count() > 0 || ($guruTermalas && $guruTermalas->persentase < 50))
            <div class="mb-4">
                @if ($jadwalBelumAbsen->count() > 0)
                    <div class="alert-insight border-warning mb-2 rounded-3 py-2 px-3 d-flex align-items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
                        <div>
                            <div class="fw-semibold small">{{ $jadwalBelumAbsen->count() }} jadwal hari ini belum diabsen
                            </div>
                            <div class="text-muted" style="font-size:12px;">
                                Guru perlu segera melakukan absensi sebelum jam pelajaran berakhir.
                            </div>
                        </div>
                    </div>
                @endif

                @if ($guruTermalas && $guruTermalas->persentase < 50)
                    <div class="alert-insight border-danger rounded-3 py-2 px-3 d-flex align-items-center gap-3">
                        <i class="bi bi-person-x-fill text-danger fs-5 flex-shrink-0"></i>
                        <div>
                            <div class="fw-semibold small">
                                {{ $guruTermalas->nama_guru }} memiliki keaktifan terendah
                                <span
                                    class="badge bg-danger-subtle text-danger ms-1">{{ $guruTermalas->persentase }}%</span>
                            </div>
                            <div class="text-muted" style="font-size:12px;">Perlu tindakan atau konfirmasi lebih lanjut.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ③ QUICK ACTION --}}
        <div class="row g-3 mb-4">
            @php
                $quickActions = [
                    [
                        'label' => 'Kelola Guru',
                        'icon' => 'bi-person-badge',
                        'color' => 'primary',
                        'route' => 'admin.guru.index',
                    ],
                    [
                        'label' => 'Kelola Kelas',
                        'icon' => 'bi-grid',
                        'color' => 'success',
                        'route' => 'admin.kelas.index',
                    ],
                    ['label' => 'Jadwal', 'icon' => 'bi-calendar3', 'color' => 'info', 'route' => 'admin.jadwal.index'],
                    [
                        'label' => 'Tahun Ajaran',
                        'icon' => 'bi-gear',
                        'color' => 'warning',
                        'route' => 'admin.tahun-ajaran.index',
                    ],
                ];
            @endphp
            @foreach ($quickActions as $action)
                <div class="col-6 col-md-3">
                    <a href="{{ route($action['route']) }}"
                        class="card border-0 shadow-sm rounded-3 text-decoration-none quick-action-card h-100">
                        <div class="card-body p-3 text-center">
                            <div class="stat-icon bg-{{ $action['color'] }}-subtle text-{{ $action['color'] }} mx-auto mb-2"
                                style="width:44px;height:44px;font-size:18px;">
                                <i class="bi {{ $action['icon'] }}"></i>
                            </div>
                            <div class="fw-semibold small text-dark">{{ $action['label'] }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- ④ CHART + TOP PERFORMER (2 kolom di desktop) --}}
        <div class="row g-4 mb-4">

            {{-- Chart --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow rounded-3 h-100">

                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                            <div>
                                <h6 class="fw-semibold mb-1">
                                    <i class="bi bi-graph-up text-primary me-2"></i>
                                    Trend Guru Mengabsen per Hari
                                </h6>

                                <small class="text-muted">
                                    Menampilkan jumlah guru yang melakukan absensi setiap hari.
                                </small>
                            </div>

                            <small class="text-muted">
                                Update:
                                {{ now()->format('H:i') }}
                            </small>

                        </div>
                    </div>

                    <div class="card-body">
                        <div style="position:relative;height:280px;">
                            <canvas id="trendChart" data-labels='@json($chartLabels ?? [])'
                                data-data='@json($chartData ?? [])'>
                            </canvas>
                        </div>
                    </div>

                </div>
            </div>


            {{-- Top Performer --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-trophy text-warning me-2"></i>Top Performer
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @forelse($topPerformer as $i => $guru)
                            <div
                                class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="fw-bold text-muted flex-shrink-0" style="width:20px;">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold small text-truncate">{{ $guru->nama_guru }}</div>
                                    <div class="progress mt-1" style="height:4px;">
                                        <div class="progress-bar bg-{{ $guru->statusColor }}"
                                            style="width:{{ $guru->persentase }}%"></div>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-{{ $guru->statusColor }}-subtle text-{{ $guru->statusColor }} flex-shrink-0">
                                    {{ $guru->persentase }}%
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted small">
                                <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                Belum ada data.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- ⑤ TABEL KEAKTIFAN GURU --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-person-check text-primary me-2"></i>Keaktifan Guru Mengabsen
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-muted small fw-bold" style="width:40px">#</th>
                            <th class="py-3 border-0 text-muted small fw-bold">NAMA GURU</th>
                            <th class="py-3 border-0 text-center text-muted small fw-bold d-none d-md-table-cell">JADWAL
                            </th>
                            <th class="py-3 border-0 text-center text-muted small fw-bold d-none d-md-table-cell">ABSEN</th>
                            <th class="py-3 border-0 text-muted small fw-bold">STATUS</th>
                            <th class="py-3 border-0 text-muted small fw-bold pe-4">KEAKTIFAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $i => $guru)
                            <tr>
                                <td class="ps-4 text-muted small">
                                    {{ $teachers->firstItem() + $i }}
                                </td>
                                <td class="fw-semibold">{{ $guru->nama_guru }}</td>
                                <td class="text-center text-muted small d-none d-md-table-cell">
                                    {{ $guru->total_jadwal }}
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <span
                                        class="badge rounded-pill {{ $guru->total_absen > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $guru->total_absen }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge rounded-pill bg-{{ $guru->statusColor }}-subtle text-{{ $guru->statusColor }}">
                                        {{ $guru->statusLabel }}
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:5px;">
                                            <div class="progress-bar bg-{{ $guru->statusColor }}"
                                                style="width:{{ $guru->persentase }}%"></div>
                                        </div>
                                        <small class="fw-bold text-nowrap text-{{ $guru->statusColor }}">
                                            {{ $guru->persentase }}%
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                    Belum ada data keaktifan guru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($teachers->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                        <small class="text-muted">
                            {{ $teachers->firstItem() }}–{{ $teachers->lastItem() }}
                            dari {{ $teachers->total() }} guru
                        </small>

                        {{ $teachers->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ⑥ JADWAL BELUM ABSEN --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden border-danger-left">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Jadwal Hari Ini Belum Diabsen
                    </h6>
                    @if ($jadwalBelumAbsen->count() > 0)
                        <span class="badge bg-danger">{{ $jadwalBelumAbsen->count() }}</span>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-muted small fw-bold">GURU</th>
                            <th class="py-3 border-0 text-muted small fw-bold">MAPEL / KELAS</th>
                            <th class="py-3 border-0 text-muted small fw-bold">JAM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalBelumAbsen as $j)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $j->nama_guru }}</td>
                                <td>
                                    <div class="fw-semibold text-primary small">{{ $j->nama_mapel }}</div>
                                    <small class="text-muted">{{ $j->tingkat }}-{{ $j->paralel }}</small>
                                </td>
                                <td class="text-nowrap small text-muted">
                                    {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} &ndash;
                                    {{ \Carbon\Carbon::parse($j->jam_habis)->format('H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted small">
                                    <i class="bi bi-check-circle display-6 d-block mb-2 text-success opacity-75"></i>
                                    Semua jadwal hari ini sudah diabsen.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
