@extends('layouts.app')

@section('title', 'Kenaikan Kelas')

@section('content')
    <div class="container py-4 pb-5 mb-4">

        {{-- Header --}}
        <div class="card border-0 rounded-3 mb-4 bg-gradient-header">
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Kenaikan Kelas</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            Tentukan tujuan tiap kelas, lalu preview sebelum dieksekusi
                            @if ($activeYear)
                                &mdash; Tahun Ajaran Aktif:
                                <strong>{{ $activeYear->tahun }} {{ $activeYear->semester }}</strong>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if (!$activeYear)
            <div class="alert alert-warning rounded-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Tidak ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu sebelum memproses kenaikan kelas.
            </div>
        @endif

        <form action="{{ route('admin.kelas.promote.preview') }}" method="POST">
            @csrf

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-arrow-up-circle text-primary me-2"></i>
                        Mapping Kenaikan Kelas
                    </h6>
                    <small class="text-muted">
                        Pilih tujuan untuk setiap kelas. Biarkan kosong untuk melewati kelas tersebut.
                    </small>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 text-muted small fw-bold" style="width:30%">KELAS ASAL
                                    </th>
                                    <th class="py-3 border-0 text-muted small fw-bold" style="width:15%">JUMLAH SISWA AKTIF
                                    </th>
                                    <th class="py-3 border-0 text-muted small fw-bold"></th>
                                    <th class="py-3 pe-4 border-0 text-muted small fw-bold" style="width:35%">TUJUAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $kelas)
                                    <tr class="{{ $kelas->students_count == 0 ? 'opacity-50' : '' }}">
                                        <td class="ps-4">
                                            <div class="fw-semibold">
                                                Kelas {{ $kelas->tingkat }}-{{ $kelas->paralel }}
                                            </div>
                                            @if ($kelas->students_count == 0)
                                                <small class="text-muted">Tidak ada siswa aktif</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1">
                                                {{ $kelas->students_count }} siswa
                                            </span>
                                        </td>
                                        <td class="text-center text-muted">
                                            <i class="bi bi-arrow-right"></i>
                                        </td>
                                        <td class="pe-4">
                                            <select name="mapping[{{ $kelas->id }}]" class="form-select form-select-sm"
                                                style="min-width: 200px; color: #212529; background-color: #fff;"
                                                {{ $kelas->students_count == 0 ? 'disabled' : '' }}>

                                                <option value="" style="color: #212529;">— Lewati —</option>

                                                @if ($allowedTargets[$kelas->id] === 'lulus')
                                                    <option value="lulus" style="color:#198754; font-weight:600;">
                                                        🎓 Lulus (tidak pindah ke kelas manapun)
                                                    </option>
                                                @elseif ($allowedTargets[$kelas->id] instanceof \Illuminate\Support\Collection)
                                                    @foreach ($allowedTargets[$kelas->id] as $tujuan)
                                                        <option value="{{ $tujuan->id }}" style="color: #212529;">
                                                            Kelas {{ $tujuan->tingkat }}-{{ $tujuan->paralel }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option disabled style="color: #6c757d;">Tidak ada tujuan tersedia
                                                    </option>
                                                @endif

                                            </select>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small">
                                            <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                            Belum ada data kelas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" {{ $classes->isEmpty() || !$activeYear ? 'disabled' : '' }}>
                    <i class="bi bi-eye me-1"></i>Preview Hasil
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    @include('components.scripts')
@endpush
