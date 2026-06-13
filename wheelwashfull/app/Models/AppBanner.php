<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'service_city_id',
        'service_zone_id',
        'subtitle',
        'image',
        'position',
        'type',
        'redirect_screen',
        'redirect_value',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function serviceCity()
    {
        return $this->belongsTo(ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }

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
}
