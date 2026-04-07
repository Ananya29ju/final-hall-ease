<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Booking Model
 * 
 * Represents a hall booking request within the application.
 * Manages relationships with the hall, customer, user, and images,
 * and handles custom logic for availability and status checks.
 */
class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hall_id',
        'customer_id',
        'user_id',
        'created_by',
        'start_datetime',
        'end_datetime',
        'booking_status',
        'admin_status',
        'media_status',
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
        'media_feedback_reason',
        'unavailable_media_requirements',
        'accepted_media_requirements',
        'media_remarks',
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'media_requirements' => 'array',
        'resources' => 'array',
        'unavailable_media_requirements' => 'array',
        'accepted_media_requirements' => 'array',
    ];


    /**
     * Define the relationship: A Booking belongs to a specific Hall.
     */
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Define the relationship: A Booking belongs to a Customer (User).
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Define the relationship: A Booking belongs to a regular User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Define the relationship: The User who created this Booking.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Define the relationship: A Booking can have multiple uploaded images.
     */
    public function images()
    {
        return $this->hasMany(HallImage::class);
    }

    /**
     * Check if this booking requires media resources.
     *
     * @return bool
     */
    public function requiresMedia()
    {
        return !empty($this->media_requirements) && count($this->media_requirements) > 0;
    }

    /**
     * Check if this booking has been fully approved by both admin and media (if applicable).
     *
     * @return bool
     */
    public function isFullyApproved()
    {
        if ($this->admin_status !== 'approved') {
            return false;
        }

        if ($this->requiresMedia()) {
            return $this->media_status === 'accepted';
        }

        return true;
    }

    /**
     * Format the booking datetime range for display.
     * e.g. "04 Nov 10:00 PM → 05 Nov 01:00 AM"
     */
    public function getFormattedDatetimeRangeAttribute(): string
    {
        $start = $this->start_datetime;
        $end = $this->end_datetime;

        if (!$start || !$end) {
            return 'N/A';
        }

        if ($start->isSameDay($end)) {
            return $start->format('d M, Y') . ' | ' . $start->format('h:i A') . ' - ' . $end->format('h:i A');
        }

        return $start->format('d M h:i A') . ' → ' . $end->format('d M h:i A');
    }

    /**
     * Check if a specific datetime range is available for a hall, including the 30-minute buffer rule.
     * Now supports multi-day bookings with proper datetime range overlap detection.
     *
     * @param int $hallId
     * @param string|Carbon $startDatetime
     * @param string|Carbon $endDatetime
     * @param int|null $excludeBookingId (optional) Booking ID to ignore in overlap checks
     * @return array ['available' => bool, 'message' => string]
     */
    public static function isSlotAvailable($hallId, $startDatetime, $endDatetime, $excludeBookingId = null): array
    {
        $newStart = Carbon::parse($startDatetime);
        $newEnd = Carbon::parse($endDatetime);

        $query = self::where('hall_id', $hallId)
            ->where('booking_status', '!=', 'cancelled');

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        // Only fetch potentially overlapping bookings (performance optimization)
        $existingBookings = $query
            ->where('start_datetime', '<', $newEnd)
            ->where('end_datetime', '>', $newStart)
            ->get();

        foreach ($existingBookings as $booking) {
            $existingStart = Carbon::parse($booking->start_datetime);
            $existingEnd = Carbon::parse($booking->end_datetime);

            // 1. Direct Overlap Check: (existing_start < new_end) AND (existing_end > new_start)
            if ($existingStart->lt($newEnd) && $existingEnd->gt($newStart)) {
                // Check if it's a buffer-only conflict (within 30-min gap) or a direct overlap
                $existingStartWithBuffer = (clone $existingStart)->subMinutes(30);
                $existingEndWithBuffer = (clone $existingEnd)->addMinutes(30);

                // If the ranges actually overlap (not just within buffer), report as direct overlap
                if ($existingStart->lt($newEnd) && $existingEnd->gt($newStart)) {
                    return ['available' => false, 'message' => 'This hall is already booked for the selected time range.'];
                }
            }
        }

        // 2. 30-Minute Gap Check (separate pass for buffer violations that aren't direct overlaps)
        $bufferBookings = self::where('hall_id', $hallId)
            ->where('booking_status', '!=', 'cancelled')
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->where('start_datetime', '<', (clone $newEnd)->addMinutes(30))
            ->where('end_datetime', '>', (clone $newStart)->subMinutes(30))
            ->get();

        foreach ($bufferBookings as $booking) {
            $existingStart = Carbon::parse($booking->start_datetime);
            $existingEnd = Carbon::parse($booking->end_datetime);

            // Check if this was already caught as a direct overlap
            if ($existingStart->lt($newEnd) && $existingEnd->gt($newStart)) {
                return ['available' => false, 'message' => 'This hall is already booked for the selected time range.'];
            }

            // Buffer-only conflict
            $existingStartWithBuffer = (clone $existingStart)->subMinutes(30);
            $existingEndWithBuffer = (clone $existingEnd)->addMinutes(30);

            if ($existingStartWithBuffer->lt($newEnd) && $existingEndWithBuffer->gt($newStart)) {
                return ['available' => false, 'message' => 'Minimum 30-minute gap required between bookings.'];
            }
        }

        return ['available' => true, 'message' => ''];
    }
}
