@extends('layouts.app')

@section('title', 'Daftar Siswa - Kelas ' . $classroom->tingkat . '-' . $classroom->paralel)

@section('content')
    <div class="container py-4">

        {{-- Header Gradient --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow-md">
            <div class="card-body px-4 py-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('guru.kelas.index') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Kelas {{ $classroom->tingkat }}-{{ $classroom->paralel }}</h5>
                        <p class="mb-0 text-white opacity-75 small">{{ $classroom->students->count() }} Siswa terdaftar</p>
                    </div>
                    @if ($schedule)
                        <a href="{{ route('guru.export.rekap', $schedule->id) }}" class="btn btn-success btn-sm ms-auto">
                            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Search --}}
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchSiswaGuru" class="form-control border-start-0"
                    placeholder="Cari nama siswa...">
            </div>
        </div>

        <div class="card border-0 shadow rounded-3 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 text-muted small fw-semibold" width="60">NO</th>
                                <th class="py-3 text-muted small fw-semibold">NAMA SISWA</th>
                                <th class="py-3 text-center text-muted small fw-semibold pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($classroom->students as $index => $student)
                                <tr>
                                    <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $student->nama }}</div>
                                        <small class="text-muted">NIS: {{ $student->nis }}</small>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('guru.siswa.detail', $student->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-person-badge me-1"></i> Profil
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted small">
                                        <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                        Belum ada siswa di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('components.scripts')
@endpush
