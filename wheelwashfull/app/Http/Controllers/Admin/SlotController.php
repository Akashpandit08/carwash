<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    /**
     * Display list of slots
     */
    public function index(Request $request)
    {
        $query = Slot::query();

        if ($request->filled('service_city_id')) {
            $query->where('service_city_id', $request->service_city_id);
        }

        // Search
        if ($request->search) {
            $query->where('start_time', 'like', '%' . $request->search . '%')
                  ->orWhere('end_time', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->status) {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        // Filter by date
        if ($request->date) {
            $query->where('date', $request->date);
        }

        $slots = $query->orderBy('date')->orderBy('start_time')->paginate(20);

        return view('admin.slots.index', compact('slots'));
    }

    /**
     * Show create slot form
     */
    public function create()
    {
        return view('admin.slots.create');
    }

    /**
     * Store new slot
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'max_bookings' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        Slot::create($validated);

        return redirect()->route('admin.slots.index')->with('success', 'Slot created successfully');
    }

    /**
     * Show edit slot form
     */
    public function edit(Slot $slot)
    {
        return view('admin.slots.edit', compact('slot'));
    }

    /**
     * Update slot
     */
    public function update(Request $request, Slot $slot)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string|after:start_time',
            'max_bookings' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $slot->update($validated);

        return redirect()->route('admin.slots.index')->with('success', 'Slot updated successfully');
    }

    /**
     * Delete slot
     */
    public function destroy(Slot $slot)
    {
        $slot->delete();

        return redirect()->route('admin.slots.index')->with('success', 'Slot deleted successfully');
    }
}
