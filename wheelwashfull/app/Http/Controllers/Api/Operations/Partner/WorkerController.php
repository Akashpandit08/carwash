<?php

namespace App\Http\Controllers\Api\Operations\Partner;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;

class WorkerController extends Controller
{
    public function index()
    {
        // Get all workers linked to this partner via WorkerProfile
        // Fallback: If your system doesn't explicitly link them yet, we just return all workers.
        
        $workers = User::where('role', UserRole::WORKER)
            ->whereHas('workerProfile', function ($query) {
                $query->where('partner_id', auth()->id());
            })
            ->get();

        // Fallback behavior if no relations are set up
        if ($workers->isEmpty()) {
            $workers = User::where('role', UserRole::WORKER)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $workers->map(function ($worker) {
                return [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'phone' => $worker->mobile_number,
                    'status' => 'active', // Placeholder until status is tracked on user level
                ];
            }),
        ]);
    }
}
