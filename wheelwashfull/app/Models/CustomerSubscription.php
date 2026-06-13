<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'vehicle_id',
        'customer_address_id',
        'service_city_id',
        'service_zone_id',
        'service_area',
        'start_date',
        'end_date',
        'total_washes',
        'used_washes',
        'remaining_washes',
        'exterior_remaining',
        'interior_remaining',
        'foam_remaining',
        'payment_status',
        'payment_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'auto_renew',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customerAddress()
    {
        return $this->belongsTo(Address::class, 'customer_address_id');
    }

    public function serviceCity()
    {
        return $this->belongsTo(ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }

    public function subscriptionBookings()
    {
        return $this->hasMany(SubscriptionBooking::class);
    }
}
