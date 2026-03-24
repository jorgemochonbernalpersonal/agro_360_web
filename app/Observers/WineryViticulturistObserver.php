<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ViticultoristAssignment;
use App\Models\WineryViticulturist;

class WineryViticulturistObserver
{
    /**
     * Dual-write to viticultor_assignments when a winery-viticulturist link is created.
     */
    public function created(WineryViticulturist $rel): void
    {
        $orgId = $this->resolveOrganizationId($rel);

        if ($orgId === null) {
            return;
        }

        ViticultoristAssignment::updateOrCreate(
            [
                'viticultor_id'   => $rel->viticulturist_id,
                'organization_id' => $orgId,
            ],
            [
                'assigned_by_org_id'  => $orgId,
                'assigned_by_user_id' => $rel->assigned_by,
            ]
        );
    }

    /**
     * Sync changes to viticultor_assignments:
     * - winery_id null→value: self-registered viticulturist linked to a winery for the first time
     * - cuaderno_access/granted_at/revoked_at: notebook access changes
     */
    public function updated(WineryViticulturist $rel): void
    {
        // Self-registered viticulturist linked to a winery for the first time
        if ($rel->wasChanged('winery_id') && $rel->getOriginal('winery_id') === null) {
            $winery = User::find($rel->winery_id);

            if ($winery?->organization_id) {
                ViticultoristAssignment::updateOrCreate(
                    [
                        'viticultor_id'   => $rel->viticulturist_id,
                        'organization_id' => $winery->organization_id,
                    ],
                    [
                        'assigned_by_org_id'  => $winery->organization_id,
                        'assigned_by_user_id' => $rel->assigned_by,
                    ]
                );
            }

            return;
        }

        if (!$rel->wasChanged(['cuaderno_access', 'cuaderno_granted_at', 'cuaderno_revoked_at'])) {
            return;
        }

        $orgId = $this->resolveOrganizationId($rel);

        if ($orgId === null) {
            return;
        }

        $assignment = ViticultoristAssignment::where('viticultor_id', $rel->viticulturist_id)
            ->where('organization_id', $orgId)
            ->first();

        if (!$assignment) {
            return;
        }

        $assignment->update([
            'cuaderno_access'     => $rel->cuaderno_access,
            'cuaderno_granted_at' => $rel->cuaderno_granted_at,
            'cuaderno_revoked_at' => $rel->cuaderno_revoked_at,
        ]);
    }

    /**
     * Remove the assignment when the winery-viticulturist link is deleted.
     */
    public function deleted(WineryViticulturist $rel): void
    {
        $orgId = $this->resolveOrganizationId($rel);

        if ($orgId === null) {
            return;
        }

        ViticultoristAssignment::where('viticultor_id', $rel->viticulturist_id)
            ->where('organization_id', $orgId)
            ->delete();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resolve the organization ID responsible for this link.
     *
     * - winery_id set   → use the winery/producer user's organization_id
     * - SOURCE_VITICULTURIST, winery_id null → use assigned_by user's organization_id
     *   (handles producers creating sub-viticulturists: producers have org but wineries
     *    attribute returns empty because it filters by role='winery')
     * - SOURCE_SELF, winery_id null → returns null (independent self-registration)
     */
    private function resolveOrganizationId(WineryViticulturist $rel): ?int
    {
        if ($rel->winery_id !== null) {
            return User::find($rel->winery_id)?->organization_id;
        }

        if ($rel->source === WineryViticulturist::SOURCE_VITICULTURIST && $rel->assigned_by !== null) {
            return User::find($rel->assigned_by)?->organization_id;
        }

        return null;
    }
}
