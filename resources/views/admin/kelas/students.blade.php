@extends('layouts.app')
@section('title', 'Data Siswa Kelas')

@section('content')
    <div class="container py-4">

        {{-- Alert error --}}
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

        {{-- Header --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </a>
                        <div>
                            <h5 class="fw-bold mb-1 text-white">
                                Siswa Kelas {{ $kelas->tingkat }}-{{ $kelas->paralel }}
                            </h5>
                            <p class="mb-0 text-white opacity-75 small">
                                Total: {{ $students->total() }} siswa terdaftar
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalImportSiswa">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            <span class="d-none d-md-inline">Import CSV</span>
                        </button>
                        <button class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal"
                            data-bs-target="#modalTambahSiswa">
                            <i class="bi bi-person-plus-fill me-1"></i>
                            <span class="d-none d-md-inline">Tambah Siswa</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search --}}
        <div class="mb-3">
            <input type="text" id="searchSiswa" class="form-control rounded-3" placeholder="Cari nama atau NIS siswa...">
        </div>

        {{-- DESKTOP: Tabel --}}
        <div class="card border-0 shadow rounded-3 overflow-hidden d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-nowrap">
                                <th class="ps-4 py-3 text-muted small fw-semibold" style="width:50px">NO</th>
                                <th class="py-3 text-muted small fw-semibold">NAMA / NIS</th>
                                <th class="py-3 text-muted small fw-semibold">JK</th>
                                <th class="py-3 text-center text-muted small fw-semibold pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $index => $s)
                                <tr>
                                    {{-- Nomor urut tetap benar di halaman 2, 3, dst --}}
                                    <td class="ps-4 text-muted small">{{ $students->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $s->nama }}</div>
                                        <small class="text-muted">{{ $s->nis }}</small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill {{ $s->jk == 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }}">
                                            {{ $s->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalDetailSiswa{{ $s->id }}" title="Detail">
                                                <i class="bi bi-eye text-secondary"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#modalEditSiswa{{ $s->id }}" title="Edit">
                                                <i class="bi bi-pencil text-primary"></i>
                                            </button>
                                            <form
                                                action="{{ route('admin.kelas.students.destroy', [$kelas->id, $s->id]) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-hapus"
                                                    data-id="{{ $s->id }}" data-nama="{{ $s->nama }}"
                                                    title="Hapus">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">
                                        <i class="bi bi-person-x display-6 d-block mb-2 opacity-50"></i>
                                        Belum ada siswa di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination desktop --}}
                @if ($students->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4 px-1">
                        <small class="text-muted">
                            {{ $students->firstItem() }}–{{ $students->lastItem() }}
                            dari {{ $students->total() }} siswa
                        </small>
                        {{ $students->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- MOBILE: Card list --}}
        <div class="d-md-none">
            @forelse($students as $s)
                <div class="card border-0 shadow rounded-3 mb-3">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-dark text-truncate">{{ $s->nama }}</div>
                                <small class="text-muted d-block">{{ $s->nis }}</small>
                                <div class="mt-1">
                                    <span
                                        class="badge rounded-pill {{ $s->jk == 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }}">
                                        {{ $s->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalDetailSiswa{{ $s->id }}">
                                    <i class="bi bi-eye text-secondary"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditSiswa{{ $s->id }}">
                                    <i class="bi bi-pencil text-primary"></i>
                                </button>
                                <form action="{{ route('admin.kelas.students.destroy', [$kelas->id, $s->id]) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-hapus"
                                        data-id="{{ $s->id }}" data-nama="{{ $s->nama }}">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted small">
                    <i class="bi bi-person-x display-6 d-block mb-2 opacity-50"></i>
                    Belum ada siswa di kelas ini.
                </div>
            @endforelse

            {{-- Pagination mobile --}}
            @if ($students->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 px-1">
                    <small class="text-muted">
                        {{ $students->firstItem() }}–{{ $students->lastItem() }}
                        dari {{ $students->total() }} siswa
                    </small>
                    {{ $students->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

    </div>

    {{-- Modal Tambah Siswa --}}
    <div class="modal fade" id="modalTambahSiswa" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('admin.kelas.students.store', $kelas->id) }}" method="POST"
                class="modal-content border-0 rounded-4 shadow-lg">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0 text-primary">Tambah Siswa Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIS</label>
                            <input type="text" name="nis" class="form-control" maxlength="10" required>
                            <div class="form-text small text-muted">Maksimal 10 karakter</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="jk" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Agama</label>
                            <select name="agama" class="form-select" required>
                                @foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $agm)
                                    <option value="{{ $agm }}">{{ $agm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Telp Siswa</label>
                            <input type="tel" name="no_telp" class="form-control" maxlength="13">
                            <div class="form-text small text-muted">Format: 08xxxxxxxxx</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Telp Ortu</label>
                            <input type="tel" name="no_telp_ortu" class="form-control" maxlength="13">
                            <div class="form-text small text-muted">Format: 08xxxxxxxxx</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit & Detail Siswa --}}
    @foreach ($students as $s)
        {{-- Modal Edit --}}
        <div class="modal fade" id="modalEditSiswa{{ $s->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form action="{{ route('admin.kelas.students.update', [$kelas->id, $s->id]) }}" method="POST"
                    class="modal-content border-0 rounded-4 shadow-lg">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-primary">Edit Data Siswa</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NIS</label>
                                <input type="text" name="nis" class="form-control" value="{{ $s->nis }}"
                                    maxlength="10" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jenis Kelamin</label>
                                <select name="jk" class="form-select" required>
                                    <option value="L" {{ $s->jk == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ $s->jk == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="{{ $s->nama }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Agama</label>
                                <select name="agama" class="form-select" required>
                                    @foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $agm)
                                        <option value="{{ $agm }}" {{ $s->agama == $agm ? 'selected' : '' }}>
                                            {{ $agm }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tanggal Lahir</label>
                                <input type="date" name="tgl_lahir" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($s->tgl_lahir)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2" required>{{ $s->alamat }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">No. Telp Siswa</label>
                                <input type="tel" name="no_telp" class="form-control" maxlength="13"
                                    value="{{ $s->no_telp }}">
                                <div class="form-text">Format: 08xxxxxxxxx</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">No. Telp Ortu</label>
                                <input type="tel" name="no_telp_ortu" class="form-control" maxlength="13"
                                    value="{{ $s->no_telp_ortu }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i>Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Detail --}}
        <div class="modal fade" id="modalDetailSiswa{{ $s->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-primary">Detail Siswa</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="text-center mb-3">
                            <div class="bg-gradient-header text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                style="width:56px;height:56px;font-size:22px;">
                                {{ strtoupper(substr($s->nama, 0, 1)) }}
                            </div>
                            <h6 class="fw-bold mb-0">{{ $s->nama }}</h6>
                            <p class="text-muted small mb-0">NIS: {{ $s->nis }}</p>
                        </div>
                        <hr class="my-3">
                        <div class="row g-3 text-start">
                            <div class="col-6">
                                <div class="text-muted small mb-1">Jenis Kelamin</div>
                                <div class="fw-semibold small">{{ $s->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small mb-1">Agama</div>
                                <div class="fw-semibold small">{{ $s->agama }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small mb-1">Tanggal Lahir</div>
                                <div class="fw-semibold small">
                                    {{ \Carbon\Carbon::parse($s->tgl_lahir)->format('d M Y') }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small mb-1">Kelas</div>
                                <div class="fw-semibold small">{{ $kelas->tingkat }}-{{ $kelas->paralel }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small mb-1">Alamat</div>
                                <div class="fw-semibold small">{{ $s->alamat }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small mb-1">No. Telp Siswa</div>
                                <div class="fw-semibold small">{{ $s->no_telp ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small mb-1">No. Telp Ortu</div>
                                <div class="fw-semibold small">{{ $s->no_telp_ortu ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalEditSiswa{{ $s->id }}" data-bs-dismiss="modal">
                            <i class="bi bi-pencil me-1"></i>Edit Data
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Import CSV --}}
    <div class="modal fade" id="modalImportSiswa" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.students.import', $kelas->id) }}" method="POST" enctype="multipart/form-data"
                class="modal-content border-0 rounded-4 shadow-lg">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0 text-primary">Import Siswa via CSV</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="alert alert-info small rounded-3 mb-3"
                        style="background-color: #eff6ff; border-left: 4px solid #3b82f6;">
                        <i class="bi bi-info-circle me-1"></i>
                        Format kolom: <code>nama, nis, jk (L/P), agama, tgl_lahir (YYYY-MM-DD), alamat, no_telp,
                            no_telp_ortu</code>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Pilih File CSV</label>
                        <input type="file" name="file_siswa" class="form-control" accept=".csv" required>
                        <div class="form-text small text-muted">File harus berekstensi .csv</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    @include('components.scripts')
@endpush
