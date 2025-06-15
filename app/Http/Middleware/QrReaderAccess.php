<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QrReaderAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is authenticated and has admin or employee role
        if (!$user || !in_array($user->role, ['admin', 'employee'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied. Only admin and employee users can access QR scanner functionality.'
                ], 403);
            }

            abort(403, 'Access denied. Only admin and employee users can access the QR scanner.');
        }

        return $next($request);
    }
}
