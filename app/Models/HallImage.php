<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * HallImage Model
 * 
 * Represents an image associated with a specific hall.
 */
class HallImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hall_id',
        'image_path',
    ];

    /**
     * Define the relationship: This Image belongs to a specific Hall.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}