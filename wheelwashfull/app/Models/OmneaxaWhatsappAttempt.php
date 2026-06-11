<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmneaxaWhatsappAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'booking_id',
        'phone',
        'template_name',
        'event_type',
        'module',
        'endpoint',
        'payload',
        'response',
        'status',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];
}
