<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::with(['services' => function ($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();

        return view('customer.services.index', compact('categories'));
    }

    public function show(Service $service)
    {
        if (!$service->is_active) {
            abort(404);
        }

        $service->load('category');

        return view('customer.services.show', compact('service'));
    }
}
