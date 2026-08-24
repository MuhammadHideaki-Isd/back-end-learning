<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAccessLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasAccessLevel()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
