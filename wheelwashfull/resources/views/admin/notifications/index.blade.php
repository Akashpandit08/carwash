@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('header-title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach(['draft', 'scheduled', 'sent', 'failed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="target_type" class="form-select">
            <option value="">All targets</option>
            @foreach(['all', 'customer', 'partner', 'driver', 'worker', 'selected_users'] as $type)
                <option value="{{ $type }}" @selected(request('target_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Filter</button>
    </form>
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-success">Create Notification</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Target</th>
                    <th>Send Type</th>
                    <th>Scheduled</th>
                    <th>Status</th>
                    <th>Sent</th>
                    <th>Results</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td><strong>{{ $notification->title }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($notification->message, 80) }}</div></td>
                        <td>{{ $notification->target_type }}</td>
                        <td>{{ $notification->send_type }}</td>
                        <td>{{ optional($notification->scheduled_at)->format('d M Y, h:i A') ?: '-' }}</td>
                        <td><span class="badge bg-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger' : 'warning') }}">{{ $notification->status }}</span></td>
                        <td>{{ optional($notification->sent_at)->format('d M Y, h:i A') ?: '-' }}</td>
                        <td><span class="text-success">{{ $notification->sent_count }}</span> / <span class="text-danger">{{ $notification->failed_count }}</span> / {{ $notification->recipients_count }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.notifications.show', $notification) }}">View</a>
                            @if(in_array($notification->status, ['draft', 'scheduled', 'failed']))
                                <form class="d-inline" method="POST" action="{{ route('admin.notifications.send', $notification) }}" onsubmit="return confirm('Send this notification now?')">@csrf<button class="btn btn-sm btn-outline-success">Send</button></form>
                            @endif
                            <form class="d-inline" method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Delete this notification?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No notifications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $notifications->links() }}</div>
</div>
@endsection
