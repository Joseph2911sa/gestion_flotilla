<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'origin',
        'destination',
        'distance_km',
        'estimated_minutes',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'distance_km'        => 'decimal:2',
            'estimated_minutes'  => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * Trip requests that reference this route.
     */
    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class);
    }

    /**
     * Trips that were driven along this route.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}