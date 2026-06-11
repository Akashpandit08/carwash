<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SlotService;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    protected $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    public function index(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $slots = $this->slotService->getAvailableSlots($request->date, $request->service_id);

        return response()->json([
            'success' => true,
            'data' => $slots
        ]);
    }
}
