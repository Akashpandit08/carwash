<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index()
    {
        $slots = Slot::latest('date')->get();
        return response()->json(['success' => true, 'data' => $slots]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'max_bookings' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $slot = Slot::create($validated);
        return response()->json(['success' => true, 'message' => 'Slot created.', 'data' => $slot], 201);
    }

    public function show(Slot $slot)
    {
        return response()->json(['success' => true, 'data' => $slot]);
    }

    public function update(Request $request, Slot $slot)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'max_bookings' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $slot->update($validated);
        return response()->json(['success' => true, 'message' => 'Slot updated.', 'data' => $slot]);
    }

    public function destroy(Slot $slot)
    {
        $slot->delete();
        return response()->json(['success' => true, 'message' => 'Slot deleted.']);
    }
}
