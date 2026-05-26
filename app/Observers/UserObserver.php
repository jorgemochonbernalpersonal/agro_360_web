<?php

namespace App\Observers;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Auto-create an Organization when a winery or DO (supervisor) user is created.
     * Auto-create the current-year Campaign when a viticulturist registers directly.
     */
    public function created(User $user): void
    {
        // Campaign for viticulturists that register directly (can_login = true)
        if ($user->role === User::ROLE_VITICULTURIST && $user->can_login) {
            Campaign::getOrCreateActiveForYear($user->id);
        }

        $type = $this->resolveOrgType($user);

        if ($type === null || $user->organization_id) {
            return;
        }

        $org = Organization::create([
            'name'          => $user->name,
            'type'          => $type,
            'slug'          => Str::slug($user->name) . '-' . $user->id,
            'email'         => !str_contains($user->email, '@noemail.agro365.es') ? $user->email : null,
            'active'        => true,
            'owner_user_id' => $user->id,
        ]);

        $user->updateQuietly(['organization_id' => $org->id]);
    }

    /**
     * Sync organization name when the user's name changes.
     * Record activation timestamp when a ghost viticulturist enables their account.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('name') && $user->organization_id) {
            $user->organization?->update(['name' => $user->name]);
        }

        if ($user->role === User::ROLE_VITICULTURIST
            && $user->wasChanged('can_login')
            && $user->can_login === true
            && $user->getOriginal('can_login') === false
            && $user->activated_at === null
        ) {
            $user->updateQuietly(['activated_at' => now()]);
            Campaign::getOrCreateActiveForYear($user->id);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveOrgType(User $user): ?string
    {
        return match ($user->role) {
            User::ROLE_WINERY,
            User::ROLE_PRODUCER   => Organization::TYPE_WINERY,
            User::ROLE_SUPERVISOR => Organization::TYPE_DENOMINATION,
            default               => null,
        };
    }
}
