{{-- Top Navbar --}}
<header class="navbar navbar-expand bg-white shadow sticky-top py-2">
    <div class="container">
        <div class="d-flex align-items-center gap-2">
            <a href="/" class="text-decoration-none">
                <img src="{{ asset('img/logo-smpn1.png') }}" alt="logo" width="45" height="45" class="rounded-3">
            </a>
            <div>
                <span class="fw-bold text-primary fs-5">ABSENSI</span>
                <small class="text-muted ms-1">| {{ Auth::user()->role ?? 'Guest' }}</small>
            </div>
        </div>

        <div class="auth-links">
            @auth
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2 mt-2">
                        <li>
                            <h6 class="dropdown-header fw-semibold text-primary px-3">Menu Profil</h6>
                        </li>

                        @if (Auth::user()->role == 'Admin')
                            <li>
                                <a class="dropdown-item px-3" href="{{ route('admin.tahun-ajaran.index') }}">
                                    <i class="bi bi-calendar-check me-2 text-primary"></i>Tahun Akademik
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item px-3" href="{{ route('admin.backup.index') }}">
                                    <i class="bi bi-database me-2 text-success"></i>Backup & Restore
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                        @endif

                        @if (Auth::user()->role == 'Guru')
                            <li>
                                <a class="dropdown-item px-3" href="{{ route('guru.profile') }}">
                                    <i class="bi bi-person-circle me-2 text-primary"></i>Profil Saya
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                        @endif

                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger px-3">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-pill px-4">Login</a>
            @endauth
        </div>
    </div>
</header>

{{-- Bottom Navigation (Semua Device) --}}
@auth
    <nav class="navbar fixed-bottom bg-white shadow border-top">
        <div class="container">
            <div class="row w-100 g-0 text-center">

                {{-- MENU KHUSUS ADMIN --}}
                @if (Auth::user()->role == 'Admin')
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->routeIs('admin.dashboard') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2 fs-5"></i>
                            <div class="small" style="font-size: 10px;">Dashboard</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('admin/guru*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('admin.guru.index') }}">
                            <i class="bi bi-person-badge fs-5"></i>
                            <div class="small" style="font-size: 10px;">Guru</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('admin/mapel*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('admin.mapel.index') }}">
                            <i class="bi bi-book fs-5"></i>
                            <div class="small" style="font-size: 10px;">Mapel</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('admin/kelas*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('admin.kelas.index') }}">
                            <i class="bi bi-door-open fs-5"></i>
                            <div class="small" style="font-size: 10px;">Kelas</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('admin/jadwal*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('admin.jadwal.index') }}">
                            <i class="bi bi-calendar3 fs-5"></i>
                            <div class="small" style="font-size: 10px;">Jadwal</div>
                        </a>
                    </div>

                    {{-- MENU KHUSUS GURU --}}
                @elseif (Auth::user()->role == 'Guru')
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->routeIs('guru.dashboard') || request()->is('guru/rekap*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('guru.dashboard') }}">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                            <div class="small" style="font-size: 10px;">Dashboard</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('guru/absensi*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('guru.absensi') }}">
                            <i class="bi bi-calendar-check fs-5"></i>
                            <div class="small" style="font-size: 10px;">Absensi</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('guru/penilaian*') || request()->is('guru/evaluations*') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('guru.penilaian.index') }}">
                            <i class="bi bi-journal-bookmark fs-5"></i>
                            <div class="small" style="font-size: 10px;">Penilaian</div>
                        </a>
                    </div>
                    <div class="col">
                        <a class="nav-link py-2 {{ request()->is('guru/kelas*') || request()->routeIs('guru.siswa.detail') ? 'text-primary' : 'text-muted' }}"
                            href="{{ route('guru.kelas.index') }}">
                            <i class="bi bi-people fs-5"></i>
                            <div class="small" style="font-size: 10px;">Kelas</div>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </nav>
@endauth
