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
        'name',
        'description',
        'price',
        'duration_minutes',
        'vehicle_types',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'vehicle_types' => 'array',
        'is_active' => 'boolean',
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
}
