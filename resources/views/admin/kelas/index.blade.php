@extends('layouts.app')
@section('title', 'Manajemen Kelas')

@section('content')
    <div class="container py-4">

        {{-- Header --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Manajemen Kelas</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            Total: {{ $classes->count() }} kelas terdaftar
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <a href="{{ route('admin.kelas.promote') }}" class="btn btn-warning btn-sm fw-semibold">
                            <i class="bi bi-arrow-up-circle me-1"></i>Kenaikan Kelas
                        </a>
                        <button class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal"
                            data-bs-target="#modalTambahKelas">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kelas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card border-0 shadow rounded-3 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-nowrap">
                                <th class="ps-4 py-3 text-muted small fw-semibold" style="width:50px">NO</th>
                                <th class="py-3 text-muted small fw-semibold">KELAS</th>
                                <th class="py-3 text-center text-muted small fw-semibold pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classes as $index => $item)
                                <tr>
                                    <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">Kelas {{ $item->tingkat }} {{ $item->paralel }}
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            @if ($item->nama_guru)
                                                <small class="text-muted">
                                                    <i class="bi bi-person me-1"></i>{{ $item->nama_guru }}
                                                </small>
                                            @endif
                                            <span class="badge bg-primary-subtle text-primary">
                                                <i class="bi bi-people-fill me-1"></i>{{ $item->jumlah_siswa }} siswa
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.kelas.students.index', $item->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="Kelola Siswa">
                                                <i class="bi bi-people-fill"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                                data-bs-target="#modalEditKelas{{ $item->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.kelas.destroy', $item->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus"
                                                    data-id="{{ $item->id }}"
                                                    data-nama="Kelas {{ $item->tingkat }} {{ $item->paralel }}"
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
                                        <i class="bi bi-grid display-6 d-block mb-2 opacity-50"></i>
                                        Belum ada data kelas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Tambah Kelas --}}
    <div class="modal fade" id="modalTambahKelas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <form action="{{ route('admin.kelas.store') }}" method="POST"
                class="modal-content border-0 rounded-4 shadow-lg">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0 text-primary">Tambah Kelas Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tingkat</label>
                        <select name="tingkat" class="form-select @error('tingkat') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Tingkat Kelas --</option>
                            <option value="VII">VII</option>
                            <option value="VIII">VIII</option>
                            <option value="IX">IX</option>
                        </select>
                        @error('tingkat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Paralel / Nama Kelas</label>
                        <select name="paralel" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Abjad Kelas --</option>
                            @foreach (range('A', 'H') as $char)
                                <option value="{{ $char }}">{{ $char }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Wali Kelas</label>
                        <select name="walas_id" class="form-select @error('walas_id') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Guru --</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->nama_guru }}</option>
                            @endforeach
                        </select>
                        @error('walas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kelas --}}
    @foreach ($classes as $item)
        <div class="modal fade" id="modalEditKelas{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <form action="{{ route('admin.kelas.update', $item->id) }}" method="POST"
                    class="modal-content border-0 rounded-4 shadow-lg">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-primary">Edit Kelas</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tingkat</label>
                            <select name="tingkat" class="form-select" required>
                                <option value="VII" {{ $item->tingkat === 'VII' ? 'selected' : '' }}>VII</option>
                                <option value="VIII" {{ $item->tingkat === 'VIII' ? 'selected' : '' }}>VIII</option>
                                <option value="IX" {{ $item->tingkat === 'IX' ? 'selected' : '' }}>IX</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Paralel / Nama Kelas</label>
                            <select name="paralel" class="form-select" required>
                                @foreach (range('A', 'H') as $char)
                                    <option value="{{ $char }}" {{ $item->paralel === $char ? 'selected' : '' }}>
                                        {{ $char }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Wali Kelas</label>
                            <select name="walas_id" class="form-select" required>
                                <option value="" disabled>-- Pilih Guru --</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                        {{ $item->walas_id == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->nama_guru }}
                                    </option>
                                @endforeach
                            </select>
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
