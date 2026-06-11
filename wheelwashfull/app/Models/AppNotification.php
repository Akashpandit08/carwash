<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'message',
        'body',
        'image',
        'target_type',
        'target_role',
        'redirect_type',
        'redirect_value',
        'send_type',
        'scheduled_at',
        'sent_at',
        'status',
        'created_by',
        'channel',
        'type',
        'user_id',
        'booking_id',
        'sound_id',
        'data',
        'screen',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'data' => 'array',
    ];

    protected $appends = ['image_url'];

    protected static function booted(): void
    {
        static::addGlobalScope('pushCampaigns', function (Builder $builder) {
            $builder->where('channel', 'push');
        });

        static::creating(function (AppNotification $notification) {
            $notification->channel ??= 'push';
            $notification->type ??= 'app_campaign';
            $notification->status ??= 'draft';
        });
    }

    public function recipients()
    {
        return $this->hasMany(NotificationUser::class, 'notification_id');
    }

    public function sound()
    {
        return $this->belongsTo(NotificationSound::class, 'sound_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
