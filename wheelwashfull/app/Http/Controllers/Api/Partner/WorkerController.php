<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Constants\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = User::where('role', UserRole::WORKER)
            ->whereHas('workerProfile', function ($query) {
                $query->where('partner_id', auth()->id());
            })
            ->with(['workerProfile'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $workers->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'mobile_number' => $w->mobile_number,
                    'status' => $w->status,
                    'current_status' => $w->workerProfile->current_status ?? 'offline',
                    'latitude' => $w->workerProfile->latitude ?? null,
                    'longitude' => $w->workerProfile->longitude ?? null,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|unique:users,mobile_number',
            'password' => 'required|string|min:6',
            'status' => 'nullable|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'mobile_number' => $validated['mobile_number'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::WORKER,
            'status' => $validated['status'] ?? 'active',
        ]);

        WorkerProfile::create([
            'user_id' => $user->id,
            'partner_id' => auth()->id(),
            'current_status' => 'offline',
        ]);

        return response()->json(['success' => true, 'message' => 'Worker added successfully.', 'data' => $user], 201);
    }

    public function show($id)
    {
        $worker = $this->getWorker($id);
        return response()->json(['success' => true, 'data' => $worker->load('workerProfile')]);
    }

    public function update(Request $request, $id)
    {
        $worker = $this->getWorker($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'mobile_number' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($worker->id)],
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $worker->update($validated);

        return response()->json(['success' => true, 'message' => 'Worker updated successfully.']);
    }

    public function destroy($id)
    {
        $worker = $this->getWorker($id);
        $worker->delete(); // Or soft delete
        return response()->json(['success' => true, 'message' => 'Worker deleted successfully.']);
    }

    public function location($id)
    {
        $worker = $this->getWorker($id);
        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $worker->workerProfile->latitude ?? null,
                'longitude' => $worker->workerProfile->longitude ?? null,
                'last_updated' => $worker->workerProfile->updated_at ?? null,
            ]
        ]);
    }

    private function getWorker($id)
    {
        return User::where('role', UserRole::WORKER)
            ->whereHas('workerProfile', function ($query) {
                $query->where('partner_id', auth()->id());
            })->findOrFail($id);
    }
}
