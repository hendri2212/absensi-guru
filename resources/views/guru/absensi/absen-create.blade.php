@extends('layouts.app')

@section('title', 'Form Absensi - ' . $schedule->subject->nama_mapel)

@section('content')
    <div class="container py-4">

        {{-- Header Gradient --}}
        <div class="card border-0 rounded-4 mb-4 bg-gradient-header shadow-md">
            <div class="card-body px-4 py-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('guru.absensi') }}" class="btn btn-outline-light border-0 btn-sm p-1 lh-1">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">{{ $schedule->subject->nama_mapel }}</h5>
                        <p class="mb-0 text-white opacity-75 small">
                            Kelas {{ $schedule->classroom->tingkat }}-{{ $schedule->classroom->paralel }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('guru.absensi.store') }}" method="POST">
            @csrf
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

            @foreach ($students as $index => $student)
                <div class="card border-0 shadow rounded-3 mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark">{{ $student->nama }}</div>
                                <small class="text-muted">NIS: {{ $student->nis }}</small>
                            </div>

                            <input type="hidden" name="absensi[{{ $index }}][student_id]"
                                value="{{ $student->id }}">

                            {{-- Status Absensi - Desktop: teks lengkap, Mobile: singkatan --}}
                            <div class="btn-group" role="group">
                                @foreach (['Hadir' => 'success', 'Izin' => 'primary', 'Sakit' => 'warning', 'Alpa' => 'danger'] as $status => $color)
                                    <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]"
                                        id="{{ strtolower($status) }}-{{ $student->id }}" value="{{ $status }}"
                                        {{ $status == 'Hadir' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-{{ $color }} px-2 px-sm-3"
                                        for="{{ strtolower($status) }}-{{ $student->id }}">
                                        <span class="d-none d-sm-inline">{{ $status }}</span>
                                        <span class="d-sm-none">{{ substr($status, 0, 1) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary w-100 mt-3 py-2 fw-semibold">
                <i class="bi bi-check2-all me-2"></i>Simpan Absensi
            </button>
        </form>
    </div>
@endsection
