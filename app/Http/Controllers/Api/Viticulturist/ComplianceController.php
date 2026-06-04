<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplianceController extends Controller
{
    // ─── GET /viticulturist/compliance ────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|min:1',
        ]);

        $campaign = $request->filled('campaign_id')
            ? Campaign::forViticulturist($user->id)->findOrFail((int) $request->campaign_id)
            : Campaign::getOrCreateActiveForYear($user->id);

        $campaignId = $campaign?->id;
        $userId = $user->id;

        return response()->json([
            'data' => [
                'campaign_id' => $campaignId,
                'campaign_year' => $campaign?->year,
                'score' => $this->calculateScore($userId, $campaignId),
                'phytosanitary' => $this->phytosanitaryStats($userId, $campaignId),
                'observations' => $this->observationStats($userId, $campaignId),
                'post_harvest' => $this->postHarvestStats($userId, $campaignId),
                'cultural_works' => $this->culturalStats($userId, $campaignId),
                'locking' => $this->lockStats($userId, $campaignId),
                'yields' => $this->estimatedYieldStats($userId, $campaignId),
            ],
        ]);
    }

    // ─── Helpers — DB aggregation, sin cargar colecciones en memoria ──────────

    private function phytosanitaryStats(int $userId, ?int $campaignId): array
    {
        $row = AgriculturalActivity::forViticulturist($userId)
            ->ofType('phytosanitary')
            ->when($campaignId, fn ($q) => $q->forCampaign($campaignId))
            ->leftJoin('phytosanitary_treatments as pt', 'pt.activity_id', '=', 'agricultural_activities.id')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN pt.treatment_justification IS NOT NULL AND pt.treatment_justification != "" THEN 1 ELSE 0 END) as with_justification,
                SUM(CASE WHEN pt.applicator_ropo_number  IS NOT NULL AND pt.applicator_ropo_number  != "" THEN 1 ELSE 0 END) as with_ropo
            ')
            ->first();

        $total = (int) ($row->total ?? 0);
        $withJ = (int) ($row->with_justification ?? 0);
        $withR = (int) ($row->with_ropo ?? 0);

        return [
            'total' => $total,
            'with_justification' => $withJ,
            'with_ropo' => $withR,
            'completion_pct' => $total > 0
                ? (int) round((($withJ + $withR) / ($total * 2)) * 100)
                : 100,
        ];
    }

    private function observationStats(int $userId, ?int $campaignId): array
    {
        $row = AgriculturalActivity::forViticulturist($userId)
            ->ofType('observation')
            ->when($campaignId, fn ($q) => $q->forCampaign($campaignId))
            ->leftJoin('observations as ob', 'ob.activity_id', '=', 'agricultural_activities.id')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN ob.threshold_exceeded = 1 THEN 1 ELSE 0 END) as threshold_exceeded,
                SUM(CASE WHEN ob.follow_up_date IS NOT NULL AND ob.follow_up_date > NOW() THEN 1 ELSE 0 END) as pending_follow_up
            ')
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'threshold_exceeded' => (int) ($row->threshold_exceeded ?? 0),
            'pending_follow_up' => (int) ($row->pending_follow_up ?? 0),
        ];
    }

    private function postHarvestStats(int $userId, ?int $campaignId): array
    {
        $row = AgriculturalActivity::forViticulturist($userId)
            ->ofType('post_harvest')
            ->when($campaignId, fn ($q) => $q->forCampaign($campaignId))
            ->leftJoin('post_harvest_treatments as pht', 'pht.activity_id', '=', 'agricultural_activities.id')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN pht.reentry_interval_hours IS NOT NULL THEN 1 ELSE 0 END) as with_reentry
            ')
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'with_reentry' => (int) ($row->with_reentry ?? 0),
        ];
    }

    private function culturalStats(int $userId, ?int $campaignId): array
    {
        $row = AgriculturalActivity::forViticulturist($userId)
            ->whereIn('activity_type', ['cultural', 'pruning'])
            ->when($campaignId, fn ($q) => $q->forCampaign($campaignId))
            ->leftJoin('cultural_works as cw', 'cw.activity_id', '=', 'agricultural_activities.id')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN cw.residue_management IS NOT NULL AND cw.residue_management != "" THEN 1 ELSE 0 END) as with_residue_management
            ')
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'with_residue_management' => (int) ($row->with_residue_management ?? 0),
        ];
    }

    private function lockStats(int $userId, ?int $campaignId): array
    {
        $row = AgriculturalActivity::forViticulturist($userId)
            ->when($campaignId, fn ($q) => $q->forCampaign($campaignId))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked
            ')
            ->first();

        $total = (int) ($row->total ?? 0);
        $locked = (int) ($row->locked ?? 0);

        return [
            'total' => $total,
            'locked' => $locked,
            'unlocked' => $total - $locked,
        ];
    }

    private function estimatedYieldStats(int $userId, ?int $campaignId): array
    {
        $row = EstimatedYield::whereHas('plotPlanting', fn ($q) => $q->whereHas('plot', fn ($q2) => $q2->where('viticulturist_id', $userId))
        )
            ->where('active', true)
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed
            ')
            ->first();

        $total = (int) ($row->total ?? 0);
        $confirmed = (int) ($row->confirmed ?? 0);

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'draft' => $total - $confirmed,
        ];
    }

    private function calculateScore(int $userId, ?int $campaignId): int
    {
        // Una sola query agrega todos los conteos necesarios para el score
        $row = AgriculturalActivity::forViticulturist($userId)
            ->when($campaignId, fn ($q) => $q->forCampaign($campaignId))
            ->leftJoin('phytosanitary_treatments as pt', function ($join) {
                $join->on('pt.activity_id', '=', 'agricultural_activities.id')
                    ->where('agricultural_activities.activity_type', 'phytosanitary');
            })
            ->leftJoin('observations as ob', function ($join) {
                $join->on('ob.activity_id', '=', 'agricultural_activities.id')
                    ->where('agricultural_activities.activity_type', 'observation');
            })
            ->leftJoin('cultural_works as cw', function ($join) {
                $join->on('cw.activity_id', '=', 'agricultural_activities.id')
                    ->whereIn('agricultural_activities.activity_type', ['cultural', 'pruning']);
            })
            ->selectRaw('
                SUM(CASE WHEN agricultural_activities.activity_type = "phytosanitary" THEN 1 ELSE 0 END) as phyto_total,
                SUM(CASE WHEN agricultural_activities.activity_type = "phytosanitary"
                     AND pt.treatment_justification IS NOT NULL AND pt.treatment_justification != ""
                     THEN 1 ELSE 0 END) as phyto_justified,
                SUM(CASE WHEN agricultural_activities.activity_type = "phytosanitary"
                     AND pt.applicator_ropo_number IS NOT NULL AND pt.applicator_ropo_number != ""
                     THEN 1 ELSE 0 END) as phyto_ropo,
                SUM(CASE WHEN agricultural_activities.activity_type = "observation" THEN 1 ELSE 0 END) as obs_total,
                SUM(CASE WHEN agricultural_activities.activity_type = "observation" AND ob.threshold_exceeded = 1 THEN 1 ELSE 0 END) as obs_exceeded,
                SUM(CASE WHEN agricultural_activities.activity_type = "observation"
                     AND ob.follow_up_date IS NOT NULL AND ob.follow_up_date > NOW()
                     THEN 1 ELSE 0 END) as obs_pending,
                SUM(CASE WHEN agricultural_activities.activity_type IN ("cultural","pruning") THEN 1 ELSE 0 END) as cultural_total,
                SUM(CASE WHEN agricultural_activities.activity_type IN ("cultural","pruning")
                     AND cw.residue_management IS NOT NULL AND cw.residue_management != ""
                     THEN 1 ELSE 0 END) as cultural_residue
            ')
            ->first();

        $points = 0;
        $max = 0;

        // Fitosanitarios (peso 40%): justificación + ROPO
        $phytoTotal = (int) ($row->phyto_total ?? 0);
        if ($phytoTotal > 0) {
            $max += 40;
            $filled = (int) ($row->phyto_justified ?? 0) + (int) ($row->phyto_ropo ?? 0);
            $points += ($filled / ($phytoTotal * 2)) * 40;
        }

        // Observaciones (peso 30%): seguimiento de umbrales superados
        $obsTotal = (int) ($row->obs_total ?? 0);
        $obsExceeded = (int) ($row->obs_exceeded ?? 0);
        if ($obsTotal > 0) {
            $max += 30;
            $pending = (int) ($row->obs_pending ?? 0);
            $followed = max(0, $obsExceeded - $pending);
            $ratio = $obsExceeded > 0 ? ($followed / $obsExceeded) : 1.0;
            $points += $ratio * 30;
        }

        // Labores culturales (peso 30%): gestión de residuos BCAM 6
        $culturalTotal = (int) ($row->cultural_total ?? 0);
        if ($culturalTotal > 0) {
            $max += 30;
            $ratio = (int) ($row->cultural_residue ?? 0) / $culturalTotal;
            $points += $ratio * 30;
        }

        return $max > 0 ? (int) round(($points / $max) * 100) : 100;
    }
}
