<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CityScopeService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(CityScopeService $cityScope)
    {
        $query = User::where('role', 'customer');
        $cityScope->apply($query, auth()->user());

        $customers = $query->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function show($id, CityScopeService $cityScope)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $cityScope->ensureCanAccessModel(auth()->user(), $customer);
        $customer->load(['vehicles', 'addresses', 'bookings' => function($q) {
            $q->latest()->take(5);
        }]);
        return response()->json(['success' => true, 'data' => $customer]);
    }
}
