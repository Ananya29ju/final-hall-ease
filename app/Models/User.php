<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\CustomResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 🔗 One User → Many Bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ⭐ helpers
    public function isAdmin()
    {
        return strtolower((string) $this->role) === 'admin';
    }

    public function isStaff()
    {
        return strtolower((string) $this->role) === 'user';
    }

    public function isMedia()
    {
        return strtolower((string) $this->role) === 'media';
    }

    public function isApproved()
    {
        return strtolower((string) $this->status) === 'approved';
    }

    public function createdBookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }
    public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPassword($token));
}
}
