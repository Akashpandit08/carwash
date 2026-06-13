<?php

namespace App\Http\Middleware;

use App\Constants\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! UserRole::isSuperAdminRole($request->user()->role)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
