<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SupervisorViticulturist extends Model
{
    protected $table = 'supervisor_viticulturist';

    protected $fillable = [
        'supervisor_id',
        'viticulturist_id',
        'assigned_by',
        'notebook_access',
        'notebook_granted_at',
        'notebook_revoked_at',
    ];

    protected $casts = [
        'notebook_access'     => 'boolean',
        'notebook_granted_at' => 'datetime',
        'notebook_revoked_at' => 'datetime',
    ];

    /**
     * Supervisor que tiene este viticultor
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Viticultor asignado al supervisor
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Usuario que asignó esta relación
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ── Cuaderno access ──────────────────────────────────────────────────────

    public function grantNotebookAccess(): void
    {
        $this->update([
            'notebook_access'     => true,
            'notebook_granted_at' => now(),
            'notebook_revoked_at' => null,
        ]);
    }

    public function revokeNotebookAccess(): void
    {
        $this->update([
            'notebook_access'     => false,
            'notebook_revoked_at' => now(),
        ]);
    }

    public function hasNotebookAccess(): bool
    {
        return (bool) $this->notebook_access;
    }

    protected static function booted(): void
    {
        $flush = function (self $sv): void {
            Cache::forget("user_{$sv->viticulturist_id}_has_supervisor");
            Cache::forget("user_{$sv->viticulturist_id}_supervisor");
        };

        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }
}
