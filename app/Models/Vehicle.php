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

    const STATUS_AVAILABLE      = 'available';
    const STATUS_IN_USE         = 'in_use';
    const STATUS_MAINTENANCE    = 'maintenance';
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

    // ─── Helpers (existentes) ─────────────────────────────────────────────────

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

    // ─── Helpers (nuevos — Tarjeta 16) ───────────────────────────────────────

    /**
     * Marca el vehículo como en mantenimiento.
     *
     * Llamado al crear un mantenimiento open.
     * No verifica el estado actual porque la validación ya ocurrió
     * en el controlador antes de llamar a este método.
     */
    public function markAsUnderMaintenance(): void
    {
        $this->update(['status' => self::STATUS_MAINTENANCE]);
    }

    /**
     * Devuelve el vehículo a 'available' solo si:
     *   1. Su estado actual es 'maintenance' (no sobrescribe otros estados especiales).
     *   2. No quedan mantenimientos abiertos asociados a este vehículo.
     *
     * Llamado al cerrar un mantenimiento.
     */
    public function releaseToAvailableIfNoOpenMaintenances(): void
    {
        if (
            $this->status === self::STATUS_MAINTENANCE &&
            $this->openMaintenances()->doesntExist()
        ) {
            $this->update(['status' => self::STATUS_AVAILABLE]);
        }
    }
}