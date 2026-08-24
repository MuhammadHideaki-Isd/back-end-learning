<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessLevel
{
    public function handle(Request $request, Closure $next): Response
    {
        if (($request->user()?->access_level ?? 0) <= 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
