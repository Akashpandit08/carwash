<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\UserRole;
use App\Models\NotificationUser;
use App\Services\NotificationService;
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
            'user_id' => ['nullable', 'integer'],
            'role' => ['nullable', 'string', 'in:customer,partner,worker,pickup_driver,admin,city_admin,super_admin'],
        ]);

        $user = $request->user();
        $userId = (int) ($request->input('user_id') ?: $user->id);
        $role = $request->input('role') ?: $user->role;

        abort_unless($userId === (int) $user->id || UserRole::isAdminRole($user->role), 403);

        $notifications = NotificationUser::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($role) {
                $query->where('role', $role)->orWhereNull('role');
            })
            ->with(['notification' => function ($query) {
                $query->withoutGlobalScopes()->with('sound');
            }])
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 20));

        $notifications->getCollection()->transform(function (NotificationUser $recipient) {
            $notification = $recipient->notification;

            return [
                'id' => $recipient->id,
                'notification_id' => $recipient->notification_id,
                'user_id' => $recipient->user_id,
                'role' => $recipient->role,
                'title' => $notification?->title,
                'body' => $notification?->body ?: $notification?->message,
                'message' => $notification?->message,
                'type' => $notification?->event_type ?: $notification?->type,
                'data' => $notification?->data ?: [],
                'booking_id' => $notification?->booking_id,
                'screen' => $notification?->screen,
                'is_read' => (bool) $recipient->is_read,
                'read_at' => $recipient->read_at,
                'read_at_ist' => $recipient->read_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A'),
                'created_at' => $recipient->created_at,
                'created_at_ist' => $recipient->created_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A'),
                'notification' => $notification,
            ];
        });

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
        abort_unless((int) $recipient->user_id === (int) $request->user()->id || in_array($request->user()->role, ['admin', 'super_admin', 'city_admin'], true), 403);

        $recipient->update([
            'is_read' => true,
            'read_at' => now('Asia/Kolkata'),
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
            'user_id' => ['nullable', 'integer'],
            'role' => ['nullable', 'string', 'in:customer,partner,worker,pickup_driver,admin,city_admin,super_admin'],
        ]);

        $user = $request->user();
        $userId = (int) ($request->input('user_id') ?: $user->id);
        $role = $request->input('role') ?: $user->role;

        abort_unless($userId === (int) $user->id || UserRole::isAdminRole($user->role), 403);

        NotificationUser::where('user_id', $userId)
            ->where(function ($query) use ($role) {
                $query->where('role', $role)->orWhereNull('role');
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now('Asia/Kolkata'),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function test(Request $request, NotificationService $notifications)
    {
        abort_unless(app()->isLocal() || config('app.debug'), 404);

        $user = $request->user();
        $notifications->sendToUser($user, $user->role, 'Test Notification', 'This is a test notification from WheelWash.', [
            'event_type' => 'test_notification',
            'screen' => 'notifications',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification queued for current user.',
        ]);
    }
}
