<?php

namespace App\Http\Middleware;

use App\Constants\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrCityAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! UserRole::isAdminRole($request->user()->role)) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access is required.',
            ], 403);
        }

        return $next($request);
    }
}
