<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'partner_id',
        'worker_id',
        'pickup_driver_id',
        'delivery_driver_id',
        'assigned_by_admin_id',
        'assigned_by',
        'assigned_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
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

    public function admin()
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
