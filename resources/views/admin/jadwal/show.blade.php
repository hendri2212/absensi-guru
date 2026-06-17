@extends('layouts.app')
@section('title', 'Jadwal Pelajaran')

@section('content')
    <div class="container py-4">

        {{-- Alert error - custom modern --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert"
                style="border-left: 4px solid #dc2626; background-color: #fef2f2;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                    <div>
                        <strong class="small">Gagal memproses data:</strong>
                        <ul class="mb-0 mt-1 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header - PAKAI CLASS bg-gradient-header --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </a>
                        <div>
                            <h5 class="fw-bold mb-1 text-white">
                                Kelas {{ $classroom->tingkat }} {{ $classroom->paralel }}
                            </h5>
                            <p class="mb-0 text-white opacity-75 small">
                                {{ count($schedules) }} jadwal &mdash; Hari {{ $hari }}
                            </p>
                        </div>
                    </div>
                    <button class="btn btn-light btn-sm fw-semibold flex-shrink-0" data-bs-toggle="modal"
                        data-bs-target="#modalTambahJadwal">
                        <i class="bi bi-plus-lg me-1"></i>
                        <span class="d-none d-md-inline">Tambah Jadwal</span>
                        <span class="d-md-none">Tambah</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Hari --}}
        <div class="d-flex gap-2 mb-4 overflow-auto pb-1">
            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                <a href="{{ route('admin.jadwal.show', [$classroom->id, 'hari' => $h]) }}"
                    class="btn btn-sm flex-shrink-0 {{ $hari == $h ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $h }}
                </a>
            @endforeach
        </div>

        {{-- Tabel Jadwal --}}
        <div class="card border-0 shadow rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-semibold">JAM</th>
                            <th class="py-3 text-muted small fw-semibold">MAPEL & GURU</th>
                            <th class="py-3 text-center text-muted small fw-semibold pe-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $j)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <div class="fw-semibold small">
                                        {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($j->jam_habis)->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary">{{ $j->nama_mapel }}</div>
                                    <small class="text-muted">{{ $j->nama_guru }}</small>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                            data-bs-target="#modalEditJadwal{{ $j->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.jadwal.destroy', $j->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus"
                                                data-id="{{ $j->id }}" data-nama="{{ $j->nama_mapel }}"
                                                title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted small">
                                    <i class="bi bi-calendar-x display-6 d-block mb-2 opacity-50"></i>
                                    Belum ada jadwal untuk hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Modal Tambah Jadwal --}}
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <form action="{{ route('admin.jadwal.store') }}" method="POST"
                class="modal-content border-0 rounded-4 shadow-lg">
                @csrf
                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0 text-primary">Tambah Jadwal</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Hari</label>
                            <select name="hari" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Hari --</option>
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                                    <option value="{{ $h }}">{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Selesai</label>
                            <input type="time" name="jam_habis" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Mata Pelajaran</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Mapel --</option>
                                @foreach ($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Guru Pengajar</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Guru --</option>
                                @foreach ($teachers as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Jadwal --}}
    @foreach ($schedules as $j)
        <div class="modal fade" id="modalEditJadwal{{ $j->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <form action="{{ route('admin.jadwal.update', $j->id) }}" method="POST"
                    class="modal-content border-0 rounded-4 shadow-lg">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-primary">Edit Jadwal</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Hari</label>
                                <select name="hari" class="form-select" required>
                                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                                        <option value="{{ $h }}" {{ $j->hari == $h ? 'selected' : '' }}>
                                            {{ $h }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control" value="{{ $j->jam_mulai }}"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Jam Selesai</label>
                                <input type="time" name="jam_habis" class="form-control" value="{{ $j->jam_habis }}"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Mata Pelajaran</label>
                                <select name="subject_id" class="form-select" required>
                                    @foreach ($subjects as $s)
                                        <option value="{{ $s->id }}"
                                            {{ $j->subject_id == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Guru Pengajar</label>
                                <select name="teacher_id" class="form-select" required>
                                    @foreach ($teachers as $g)
                                        <option value="{{ $g->id }}"
                                            {{ $j->teacher_id == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama_guru }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection

@push('scripts')
    @include('components.scripts')
@endpush
