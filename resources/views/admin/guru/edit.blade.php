@extends('layouts.app')
@section('title', 'Edit Guru')
@section('content')

    <div class="container py-4">

        {{-- Header - PAKAI CLASS bg-gradient-header (konsisten) --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <h5 class="fw-bold mb-1 text-white">Edit Data Guru</h5>
                <p class="mb-0 text-white opacity-75 small">{{ $teacher->nama_guru }}</p>
            </div>
        </div>

        {{-- Alert error - custom modern --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert"
                style="border-left: 4px solid #dc2626; background-color: #fef2f2;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                    <div>
                        <strong class="small">Gagal memperbarui data:</strong>
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

        <form action="{{ route('admin.guru.update', $teacher->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">

                {{-- Kolom Kiri: Informasi Pribadi --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h6 class="fw-semibold mb-0 text-primary">
                                <i class="bi bi-person-vcard me-2"></i>Informasi Pribadi
                            </h6>
                        </div>
                        <div class="card-body px-4 py-4">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label class="form-label fw-semibold small">NIP</label>
                                    <input type="tel" name="nip" inputmode="numeric"
                                        class="form-control @error('nip') is-invalid @enderror"
                                        value="{{ old('nip', $teacher->nip) }}" maxlength="18" pattern="[0-9]{18}"
                                        title="NIP harus 18 digit angka" required>
                                    <div class="form-text small text-muted">18 digit angka</div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label fw-semibold small">Nama Lengkap</label>
                                    <input type="text" name="nama_guru"
                                        class="form-control @error('nama_guru') is-invalid @enderror"
                                        value="{{ old('nama_guru', $teacher->nama_guru) }}" required>
                                </div>
                                <div class="col-md-4 col-6">
                                    <label class="form-label fw-semibold small">Jenis Kelamin</label>
                                    <select name="jk" class="form-select" required>
                                        <option value="L" {{ old('jk', $teacher->jk) == 'L' ? 'selected' : '' }}>
                                            Laki-laki</option>
                                        <option value="P" {{ old('jk', $teacher->jk) == 'P' ? 'selected' : '' }}>
                                            Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-6">
                                    <label class="form-label fw-semibold small">Agama</label>
                                    <select name="agama" class="form-select">
                                        @foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $ag)
                                            <option value="{{ $ag }}"
                                                {{ old('agama', $teacher->agama) == $ag ? 'selected' : '' }}>
                                                {{ $ag }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label fw-semibold small">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir" class="form-control"
                                        value="{{ old('tgl_lahir', $teacher->tgl_lahir) }}" required>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label fw-semibold small">Nomor Telepon</label>
                                    <input type="tel" name="no_telp" inputmode="numeric" class="form-control"
                                        maxlength="13" pattern="[0-9]{10,13}" title="Nomor telepon 10-13 digit angka"
                                        value="{{ old('no_telp', $teacher->no_telp) }}" required>
                                    <div class="form-text small text-muted">Format: 08xxxxxxxxx</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Alamat Lengkap</label>
                                    <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $teacher->alamat) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Kredensial + Tombol --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h6 class="fw-semibold mb-0 text-primary">
                                <i class="bi bi-shield-lock me-2"></i>Kredensial Akun
                            </h6>
                        </div>
                        <div class="card-body px-4 py-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Username</label>
                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $teacher->user->username) }}" required>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold small">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="passwordField" class="form-control"
                                        placeholder="Kosongkan jika tidak diubah">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text small text-muted">Kosongkan jika tidak ingin mengubah password.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning py-2">
                            <i class="bi bi-save me-1"></i>Perbarui Data Guru
                        </button>
                        <a href="{{ route('admin.guru.index') }}" class="btn btn-outline-secondary py-2">Batal</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

@endsection

@push('scripts')
    @include('components.scripts')
@endpush
