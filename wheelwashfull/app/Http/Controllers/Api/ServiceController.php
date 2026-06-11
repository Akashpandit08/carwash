<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::with(['services' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('name');
        }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function show(Service $service)
    {
        if (!$service->is_active) {
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
