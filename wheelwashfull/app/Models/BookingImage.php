<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'image_type',
        'image_path',
        'uploaded_by',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
