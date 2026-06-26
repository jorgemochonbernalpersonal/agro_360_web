<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignService
{
    /**
     * @return array{campaign: Campaign, activated: bool}
     */
    public function create(User $user, array $data): array
    {
        $existing = Campaign::forViticulturist($user->id)
            ->forYear($data['year'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'year' => __('Ya existe una campaña para el año :year.', ['year' => $data['year']]),
            ]);
        }

        $campaign = DB::transaction(function () use ($user, $data) {
            $wineryViticulturistId = null;
            if ($user->isViticulturist()) {
                $relations = WineryViticulturist::where('viticulturist_id', $user->id)
                    ->whereNotNull('winery_id')
                    ->get();
                if ($relations->count() === 1) {
                    $wineryViticulturistId = $relations->first()->id;
                }
            }

            $campaign = Campaign::create([
                'name' => $data['name'],
                'year' => $data['year'],
                'viticulturist_id' => $user->id,
                'winery_viticulturist_id' => $wineryViticulturistId,
                'start_date' => $data['start_date'] ?: null,
                'end_date' => $data['end_date'] ?: null,
                'description' => $data['description'],
                'active' => false,
            ]);

            if ($data['active']) {
                $campaign->activate();
            }

            return $campaign;
        });

        return ['campaign' => $campaign, 'activated' => (bool) $data['active']];
    }

    /**
     * Returns true if the campaign was just activated by this update.
     */
    public function update(Campaign $campaign, User $user, array $data): bool
    {
        $existing = Campaign::forViticulturist($user->id)
            ->forYear($data['year'])
            ->where('id', '!=', $campaign->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'year' => __('Ya existe otra campaña para el año :year.', ['year' => $data['year']]),
            ]);
        }

        $wasActive = (bool) $campaign->active;

        DB::transaction(function () use ($campaign, $data, $wasActive) {
            $campaign->update([
                'name' => $data['name'],
                'year' => $data['year'],
                'start_date' => $data['start_date'] ?: null,
                'end_date' => $data['end_date'] ?: null,
                'description' => $data['description'],
            ]);

            if ($data['active'] && ! $wasActive) {
                $campaign->activate();
            } elseif (! $data['active'] && $wasActive) {
                $campaign->update(['active' => false]);
            }
        });

        return $data['active'] && ! $wasActive;
    }
}
