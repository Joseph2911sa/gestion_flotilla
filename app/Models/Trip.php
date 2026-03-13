<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_request_id',
        'vehicle_id',
        'driver_id',
        'route_id',
        'start_time',
        'end_time',
        'start_mileage',
        'end_mileage',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'start_time'    => 'datetime',
            'end_time'      => 'datetime',
            'start_mileage' => 'integer',
            'end_mileage'   => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The approved request that originated this trip.
     */
    public function tripRequest(): BelongsTo
    {
        return $this->belongsTo(TripRequest::class);
    }

    /**
     * The vehicle used for this trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * The driver assigned to this trip.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * The route driven on this trip.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Kilometres driven during this trip.
     * Returns null if the trip has not been completed yet.
     */
    public function distanceDriven(): ?int
    {
        if (is_null($this->start_mileage) || is_null($this->end_mileage)) {
            return null;
        }

        return $this->end_mileage - $this->start_mileage;
    }

    public function isCompleted(): bool
    {
        return ! is_null($this->end_time) && ! is_null($this->end_mileage);
    }
}