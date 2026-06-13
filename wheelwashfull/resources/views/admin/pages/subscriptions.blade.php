@extends('admin.layout')
@section('title', 'Subscriptions')
@section('page_title', 'Subscriptions')
@section('content')
<div class="container-fluid">
    <h2 class="mb-1">Subscriptions</h2>
    <p class="text-muted">Plans and recent customer subscriptions by city.</p>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card"><div class="card-header">Plans</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>City</th><th>Price</th><th>Washes</th><th>Status</th></tr></thead><tbody>@forelse($plans as $plan)<tr><td>{{ $plan->name }}</td><td>{{ $plan->serviceCity?->name ?? 'Global' }}</td><td>Rs {{ number_format($plan->price, 2) }}</td><td>{{ $plan->total_washes }}</td><td>{{ $plan->status }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No plans.</td></tr>@endforelse</tbody></table></div></div><div class="mt-3">{{ $plans->appends(request()->query())->links() }}</div></div>
        <div class="col-lg-5"><div class="card"><div class="card-header">Recent Subscriptions</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Customer</th><th>Plan</th><th>Status</th></tr></thead><tbody>@forelse($subscriptions as $sub)<tr><td>{{ $sub->user?->name ?? '-' }}</td><td>{{ $sub->subscriptionPlan?->name ?? '-' }}</td><td>{{ $sub->status }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">No subscriptions.</td></tr>@endforelse</tbody></table></div></div></div>
    </div>
</div>
@endsection
