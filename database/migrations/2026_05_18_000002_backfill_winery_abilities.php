<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill: grant ALL abilities to winery/producer users that have ZERO abilities.
 *
 * Context: CheckWineryAbility used to allow full access when a winery had no
 * abilities configured (retrocompatible default). Now the default changes to DENY,
 * so existing wineries that relied on that open-door need explicit grants.
 *
 * Only affects users with 0 entries in user_abilities — wineries that were already
 * partially configured by a supervisor are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $allAbilityIds = DB::table('abilities')->pluck('id');

        if ($allAbilityIds->isEmpty()) {
            return;
        }

        $wineryIds = DB::table('users')
            ->whereIn('role', ['winery', 'producer'])
            ->pluck('id');

        foreach ($wineryIds as $userId) {
            $hasAny = DB::table('user_abilities')->where('user_id', $userId)->exists();

            if ($hasAny) {
                continue; // Already configured by supervisor — leave as-is
            }

            $rows = $allAbilityIds->map(fn ($abilityId) => [
                'user_id' => $userId,
                'ability_id' => $abilityId,
                'granted_by' => $userId,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            DB::table('user_abilities')->insert($rows);
        }
    }

    public function down(): void
    {
        // Non-destructive: we cannot know which rows were seeded vs. manually granted.
        // Intentionally left empty — rollback requires manual intervention.
    }
};
