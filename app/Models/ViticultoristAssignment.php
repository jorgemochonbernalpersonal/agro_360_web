<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViticultoristAssignment extends Model
{
    protected $table = 'viticultor_assignments';

    protected $fillable = [
        'viticultor_id',
        'organization_id',
        'assigned_by_org_id',
        'assigned_by_user_id',
        'cuaderno_access',
        'cuaderno_granted_at',
        'cuaderno_revoked_at',
    ];

    protected $casts = [
        'cuaderno_access'     => 'boolean',
        'cuaderno_granted_at' => 'datetime',
        'cuaderno_revoked_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function viticultor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticultor_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedByOrg(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'assigned_by_org_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
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

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function hasNotebookAccess(): bool
    {
        return $this->cuaderno_access;
    }

    /**
     * Whether assignment was made by a DO (denomination of origin) organisation.
     */
    public function isAssignedByDO(): bool
    {
        return $this->assignedByOrg?->isDenomination() ?? false;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeWithNotebookAccess($query)
    {
        return $query->where('cuaderno_access', true);
    }

    public function scopeAssignedByDO($query)
    {
        return $query->whereHas('assignedByOrg', fn($q) => $q->where('type', Organization::TYPE_DENOMINATION));
    }
}
