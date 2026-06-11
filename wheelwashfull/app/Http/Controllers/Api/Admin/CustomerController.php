<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function show($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $customer->load(['vehicles', 'addresses', 'bookings' => function($q) {
            $q->latest()->take(5);
        }]);
        return response()->json(['success' => true, 'data' => $customer]);
    }
}
