<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure the user is logged in and is an admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            // If an API request, return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
            }

            // If using Inertia, return a 403 error page
            return Inertia::render('Error', ['status' => 403])
                ->toResponse($request)
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
