<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use App\Services\CityScopeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeCityData
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || app(CityScopeService::class)->isSuperAdmin($user)) {
            return $next($request);
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Booking || $parameter instanceof User || $parameter instanceof Service || $parameter instanceof Slot || $parameter instanceof Coupon) {
                app(CityScopeService::class)->ensureCanAccessModel($user, $parameter);
            }
        }

        return $next($request);
    }
}
