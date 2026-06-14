<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Request $request)
    {
        $query = AppNotification::withCount([
            'recipients',
            'recipients as sent_count' => fn ($query) => $query->where('status', 'sent'),
            'recipients as failed_count' => fn ($query) => $query->where('status', 'failed'),
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $this->validateNotification($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->storeOnCloudinary('notifications')->getSecurePath();
        }

        $data['created_by'] = Auth::id();
        $data['user_id'] = Auth::id();
        $data['type'] = 'app_campaign';
        $data['channel'] = 'push';
        $data['status'] = $data['send_type'] === 'scheduled' ? 'scheduled' : 'draft';

        $notification = $this->notifications->createNotification($data, $request->input('user_ids', []));

        if ($notification->send_type === 'immediate') {
            $this->notifications->sendNow($notification);
            return redirect()->route('admin.notifications.show', $notification)->with('success', 'Notification sent.');
        }

        $this->notifications->scheduleNotification($notification);

        return redirect()->route('admin.notifications.show', $notification)->with('success', 'Notification scheduled.');
    }

    public function show(AppNotification $notification)
    {
        $notification->load(['recipients.user']);
        $sentCount = $notification->recipients->where('status', 'sent')->count();
        $failedCount = $notification->recipients->where('status', 'failed')->count();

        return view('admin.notifications.show', compact('notification', 'sentCount', 'failedCount'));
    }

    public function send(AppNotification $notification)
    {
        if (! in_array($notification->status, ['draft', 'scheduled', 'failed'], true)) {
            return back()->with('error', 'Only draft, scheduled, or failed notifications can be sent.');
        }

        $this->notifications->sendNow($notification->load('recipients.user.devices'));

        return back()->with('success', 'Notification send completed.');
    }

    public function testPush()
    {
        $user = Auth::user();

        $this->notifications->sendToUser($user, $user->role, 'WheelWash Test Push', 'This is a test push from the admin dashboard.', [
            'event_type' => 'admin_test_push',
            'screen' => 'notifications',
        ]);

        $activeDevices = $user->devices()->where('is_active', true)->count();
        $message = $activeDevices > 0
            ? "Test push queued for {$user->name}. Active devices: {$activeDevices}."
            : "Test notification row created for {$user->name}, but no active device token is registered for this admin.";

        return back()->with('success', $message);
    }

    public function destroy(AppNotification $notification)
    {
        if ($notification->image) {
            // Storage::disk('public')->delete($notification->image);
        }

        $notification->delete();

        return redirect()->route('admin.notifications.index')->with('success', 'Notification deleted.');
    }

    private function validateNotification(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'target_type' => ['required', Rule::in(['all', 'customer', 'partner', 'driver', 'worker', 'selected_users'])],
            'user_ids' => ['required_if:target_type,selected_users', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'redirect_type' => ['required', Rule::in(['home', 'services', 'service_detail', 'booking', 'booking_detail', 'offers', 'profile', 'external_url', 'custom_screen'])],
            'redirect_value' => ['nullable', 'string', 'max:255'],
            'send_type' => ['required', Rule::in(['immediate', 'scheduled'])],
            'scheduled_at' => ['required_if:send_type,scheduled', 'nullable', 'date', 'after_or_equal:now'],
        ]);
    }
}
