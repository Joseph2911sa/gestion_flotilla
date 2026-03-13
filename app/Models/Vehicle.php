<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plate',
        'brand',
        'model',
        'year',
        'vehicle_type',
        'capacity',
        'fuel_type',
        'image_path',
        'status',
        'mileage',
    ];

    protected function casts(): array
    {
        return [
            'year'     => 'integer',
            'capacity' => 'integer',
            'mileage'  => 'integer',
        ];
    }

    // ─── Status constants ─────────────────────────────────────────────────────

    const STATUS_AVAILABLE     = 'available';
    const STATUS_IN_USE        = 'in_use';
    const STATUS_MAINTENANCE   = 'maintenance';
    const STATUS_OUT_OF_SERVICE = 'out_of_service';

    const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_IN_USE,
        self::STATUS_MAINTENANCE,
        self::STATUS_OUT_OF_SERVICE,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * All trip requests associated with this vehicle.
     */
    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class);
    }

    /**
     * All trips (deliveries/returns) involving this vehicle.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * All maintenance records for this vehicle.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    /**
     * Returns open (active) maintenances for this vehicle.
     */
    public function openMaintenances(): HasMany
    {
        return $this->maintenances()->where('status', Maintenance::STATUS_OPEN);
    }
}