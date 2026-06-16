@extends('layouts.app')

@section('title', 'Preview Kenaikan Kelas')

@section('content')
    <div class="container py-4 pb-5 mb-4">

        {{-- Header --}}
        <div class="card border-0 rounded-3 mb-4 bg-gradient-header">
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.kelas.promote') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Preview Kenaikan Kelas</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            Centang siswa yang <strong>tidak naik kelas</strong>, lalu eksekusi
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Warning --}}
        <div class="alert alert-warning rounded-3 d-flex gap-2 align-items-start mb-4">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
            <div>
                <div class="fw-semibold">Perhatikan sebelum eksekusi</div>
                <small>Setelah dieksekusi, <strong>classroom_id</strong> siswa akan berubah dan riwayat perpindahan
                    akan tercatat permanen. Proses ini <strong>tidak dapat dibatalkan</strong>.</small>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fw-bold fs-4 text-primary">
                        {{ collect($preview)->sum(fn($p) => $p['students']->count()) }}
                    </div>
                    <div class="text-muted small">Naik Kelas</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fw-bold fs-4 text-warning">
                        {{ collect($preview)->where('lulus', true)->sum(fn($p) => $p['students']->count()) }}
                    </div>
                    <div class="text-muted small">Lulus</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fw-bold fs-4 text-secondary">
                        {{ collect($preview)->sum(fn($p) => $p['dikecualikan']->count()) }}
                    </div>
                    <div class="text-muted small">Dikecualikan</div>
                </div>
            </div>
        </div>

        {{-- Single form untuk preview ulang + eksekusi --}}
        <form id="formPreview" action="{{ route('admin.kelas.promote.preview') }}" method="POST">
            @csrf

            {{-- Kirim ulang mapping --}}
            @foreach ($preview as $item)
                <input type="hidden" name="mapping[{{ $item['from']->id }}]"
                    value="{{ $item['lulus'] ? 'lulus' : $item['to']->id }}">
            @endforeach

            {{-- Kirim ulang siswa yang sudah dikecualikan sebelumnya --}}
            @foreach ($preview as $item)
                @foreach ($item['dikecualikan'] as $student)
                    <input type="hidden" name="tidak_naik[]" value="{{ $student->id }}">
                @endforeach
            @endforeach

            {{-- Detail per kelas --}}
            @foreach ($preview as $item)
                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold">
                                    Kelas {{ $item['from']->tingkat }}-{{ $item['from']->paralel }}
                                </span>
                                <i class="bi bi-arrow-right text-muted"></i>
                                @if ($item['lulus'])
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1">
                                        <i class="bi bi-mortarboard me-1"></i>Lulus
                                    </span>
                                @elseif ($item['to'])
                                    <span class="badge bg-success-subtle text-success px-2 py-1">
                                        Kelas {{ $item['to']->tingkat }}-{{ $item['to']->paralel }}
                                    </span>
                                @endif
                            </div>
                            <span class="badge bg-primary-subtle text-primary">
                                {{ $item['students']->count() }} siswa naik
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-2 border-0 text-muted small fw-bold" style="width: 40px"></th>
                                        <th class="py-2 border-0 text-muted small fw-bold">NAMA</th>
                                        <th class="py-2 border-0 text-muted small fw-bold">NIS</th>
                                        <th class="py-2 pe-4 border-0 text-muted small fw-bold text-end">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Siswa yang naik --}}
                                    @foreach ($item['students'] as $student)
                                        <tr>
                                            <td class="ps-4 py-2">
                                                <input type="checkbox" name="tidak_naik[]" value="{{ $student->id }}"
                                                    class="form-check-input mt-0" title="Centang untuk tidak naik">
                                            </td>
                                            <td class="py-2 small fw-semibold">{{ $student->nama }}</td>
                                            <td class="py-2 small text-muted">{{ $student->nis }}</td>
                                            <td class="py-2 pe-4 text-end">
                                                @if ($item['lulus'])
                                                    <span class="badge bg-warning-subtle text-warning small">Lulus</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success small">
                                                        → {{ $item['to']->tingkat }}-{{ $item['to']->paralel }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Siswa dikecualikan --}}
                                    @foreach ($item['dikecualikan'] as $student)
                                        <tr class="opacity-50">
                                            <td class="ps-4 py-2">
                                                <input type="checkbox" name="tidak_naik[]" value="{{ $student->id }}"
                                                    class="form-check-input mt-0" checked>
                                            </td>
                                            <td class="py-2 small fw-semibold text-decoration-line-through">
                                                {{ $student->nama }}
                                            </td>
                                            <td class="py-2 small text-muted">{{ $student->nis }}</td>
                                            <td class="py-2 pe-4 text-end">
                                                <span class="badge bg-secondary-subtle text-secondary small">
                                                    Dikecualikan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.kelas.promote') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali & Ubah
                </a>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Terapkan Pengecualian
                </button>
                <button type="submit" formaction="{{ route('admin.kelas.promote.execute') }}" class="btn btn-danger">
                    <i class="bi bi-check-circle me-1"></i>Eksekusi Kenaikan Kelas
                </button>
            </div>
        </form>

    </div>
@endsection
