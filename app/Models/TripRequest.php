<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'route_id',
        'reviewed_by',
        'departure_date',
        'return_date',
        'status',
        'reason',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'datetime',
            'return_date' => 'datetime',
        ];
    }

    // ─── Status constants ─────────────────────────────────────────────────────

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The driver who submitted this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The vehicle requested (optional).
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * The route requested (optional).
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * The operator or admin who reviewed this request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Trip generated from this approved request.
     */
    public function trip(): HasOne
    {
        return $this->hasOne(Trip::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED
        ]);
    }
}