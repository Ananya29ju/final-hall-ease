<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'user_id',
        'start_datetime',
        'end_datetime',
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
        'status',
        'notified_at',
        'expires_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'media_requirements' => 'array',
        'resources' => 'array',
        'notified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->status === 'notified' && $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Format the waitlist datetime range for display.
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
     * Notify the next eligible user in the waitlist for a specific slot.
     * Updated to use datetime range overlap detection.
     */
    public static function notifyNextInWaitlist($hallId, $startDatetime, $endDatetime = null)
    {
        // Get all pending waitlist entries for this hall
        $query = self::where('hall_id', $hallId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc');

        $allPending = $query->get();

        $eligible = null;

        foreach ($allPending as $w) {
            // Check if THIS waitlist slot is completely free
            $availability = Booking::isSlotAvailable(
                $hallId,
                $w->start_datetime,
                $w->end_datetime
            );

            if ($availability['available']) {
                $eligible = $w;
                break;
            }
        }

        if ($eligible) {
            $eligible->update([
                'status' => 'notified',
                'notified_at' => now(),
                'expires_at' => now()->addHours(2),
            ]);

            $user = $eligible->user;
            if ($user) {
                $user->notify(new \App\Notifications\WaitlistSlotAvailable($eligible));
            }
        }
    }
}
