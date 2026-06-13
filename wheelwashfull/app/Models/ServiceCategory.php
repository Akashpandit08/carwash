<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'is_active',
        'service_city_id',
        'service_zone_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function serviceCity()
    {
        return $this->belongsTo(ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
