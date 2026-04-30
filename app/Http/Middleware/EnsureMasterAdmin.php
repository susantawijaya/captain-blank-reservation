<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasterAdmin
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isMasterAdmin()) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard')
            ->with('auth_error', 'Hanya master admin yang dapat mengelola akun admin.');
    }
}
