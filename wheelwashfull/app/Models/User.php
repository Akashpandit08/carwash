<?php

namespace App\Models;

use App\Constants\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'password',
        'role',
        'status',
        'otp',
        'otp_expires_at',
        'api_token_hash',
        'service_city_id',
        'service_zone_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'api_token_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isAdmin(): bool
    {
        return UserRole::isAdminRole($this->role);
    }

    public function isSuperAdmin(): bool
    {
        return UserRole::isSuperAdminRole($this->role);
    }

    public function isCityAdmin(): bool
    {
        return $this->role === UserRole::CITY_ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function isPartner(): bool
    {
        return $this->role === UserRole::PARTNER;
    }

    public function isWorker(): bool
    {
        return $this->role === UserRole::WORKER;
    }

    public function isPickupDriver(): bool
    {
        return $this->role === UserRole::PICKUP_DRIVER;
    }

    /**
     * Get all bookings for the user.
     */
    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class);
    }

    /**
     * Get all bookings assigned to this partner.
     */
    public function assignedBookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'partner_id');
    }

    public function workerBookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'worker_id');
    }

    public function pickupDriverBookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'pickup_driver_id');
    }

    public function partnerProfile()
    {
        return $this->hasOne(\App\Models\PartnerProfile::class);
    }

    public function workerProfile()
    {
        return $this->hasOne(\App\Models\WorkerProfile::class);
    }

    public function pickupDriverProfile()
    {
        return $this->hasOne(\App\Models\PickupDriverProfile::class);
    }

    /**
     * Get all vehicles for the user.
     */
    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class);
    }

    /**
     * Get all addresses for the user.
     */
    public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class);
    }

    public function devices()
    {
        return $this->hasMany(\App\Models\UserDevice::class);
    }

    public function serviceCity()
    {
        return $this->belongsTo(\App\Models\ServiceCity::class);
    }

    public function serviceZone()
    {
        return $this->belongsTo(\App\Models\ServiceZone::class);
    }

    /**
     * Get all ratings received (as a partner).
     */
    public function receivedRatings()
    {
        return $this->hasMany(\App\Models\Rating::class, 'partner_id');
    }

    /**
     * Get this partner's average rating.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->receivedRatings()->avg('rating') ?? 0, 1);
    }
}
