@extends('admin.layouts.app')

@section('title', 'Create Notification')
@section('header-title', 'Create Notification')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <img id="image-preview" class="mt-3 rounded d-none" style="max-width: 220px; max-height: 120px; object-fit: cover;">
                </div>
                <div class="col-12">
                    <label class="form-label">Message</label>
                    <textarea name="message" rows="4" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Target Type</label>
                    <select name="target_type" id="target_type" class="form-select" onchange="toggleUsers()" required>
                        @foreach(['all', 'customer', 'partner', 'driver', 'worker', 'selected_users'] as $type)
                            <option value="{{ $type }}" @selected(old('target_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 d-none" id="selected-users-wrap">
                    <label class="form-label">Selected Users</label>
                    <select name="user_ids[]" class="form-select" multiple size="6">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>{{ $user->name }} - {{ $user->mobile_number }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple users.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Redirect Type</label>
                    <select name="redirect_type" id="redirect_type" class="form-select" onchange="updateRedirectHelp()" required>
                        @foreach(['home', 'services', 'service_detail', 'booking', 'booking_detail', 'offers', 'profile', 'external_url', 'custom_screen'] as $type)
                            <option value="{{ $type }}" @selected(old('redirect_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    <div id="redirect-help" class="form-text"></div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Redirect Value</label>
                    <input name="redirect_value" value="{{ old('redirect_value') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Send Type</label>
                    <select name="send_type" id="send_type" class="form-select" onchange="toggleSchedule()" required>
                        <option value="immediate" @selected(old('send_type') === 'immediate')>Send immediately</option>
                        <option value="scheduled" @selected(old('send_type') === 'scheduled')>Schedule later</option>
                    </select>
                </div>
                <div class="col-md-4 d-none" id="schedule-wrap">
                    <label class="form-label">Scheduled At</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="form-control">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Notification</button>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(event) {
    const preview = document.getElementById('image-preview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.classList.remove('d-none');
}
function toggleUsers() {
    document.getElementById('selected-users-wrap').classList.toggle('d-none', document.getElementById('target_type').value !== 'selected_users');
}
function toggleSchedule() {
    document.getElementById('schedule-wrap').classList.toggle('d-none', document.getElementById('send_type').value !== 'scheduled');
}
function updateRedirectHelp() {
    const help = {
        service_detail: 'Enter service ID.',
        booking_detail: 'Enter booking ID.',
        external_url: 'Enter full URL.',
        custom_screen: 'Enter app route name.'
    };
    document.getElementById('redirect-help').textContent = help[document.getElementById('redirect_type').value] || 'No redirect value needed unless this target requires one.';
}
toggleUsers(); toggleSchedule(); updateRedirectHelp();
</script>
@endpush
