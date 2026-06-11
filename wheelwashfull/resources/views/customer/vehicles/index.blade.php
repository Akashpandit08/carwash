@extends('customer.layouts.app')

@section('title', 'My Vehicles - WashMate')
@section('header-title', 'My Vehicles')
@section('header-subtitle', 'Manage your vehicles')

@section('header-action')
<a href="{{ route('customer.vehicles.create') }}" class="btn btn-light btn-sm fw-semibold" style="border-radius:20px;font-size:12px;">
    <i class="bi bi-plus-lg"></i> Add
</a>
@endsection

@section('content')
<div class="mt-2">
@if($vehicles->isEmpty())
    <div class="text-center py-5 mt-4">
        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:90px;height:90px;">
            <i class="bi bi-car-front text-primary" style="font-size:44px;"></i>
        </div>
        <h5 class="fw-bold mb-2">No Vehicles Yet</h5>
        <p class="text-muted mb-4" style="font-size:14px;">Add your vehicle to start booking services</p>
        <a href="{{ route('customer.vehicles.create') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-circle me-2"></i>Add Vehicle
        </a>
    </div>
@else
    @foreach($vehicles as $vehicle)
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:56px;height:56px;">
                    @php
                        $icon = ['bike'=>'bicycle','truck'=>'truck','suv'=>'car-front-fill'][$vehicle->vehicle_type] ?? 'car-front-fill';
                    @endphp
                    <i class="bi bi-{{ $icon }} text-primary" style="font-size:26px;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="fw-bold mb-0 text-truncate">{{ $vehicle->brand }} {{ $vehicle->model }}</h6>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:10px;">{{ ucfirst($vehicle->vehicle_type) }}</span>
                    </div>
                    <div class="text-muted" style="font-size:12px;">
                        <i class="bi bi-upc me-1"></i>{{ strtoupper($vehicle->registration_number) }}
                        @if($vehicle->color)
                            &nbsp;•&nbsp;<i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>{{ $vehicle->color }}
                        @endif
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-circle" style="width:34px;height:34px;"
                            type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:12px;">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('customer.vehicles.edit', $vehicle) }}">
                                <i class="bi bi-pencil text-primary me-2"></i>Edit
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('customer.vehicles.destroy', $vehicle) }}" method="POST"
                                  onsubmit="return confirm('Delete this vehicle?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif
</div>
@endsection
