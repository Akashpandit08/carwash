<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'service_city_id',
        'service_zone_id',
        'subtitle',
        'image',
        'redirect_type',
        'redirect_value',
        'user_type',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function scopeVisibleForRole(Builder $query, string $role): Builder
    {
        $role = $role === 'pickup_driver' ? 'driver' : $role;

        return $query
            ->where('is_active', true)
            ->whereIn('user_type', ['all', $role])
            ->where(function (Builder $query) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset(Storage::url($this->image));
    }
}
