<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViticulturistAssignment extends Model
{
    protected $table = 'viticulturist_assignments';

    protected $fillable = [
        'viticulturist_id',
        'organization_id',
        'assigned_by_org_id',
        'assigned_by_user_id',
        'notebook_access',
        'notebook_granted_at',
        'notebook_revoked_at',
    ];

    protected $casts = [
        'notebook_access' => 'boolean',
        'notebook_granted_at' => 'datetime',
        'notebook_revoked_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
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

    // ── Notebook access ──────────────────────────────────────────────────────

    public function grantNotebookAccess(): void
    {
        $this->update([
            'notebook_access' => true,
            'notebook_granted_at' => now(),
            'notebook_revoked_at' => null,
        ]);
    }

    public function revokeNotebookAccess(): void
    {
        $this->update([
            'notebook_access' => false,
            'notebook_revoked_at' => now(),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function hasNotebookAccess(): bool
    {
        return $this->notebook_access;
    }

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
        return $query->where('notebook_access', true);
    }

    public function scopeAssignedByDO($query)
    {
        return $query->whereHas('assignedByOrg', fn ($q) => $q->where('type', Organization::TYPE_DENOMINATION));
    }
}
