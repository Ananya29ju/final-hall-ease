<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Hall Model
 * 
 * Represents a physical hall or venue that can be booked within the system.
 */
class Hall extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'campus_name',
        'location',
        'capacity',
        'description',
        'image',
        'status',
    ];

    /**
     * Define the relationship: One Hall can have many Bookings.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Define the relationship: One Hall can have many Images.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(HallImage::class);
    }
}
