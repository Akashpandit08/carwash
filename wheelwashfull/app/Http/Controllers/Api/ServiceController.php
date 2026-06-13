<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $serviceScope = function (Builder $query) use ($request) {
            $query->where('is_active', true)
                ->where('status', 'active');
                
            if ($request->filled('service_city_id') || $request->filled('service_zone_id')) {
                $query->where(function (Builder $q) use ($request) {
                    $q->where('is_global', true);

                    if ($request->filled('service_city_id')) {
                        $q->orWhere('service_city_id', $request->service_city_id);
                    }

                    if ($request->filled('service_zone_id')) {
                        $q->orWhere('service_zone_id', $request->service_zone_id);
                    }
                });
            }
                
            $query->orderBy('sort_order')
                ->orderBy('name');
        };

        $categoryQuery = ServiceCategory::with(['services' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name');
        }])
            ->where('is_active', true);
            
        if ($request->filled('service_city_id') || $request->filled('service_zone_id')) {
            $categoryQuery->where(function (Builder $q) use ($request) {
                $q->whereNull('service_city_id')->whereNull('service_zone_id'); // Global Categories
                
                if ($request->filled('service_city_id')) {
                    $q->orWhere('service_city_id', $request->service_city_id);
                }

                if ($request->filled('service_zone_id')) {
                    $q->orWhere('service_zone_id', $request->service_zone_id);
                }
            });
        }
            
        $categories = $categoryQuery->orderBy('name')->get();

        if ($request->filled('service_city_id') || $request->filled('service_zone_id')) {
            $categories->each(fn ($category) => $category->setRelation('services', $category->services->filter(function (Service $service) use ($request) {
                return $service->is_global
                    || ($request->filled('service_city_id') && (int) $service->service_city_id === (int) $request->service_city_id)
                    || ($request->filled('service_zone_id') && (int) $service->service_zone_id === (int) $request->service_zone_id);
            })->values()));
        }
        
        $categories = $categories->filter(fn ($category) => $category->services->isNotEmpty())->values();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'services' => Service::with(['category', 'serviceCity', 'serviceZone'])->where($serviceScope)->get(),
        ]);
    }

    public function show(Service $service, Request $request)
    {
        $visible = $service->is_active
            && $service->status === 'active'
            && (
                $service->is_global
                || ($request->filled('service_city_id') && (int) $service->service_city_id === (int) $request->service_city_id)
                || ($request->filled('service_zone_id') && (int) $service->service_zone_id === (int) $request->service_zone_id)
            );

        if (!$visible) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }

        $service->load('category');

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }
}
