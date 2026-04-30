<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        $fallbackRoute = $user->isAdmin()
            ? route('admin.dashboard')
            : route('customer.dashboard');

        return redirect($fallbackRoute)
            ->with('auth_error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
