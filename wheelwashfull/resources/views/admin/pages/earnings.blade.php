@extends('admin.layout')
@section('title', 'Earnings')
@section('page_title', 'Earnings')
@section('content')
<div class="container-fluid">
    <h2 class="mb-3">Earnings</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Total Revenue</div><h3>Rs {{ number_format($totalRevenue, 2) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">COD Revenue</div><h3>Rs {{ number_format($codRevenue, 2) }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Online Revenue</div><h3>Rs {{ number_format($onlineRevenue, 2) }}</h3></div></div></div>
    </div>
    <div class="card"><div class="card-header">City Wise Revenue</div><table class="table mb-0"><thead><tr><th>City</th><th>Paid Bookings</th><th>Revenue</th></tr></thead><tbody>@foreach($cityRows as $row)<tr><td>{{ $row['city']->name }}</td><td>{{ $row['bookings'] }}</td><td>Rs {{ number_format($row['revenue'], 2) }}</td></tr>@endforeach</tbody></table></div>
</div>
@endsection
