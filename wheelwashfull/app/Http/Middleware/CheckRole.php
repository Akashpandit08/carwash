<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($request->is('partner/*')) {
                return redirect()->route('partner.login');
            }

            if ($request->is('customer/*')) {
                return redirect()->route('customer.login');
            }

            return redirect()->route('admin.login');
        }

        if (!$request->user()->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to access this resource.',
                    'required_roles' => $roles,
                    'your_role' => $request->user()->role,
                ], 403);
            }

            // Redirect to appropriate login/home instead of hard 403
            $role = $request->user()->role;
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role === 'partner') {
                return redirect()->route('partner.jobs.today');
            } elseif ($role === 'customer') {
                return redirect()->route('customer.home');
            } elseif (in_array($role, ['worker', 'pickup_driver'], true)) {
                abort(403, 'Unauthorized.');
            }

            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
