<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        if (! in_array(strtolower($user->role), array_map('strtolower', $roles))) {
            abort(403, 'Akses Ditolak');
        }

        return $next($request);
    }
}
