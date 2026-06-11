<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display list of customers
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        // Search by name or mobile
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile_number', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $customers = $query->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show customer details
     */
    public function show(User $customer)
    {
        // Make sure it's a customer
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $bookings = $customer->bookings()->with(['service', 'vehicle', 'partner'])->latest()->get();
        $bookingCount = $customer->bookings()->count();
        $totalSpent = $customer->bookings()->where('payment_status', 'paid')->sum('final_price');

        return view('admin.customers.show', compact('customer', 'bookings', 'bookingCount', 'totalSpent'));
    }
}
