<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_subscription_id',
        'booking_id',
        'wash_type',
        'used_at',
        'status',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function customerSubscription()
    {
        return $this->belongsTo(CustomerSubscription::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
