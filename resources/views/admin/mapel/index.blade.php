@extends('layouts.app')
@section('title', 'Mata Pelajaran')

@section('content')
    <div class="container py-4">

        {{-- Header - PAKAI CLASS bg-gradient-header --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <h5 class="fw-bold mb-1 text-white">Mata Pelajaran</h5>
                <p class="mb-0 text-white opacity-75 small">
                    Total: {{ $subjects->count() }} mata pelajaran terdaftar
                </p>
            </div>
        </div>

        <div class="row g-4">

            {{-- Tabel --}}
            <div class="col-lg-8 order-2 order-lg-1">
                <div class="card border-0 shadow rounded-3 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th class="ps-4 py-3 text-muted small fw-semibold" style="width:60px">NO</th>
                                        <th class="py-3 text-muted small fw-semibold">NAMA MATA PELAJARAN</th>
                                        <th class="py-3 text-center text-muted small fw-semibold pe-4">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($subjects as $index => $s)
                                        <tr>
                                            <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                            <td class="fw-semibold text-dark">{{ $s->nama_mapel }}</td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                                        data-bs-target="#modalEditMapel{{ $s->id }}" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('admin.mapel.destroy', $s->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-hapus"
                                                            data-id="{{ $s->id }}" data-nama="{{ $s->nama_mapel }}"
                                                            title="Hapus">
                                                            <i class="bi bi-trash danger"></i>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted small">
                                                <i class="bi bi-book display-6 d-block mb-2 opacity-50"></i>
                                                Belum ada data mata pelajaran.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Tambah --}}
            <div class="col-lg-4 order-1 order-lg-2">
                <div class="card border-0 shadow rounded-3 sticky-lg-top" style="top: 1rem;">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="fw-semibold mb-0 text-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Mapel Baru
                        </h6>
                    </div>
                    <div class="card-body px-4 py-4">
                        <form action="{{ route('admin.mapel.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Nama Mapel</label>
                                <input type="text" name="nama_mapel"
                                    class="form-control @error('nama_mapel') is-invalid @enderror" placeholder="Contoh: IPA"
                                    value="{{ old('nama_mapel') }}" required>
                                @error('nama_mapel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-save me-1"></i>Simpan Mapel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Edit - DIPERCANTIK --}}
    @foreach ($subjects as $s)
        <div class="modal fade" id="modalEditMapel{{ $s->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.mapel.update', $s->id) }}" method="POST"
                    class="modal-content border-0 rounded-4 shadow-lg">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-primary">Edit Mata Pelajaran</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <label class="form-label fw-semibold small">Nama Mapel</label>
                        <input type="text" name="nama_mapel" class="form-control" value="{{ $s->nama_mapel }}" required>
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
