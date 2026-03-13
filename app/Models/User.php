<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The role assigned to this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Trip requests submitted by this user (as a driver).
     */
    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class, 'user_id');
    }

    /**
     * Trip requests reviewed (approved/rejected) by this user (as operator/admin).
     */
    public function reviewedRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class, 'reviewed_by');
    }

    /**
     * Trips driven by this user.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->role?->name === Role::OPERATOR;
    }

    public function isDriver(): bool
    {
        return $this->role?->name === Role::DRIVER;
    }
}