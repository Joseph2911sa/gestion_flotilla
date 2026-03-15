<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    // ─── Constants for role names ────────────────────────────────────────────

    const ADMIN    = 'Admin';
    const OPERATOR = 'Operador';
    const DRIVER   = 'Chofer';

    // ─── Relationships ───────────────────────────────────────────────────────

    /**
     * A role can be assigned to many users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}