<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'service_city_id',
        'service_zone_id',
        'service_area',
        'is_global',
        'name',
        'description',
        'price',
        'duration_minutes',
        'vehicle_types',
        'wash_type',
        'image',
        'is_active',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'vehicle_types' => 'array',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function serviceCity()
    {
        return $this->belongsTo(ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }
}
