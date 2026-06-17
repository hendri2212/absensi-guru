@extends('layouts.app')
@section('title', 'Backup & Restore')

@section('content')
    <div class="container py-4">

        {{-- Header --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow">
            <div class="card-body px-4 py-4">
                <h5 class="fw-bold mb-1 text-white">Backup & Restore Database</h5>
                <p class="mb-0 text-white opacity-75 small">
                    Download backup atau restore database dari file backup sebelumnya
                </p>
            </div>
        </div>

        <div class="row g-4">

            {{-- Backup --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success-subtle rounded-3 p-3">
                                <i class="bi bi-download fs-4 text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Download Backup</h6>
                                <small class="text-muted">Export seluruh database ke file .sql</small>
                            </div>
                        </div>
                        <p class="text-muted small mb-4">
                            File backup berisi seluruh data termasuk siswa, guru, absensi, dan nilai.
                            Simpan file ini di tempat yang aman.
                        </p>
                        <a href="{{ route('admin.backup.download') }}" class="btn btn-success w-100">
                            <i class="bi bi-download me-2"></i>Download Backup Sekarang
                        </a>
                    </div>
                </div>
            </div>

            {{-- Restore --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-warning-subtle rounded-3 p-3">
                                <i class="bi bi-upload fs-4 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Restore Database</h6>
                                <small class="text-muted">Import file .sql untuk memulihkan data</small>
                            </div>
                        </div>
                        <div class="alert alert-warning small rounded-3 mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Perhatian:</strong> Restore akan menimpa data yang ada sekarang.
                            Pastikan file backup valid sebelum melanjutkan.
                        </div>
                        <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data"
                            id="formRestore">
                            @csrf
                            <div class="mb-3">
                                <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                                @error('backup_file')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="button" class="btn btn-warning w-100" id="btnRestore">
                                <i class="bi bi-upload me-2"></i>Restore Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    @include('components.scripts')
@endpush
