@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Edit Coupon</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">← Back to Coupons</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="code" class="form-label">Coupon Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" required value="{{ old('code', $coupon->code) }}" placeholder="e.g., SUMMER2024">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $coupon->description) }}">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="discount_type" class="form-label">Discount Type</label>
                                <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type" required onchange="updateDiscountLabel()">
                                    <option value="percentage" {{ old('discount_type', $coupon->discount_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="discount_value" class="form-label" id="discountLabel">Discount Value</label>
                                <input type="number" class="form-control @error('discount_value') is-invalid @enderror" id="discount_value" name="discount_value" step="0.01" required value="{{ old('discount_value', $coupon->discount_value) }}">
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="min_order_amount" class="form-label">Minimum Order Amount (₹)</label>
                            <input type="number" class="form-control @error('min_order_amount') is-invalid @enderror" id="min_order_amount" name="min_order_amount" step="0.01" value="{{ old('min_order_amount', $coupon->min_order_amount) }}">
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="max_discount" class="form-label">Maximum Discount (₹)</label>
                            <input type="number" class="form-control @error('max_discount') is-invalid @enderror" id="max_discount" name="max_discount" step="0.01" value="{{ old('max_discount', $coupon->max_discount) }}">
                            @error('max_discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="usage_limit" class="form-label">Usage Limit (leave empty for unlimited)</label>
                            <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}">
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="valid_from" class="form-label">Valid From</label>
                                <input type="datetime-local" class="form-control @error('valid_from') is-invalid @enderror" id="valid_from" name="valid_from" value="{{ old('valid_from', $coupon->valid_from?->format('Y-m-d\TH:i')) }}">
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="valid_until" class="form-label">Valid Until</label>
                                <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until" name="valid_until" required value="{{ old('valid_until', $coupon->valid_until->format('Y-m-d\TH:i')) }}">
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Coupon</button>
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateDiscountLabel() {
    const type = document.getElementById('discount_type').value;
    const label = document.getElementById('discountLabel');
    label.textContent = type === 'percentage' ? 'Discount Value (%)' : 'Discount Value (₹)';
}
document.addEventListener('DOMContentLoaded', updateDiscountLabel);
</script>
@endsection
