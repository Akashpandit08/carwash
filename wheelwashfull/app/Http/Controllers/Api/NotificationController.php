<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\NotificationUser;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifications for a user by role.
     *
     * GET /api/app/notifications?user_id=ID&role=ROLE
     */
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'in:customer,partner,worker,pickup_driver,admin'],
        ]);

        $userId = $request->input('user_id');
        $role = $request->input('role');

        $notifications = NotificationUser::where('user_id', $userId)
            ->where(function ($query) use ($role) {
                $query->where('role', $role)->orWhereNull('role');
            })
            ->with(['notification' => function ($query) {
                $query->withoutGlobalScopes()->with('sound');
            }])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     *
     * POST /api/app/notifications/{id}/read
     */
    public function markRead(Request $request, $id)
    {
        $recipient = NotificationUser::findOrFail($id);

        $recipient->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read for a user + role.
     *
     * POST /api/app/notifications/read-all
     */
    public function readAll(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'in:customer,partner,worker,pickup_driver,admin'],
        ]);

        $userId = $request->input('user_id');
        $role = $request->input('role');

        NotificationUser::where('user_id', $userId)
            ->where(function ($query) use ($role) {
                $query->where('role', $role)->orWhereNull('role');
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
