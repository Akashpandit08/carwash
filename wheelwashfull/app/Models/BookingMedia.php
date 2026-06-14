<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingMedia extends Model
{
    use HasFactory;

    protected $table = 'booking_media';

    protected $fillable = [
        'booking_id',
        'uploaded_by_user_id',
        'type',
        'side',
        'file_path',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
