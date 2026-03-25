<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorViticulturist extends Model
{
    protected $table = 'supervisor_viticulturist';

    protected $fillable = [
        'supervisor_id',
        'viticulturist_id',
        'assigned_by',
        'cuaderno_access',
        'cuaderno_granted_at',
        'cuaderno_revoked_at',
    ];

    protected $casts = [
        'cuaderno_access'     => 'boolean',
        'cuaderno_granted_at' => 'datetime',
        'cuaderno_revoked_at' => 'datetime',
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
            'cuaderno_access'     => true,
            'cuaderno_granted_at' => now(),
            'cuaderno_revoked_at' => null,
        ]);
    }

    public function revokeNotebookAccess(): void
    {
        $this->update([
            'cuaderno_access'     => false,
            'cuaderno_revoked_at' => now(),
        ]);
    }

    public function hasNotebookAccess(): bool
    {
        return (bool) $this->cuaderno_access;
    }
}
