<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupDriverProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'partner_id', 'vehicle_type', 'license_number', 'service_area', 'latitude', 'longitude', 'current_status', 'rating', 'total_jobs'];

    protected $casts = ['latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'rating' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
