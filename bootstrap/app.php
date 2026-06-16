<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 1. Buat instance application dan tampung ke variabel $app
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: '/admin/dashboard'
        );

        $middleware->trustProxies(at: '*'); // tambah di sini
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// 2. Sekarang variabel $app sudah ada, baru kita set storage path-nya
$app->useStoragePath(env('APP_STORAGE', base_path('storage')));
// Tips: default-nya dikembalikan ke base_path('storage') agar lokal kamu tidak bingung mencari folder /tmp milik linux.

// 3. Return aplikasi ke sistem Laravel
return $app;
