@extends('admin.layouts.app')

@section('title', 'Notification Detail')
@section('header-title', 'Notification Detail')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                @if($notification->image_url)
                    <img src="{{ $notification->image_url }}" class="img-fluid rounded mb-3" alt="{{ $notification->title }}">
                @endif
                <h4>{{ $notification->title }}</h4>
                <p class="text-muted">{{ $notification->message }}</p>
                <dl class="row mb-0">
                    <dt class="col-5">Target</dt><dd class="col-7">{{ $notification->target_type }}</dd>
                    <dt class="col-5">Redirect</dt><dd class="col-7">{{ $notification->redirect_type }} {{ $notification->redirect_value }}</dd>
                    <dt class="col-5">Send Type</dt><dd class="col-7">{{ $notification->send_type }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7"><span class="badge bg-primary">{{ $notification->status }}</span></dd>
                    <dt class="col-5">Scheduled</dt><dd class="col-7">{{ optional($notification->scheduled_at)->format('d M Y, h:i A') ?: '-' }}</dd>
                    <dt class="col-5">Sent</dt><dd class="col-7">{{ optional($notification->sent_at)->format('d M Y, h:i A') ?: '-' }}</dd>
                </dl>
                @if(in_array($notification->status, ['draft', 'scheduled', 'failed']))
                    <form method="POST" action="{{ route('admin.notifications.send', $notification) }}" class="mt-3" onsubmit="return confirm('Send this notification now?')">
                        @csrf
                        <button class="btn btn-success w-100">Send Now</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col"><div class="card"><div class="card-body"><div class="text-muted">Targets</div><h3>{{ $notification->recipients->count() }}</h3></div></div></div>
            <div class="col"><div class="card"><div class="card-body"><div class="text-muted">Sent</div><h3 class="text-success">{{ $sentCount }}</h3></div></div></div>
            <div class="col"><div class="card"><div class="card-body"><div class="text-muted">Failed</div><h3 class="text-danger">{{ $failedCount }}</h3></div></div></div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>User</th><th>Role</th><th>Status</th><th>Sent At</th><th>Failed Reason</th></tr></thead>
                    <tbody>
                        @foreach($notification->recipients as $recipient)
                            <tr>
                                <td>{{ $recipient->user?->name }}<div class="small text-muted">{{ $recipient->user?->mobile_number }}</div></td>
                                <td>{{ $recipient->user?->role }}</td>
                                <td><span class="badge bg-{{ $recipient->status === 'sent' ? 'success' : ($recipient->status === 'failed' ? 'danger' : 'warning') }}">{{ $recipient->status }}</span></td>
                                <td>{{ optional($recipient->sent_at)->format('d M Y, h:i A') ?: '-' }}</td>
                                <td class="text-danger small">{{ $recipient->error_message }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
