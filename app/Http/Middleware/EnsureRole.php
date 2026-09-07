<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Admins pass every role check; other roles must match exactly.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->is_active) {
            abort(403, 'Akun tidak aktif.');
        }

        if (! $user->isAdmin() && ! in_array($user->role, $roles, true)) {
            abort(403, 'Halaman ini hanya untuk peran lain.');
        }

        return $next($request);
    }
}
