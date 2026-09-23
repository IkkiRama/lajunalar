<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdministrator
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->guest(route('google.redirect'));
        }

        abort_unless($request->user()->role === 'admin', 403, 'Akun ini tidak memiliki akses administrator.');

        return $next($request);
    }
}
