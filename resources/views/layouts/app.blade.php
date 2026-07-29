<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Absensi-Guru')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
</head>

<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then((reg) => console.log('SW registered:', reg))
            .catch((err) => console.error('SW registration failed:', err));
    }
</script>

<body class="d-flex flex-column min-vh-100">

    @if (!request()->is('login'))
        @include('components.navbar')
    @endif

    <!-- PERBAIKAN 2: HAPUS mt-4 mb-5, biar spacing diatur per halaman -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    @if (!request()->is('login'))
        @include('components.footer')
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('components.scripts')
    @stack('scripts')
</body>

</html>
