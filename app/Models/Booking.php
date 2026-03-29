<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'customer_id',
        'user_id',
        'created_by',
        'event_date',
        'start_time',
        'end_time',
        'booking_status',
        'event_name',
        'event_department',
        'event_type',
        'coordinator_name',
        'coordinator_phone',
        'coordinator_department',
        'coordinator_email',
        'coordinator_emergency_number',
        'media_requirements',
        'media_requirements_other',
        'resources',
        'resources_other',
        'cancellation_reason',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'media_requirements' => 'array',
        'resources' => 'array',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images()
    {
        return $this->hasMany(HallImage::class);
    }
}
