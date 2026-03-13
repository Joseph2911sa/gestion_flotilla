<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'type',
        'status',
        'description',
        'start_date',
        'end_date',
        'cost',
        'mileage_at_service',
    ];

    protected function casts(): array
    {
        return [
            'start_date'         => 'date',
            'end_date'           => 'date',
            'cost'               => 'decimal:2',
            'mileage_at_service' => 'integer',
        ];
    }

    // ─── Type constants ───────────────────────────────────────────────────────

    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_INSPECTION = 'inspection';

    const TYPES = [
        self::TYPE_PREVENTIVE,
        self::TYPE_CORRECTIVE,
        self::TYPE_INSPECTION,
    ];

    // ─── Status constants ─────────────────────────────────────────────────────

    const STATUS_OPEN   = 'open';
    const STATUS_CLOSED = 'closed';

    const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The vehicle this maintenance record belongs to.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}