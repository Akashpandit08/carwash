<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSound extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function notifications()
    {
        return $this->hasMany(AppNotification::class, 'sound_id');
    }
}
