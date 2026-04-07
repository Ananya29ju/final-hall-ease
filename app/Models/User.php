<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\CustomResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 * 
 * Represents a user in the application. Users can have different roles
 * (e.g., admin, user, media) which dictate their capabilities within the system.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization,
     * ensuring sensitive data isn't exposed.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define the relationship: A User can have many Bookings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Helper Method: Check if the user has an 'admin' role.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return strtolower((string) $this->role) === 'admin';
    }

    /**
     * Helper Method: Check if the user has a 'user' (staff) role.
     *
     * @return bool
     */
    public function isStaff()
    {
        return strtolower((string) $this->role) === 'user';
    }

    /**
     * Helper Method: Check if the user has a 'media' role.
     *
     * @return bool
     */
    public function isMedia()
    {
        return strtolower((string) $this->role) === 'media';
    }

    /**
     * Helper Method: Check if the user's account is 'approved'.
     *
     * @return bool
     */
    public function isApproved()
    {
        return strtolower((string) $this->status) === 'approved';
    }

    /**
     * Define the relationship: Bookings created by this specific user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdBookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    /**
     * Send the custom password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
