<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Logs one row per public page view.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->is('admin*', 'guru*', 'settings*')) {
            VisitorLog::create([
                'session_id' => substr((string) $request->session()->getId(), 0, 64),
                'ip' => (string) $request->ip(),
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'path' => substr($request->path(), 0, 500),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);
        }

        return $response;
    }
}
