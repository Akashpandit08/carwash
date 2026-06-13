<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'partner_id', 'skills', 'service_area', 'service_radius', 'latitude', 'longitude', 'current_status', 'rating', 'total_jobs'];

    protected $casts = ['skills' => 'array', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'rating' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
