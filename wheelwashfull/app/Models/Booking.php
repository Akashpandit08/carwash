<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'customer_subscription_id',
        'booking_source',
        'subscription_wash_type',
        'vehicle_id',
        'service_id',
        'service_city_id',
        'service_zone_id',
        'service_mode',
        'wash_type',
        'booking_date',
        'slot_time',
        'address',
        'latitude',
        'longitude',
        'price',
        'discount',
        'final_price',
        'total_amount',
        'payable_amount',
        'coupon_id',
        'payment_method',
        'payment_status',
        'status',
        'partner_id',
        'worker_id',
        'pickup_driver_id',
        'delivery_driver_id',
        'notes',
        'cancellation_reason',
        'pickup_address_id',
        'drop_address_id',
        'pickup_fee',
        'drop_fee',
        'pickup_scheduled_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function customerSubscription()
    {
        return $this->belongsTo(CustomerSubscription::class);
    }

    public function serviceCity()
    {
        return $this->belongsTo(ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function pickupDriver()
    {
        return $this->belongsTo(User::class, 'pickup_driver_id');
    }

    public function deliveryDriver()
    {
        return $this->belongsTo(User::class, 'delivery_driver_id');
    }

    public function pickupAddress()
    {
        return $this->belongsTo(Address::class, 'pickup_address_id');
    }

    public function dropAddress()
    {
        return $this->belongsTo(Address::class, 'drop_address_id');
    }

    public function images()
    {
        return $this->hasMany(BookingImage::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function assignments()
    {
        return $this->hasMany(BookingAssignment::class)->orderBy('assigned_at', 'desc');
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class)->orderBy('created_at');
    }

    public function statusLogs()
    {
        return $this->hasMany(BookingStatusLog::class)->orderBy('created_at');
    }

    public function media()
    {
        return $this->hasMany(BookingMedia::class);
    }

    public function liveLocations()
    {
        return $this->hasMany(LiveLocation::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
