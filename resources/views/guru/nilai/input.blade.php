@extends('layouts.app')

@section('title', 'Tambah Penilaian - ' . $schedule->subject->nama_mapel)

@section('content')
    <div class="container py-4">

        {{-- Header Gradient --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow-md">
            <div class="card-body px-4 py-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">{{ $schedule->subject->nama_mapel }}</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            <i class="bi bi-door-open me-1"></i>
                            Kelas {{ $schedule->classroom->tingkat }}-{{ $schedule->classroom->paralel }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow rounded-3 no-hover">
            <div class="card-body p-4">
                <form action="{{ route('guru.evaluations.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                    <input type="hidden" name="subject_id" value="{{ $schedule->subject_id }}">

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jenis Penilaian</label>
                            <select name="jenis" class="form-select" required>
                                <option value="Tugas">Tugas</option>
                                <option value="UH">Ulangan Harian (UH)</option>
                                <option value="UTS">UTS</option>
                                <option value="UAS">UAS</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Penilaian</label>
                            <input type="text" name="nama_penilaian" class="form-control" required
                                placeholder="Contoh: UH 1, Tugas Aljabar">
                            <div class="form-text small text-muted">Contoh: UH 1, Tugas Aljabar</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            @error('tanggal')
                                <div class="text-danger small-mt-1"> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-3 text-muted small fw-semibold" width="60">NO</th>
                                    <th class="py-3 text-muted small fw-semibold">NAMA SISWA</th>
                                    <th class="py-3 text-center text-muted small fw-semibold pe-3" width="180">NILAI
                                        (0-100)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $student->nama }}</div>
                                            <small class="text-muted">NIS: {{ $student->nis }}</small>
                                        </td>
                                        <td>
                                            <input type="hidden" name="penilaian[{{ $student->id }}][student_id]"
                                                value="{{ $student->id }}">
                                            <input type="number" name="penilaian[{{ $student->id }}][nilai]"
                                                class="form-control text-center" min="0" max="100"
                                                step="1" placeholder="Nilai" style="width: 120px; margin: 0 auto;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i>Simpan Semua Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
