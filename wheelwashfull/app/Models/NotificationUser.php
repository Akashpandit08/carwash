<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'user_id',
        'role',
        'status',
        'is_read',
        'read_at',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function notification()
    {
        return $this->belongsTo(AppNotification::class, 'notification_id')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
