<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_city_id',
        'name',
        'slug',
        'status',
        'sort_order',
    ];

    public function city()
    {
        return $this->belongsTo(ServiceCity::class, 'service_city_id');
    }
}
