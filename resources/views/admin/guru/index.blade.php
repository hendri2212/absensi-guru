@extends('layouts.app')
@section('title', 'Manajemen Data Guru')

@section('content')
    <div class="container py-4">

        {{-- Header - PAKAI CLASS bg-gradient-header --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Data Guru</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            Total: {{ $teachers->total() }} guru terdaftar
                        </p>
                    </div>
                    <a href="{{ route('admin.guru.create') }}" class="btn btn-light fw-semibold flex-shrink-0">
                        <i class="bi bi-person-plus-fill me-1"></i>Tambah Guru
                    </a>
                </div>
            </div>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.guru.index') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control rounded-start-3"
                    placeholder="Cari nama atau NIP guru..." value="{{ $search ?? '' }}">
                @if ($search)
                    <a href="{{ route('admin.guru.index') }}" class="btn btn-outline-secondary" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                <button class="btn btn-primary rounded-end-3" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>

        {{-- DESKTOP: Tabel (md ke atas) --}}
        <div class="card border-0 shadow rounded-3 overflow-hidden d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-nowrap">
                                <th class="ps-4 py-3 text-muted small fw-semibold">NAMA / NIP</th>
                                <th class="py-3 text-muted small fw-semibold">JK</th>
                                <th class="py-3 text-muted small fw-semibold">KONTAK</th>
                                <th class="py-3 text-center text-muted small fw-semibold pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teachers as $teacher)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $teacher->nama_guru }}</div>
                                        <small class="text-muted">{{ $teacher->nip ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill {{ $teacher->jk == 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }}">
                                            {{ $teacher->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        <i class="bi bi-telephone me-1"></i>{{ $teacher->no_telp }}
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#detailModal{{ $teacher->id }}"
                                                title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ route('admin.guru.edit', $teacher->id) }}"
                                                class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.guru.destroy', $teacher->id) }}" method="POST"
                                                class="d-inline" id="form-hapus-{{ $teacher->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus"
                                                    data-id="{{ $teacher->id }}" data-nama="{{ $teacher->nama_guru }}"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">
                                        <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                        Belum ada data guru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MOBILE: Card list (di bawah md) --}}
        <div class="d-md-none">
            @forelse($teachers as $teacher)
                <div class="card border-0 shadow rounded-3 mb-3">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-dark text-truncate">{{ $teacher->nama_guru }}</div>
                                <small class="text-muted d-block">{{ $teacher->nip ?? '-' }}</small>
                                <div class="mt-1">
                                    <span
                                        class="badge rounded-pill {{ $teacher->jk == 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }}">
                                        {{ $teacher->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#detailModal{{ $teacher->id }}" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('admin.guru.edit', $teacher->id) }}"
                                    class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil text-warning"></i>
                                </a>
                                <form action="{{ route('admin.guru.destroy', $teacher->id) }}" method="POST"
                                    class="d-inline" id="form-hapus-{{ $teacher->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-hapus"
                                        data-id="{{ $teacher->id }}" data-nama="{{ $teacher->nama_guru }}"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted small">
                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                    Belum ada data guru.
                </div>
            @endforelse
        </div>

        {{-- Modal Detail (dipercantik) --}}
        @foreach ($teachers as $teacher)
            <div class="modal fade" id="detailModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <h6 class="fw-bold mb-0 text-primary">Detail Guru</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-2 px-4">
                            <div class="text-center mb-3">
                                <div class="bg-gradient-header text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                    style="width:64px;height:64px;font-size:24px;">
                                    {{ strtoupper(substr($teacher->nama_guru, 0, 1)) }}
                                </div>
                                <h6 class="fw-bold mb-0">{{ $teacher->nama_guru }}</h6>
                                <p class="text-muted small mb-0">NIP: {{ $teacher->nip ?? '-' }}</p>
                            </div>
                            <hr class="my-3">
                            <div class="row g-3 text-start">
                                <div class="col-6">
                                    <div class="text-muted small mb-1">Jenis Kelamin</div>
                                    <div class="fw-semibold small">{{ $teacher->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1">Agama</div>
                                    <div class="fw-semibold small">{{ $teacher->agama ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1">Tanggal Lahir</div>
                                    <div class="fw-semibold small">{{ $teacher->tgl_lahir ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small mb-1">No. Telepon</div>
                                    <div class="fw-semibold small">{{ $teacher->no_telp ?? '-' }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small mb-1">Alamat</div>
                                    <div class="fw-semibold small">{{ $teacher->alamat ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 pb-4 px-4">
                            <a href="{{ route('admin.guru.edit', $teacher->id) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i>Edit Data
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        @if ($teachers->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 px-1">
                <small class="text-muted">
                    {{ $teachers->firstItem() }}–{{ $teachers->lastItem() }} dari {{ $teachers->total() }} guru
                </small>
                {{ $teachers->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @include('components.scripts')
@endpush
