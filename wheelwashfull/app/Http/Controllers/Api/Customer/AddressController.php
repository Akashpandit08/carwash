<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:home,office,other',
            'full_address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean'
        ]);

        $user = Auth::user();

        // If this is set as default, unset others
        if (!empty($validated['is_default']) && $validated['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }

        // If user has no addresses, make this default automatically
        if ($user->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        $address = $user->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'data' => $address
        ], 201);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|string|in:home,office,other',
            'full_address' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean'
        ]);

        // If this is set as default, unset others
        if (isset($validated['is_default']) && $validated['is_default']) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => $address
        ]);
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.'
        ]);
    }
}
