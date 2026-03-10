<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oenologist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'surname',
        'license_number',
        'email',
        'phone',
        'active',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->surname}");
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
