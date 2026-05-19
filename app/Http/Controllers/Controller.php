<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve per_page from request. Supports "all" to load everything.
     * When "all" is passed, returns $max (default 1000) so paginate() returns one page.
     */
    protected function resolvePerPage(Request $request, int $default = 30, int $max = 500): int
    {
        $raw = $request->query('per_page', (string) $default);

        if ($raw === 'all') {
            return $max;
        }

        return min(max((int) $raw, 1), $max);
    }

    /**
     * Normalize campaign_id = 0 to null before validation.
     * Mobile clients send 0 as "not set" before the active campaign loads.
     * The validation rule nullable|integer|exists:campaigns,id rejects 0
     * because no campaign has id=0. Converting to null lets the fallback
     * logic assign the active campaign instead.
     */
    protected function normalizeCampaignId(Request $request): void
    {
        if ((int) $request->input('campaign_id', 0) === 0) {
            $request->merge(['campaign_id' => null]);
        }
    }
}
