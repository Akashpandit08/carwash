<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_city_id',
        'service_zone_id',
        'service_area',
        'is_global',
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'total_washes',
        'exterior_washes',
        'interior_washes',
        'foam_washes',
        'tyre_polish_included',
        'dashboard_wipe_included',
        'vacuum_included',
        'priority_booking',
        'pickup_drop_included',
        'doorstep_included',
        'max_washes_per_week',
        'terms',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'price' => 'decimal:2',
        'tyre_polish_included' => 'boolean',
        'dashboard_wipe_included' => 'boolean',
        'vacuum_included' => 'boolean',
        'priority_booking' => 'boolean',
        'pickup_drop_included' => 'boolean',
        'doorstep_included' => 'boolean',
    ];

    public function serviceCity()
    {
        return $this->belongsTo(ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }

    public function customerSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
    }
}
