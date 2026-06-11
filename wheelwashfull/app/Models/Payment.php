<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_reference',
        'gateway_order_id',
        'gateway_payment_id',
        'method',
        'status',
        'amount',
        'currency',
        'gateway_response',
        'paid_at',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
