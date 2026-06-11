<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'partner_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the booking that was rated
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the customer who gave the rating
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the partner who received the rating
     */
    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * Scope to get ratings for a specific partner
     */
    public function scopeForPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    /**
     * Scope to get ratings by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent ratings
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
