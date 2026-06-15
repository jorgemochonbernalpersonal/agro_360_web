<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HarvestSummaryController extends BaseApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $year = $request->integer('year', now()->year);

        // ── Totales generales ────────────────────────────────────────────────
        $totals = Harvest::where('winery_id', $user->id)
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('
                COUNT(*) as total_receptions,
                SUM(total_weight) as total_kg,
                AVG(baume_degree) as avg_baume,
                AVG(brix_degree) as avg_brix,
                AVG(ph_level) as avg_ph,
                AVG(acidity_level) as avg_acidity,
                AVG(price_per_kg) as avg_price_per_kg
            ')
            ->first();

        // ── Por viticultor ───────────────────────────────────────────────────
        $byViticulturist = Harvest::where('harvests.winery_id', $user->id)
            ->where('harvests.status', 'active')
            ->whereYear('harvests.harvest_start_date', $year)
            ->join('grape_reception_batches as b', 'harvests.batch_id', '=', 'b.id')
            ->join('users as v', 'b.viticulturist_id', '=', 'v.id')
            ->selectRaw('
                v.id as viticulturist_id,
                v.name as viticulturist_name,
                COUNT(harvests.id) as receptions,
                SUM(harvests.total_weight) as total_kg,
                AVG(harvests.baume_degree) as avg_baume,
                AVG(harvests.price_per_kg) as avg_price_per_kg
            ')
            ->groupBy('v.id', 'v.name')
            ->orderByDesc('total_kg')
            ->get()
            ->map(fn ($r) => [
                'viticulturist_id' => $r->viticulturist_id,
                'viticulturist_name' => $r->viticulturist_name,
                'receptions' => (int) $r->receptions,
                'total_kg' => (float) $r->total_kg,
                'avg_baume' => $r->avg_baume !== null ? round((float) $r->avg_baume, 2) : null,
                'avg_price_per_kg' => $r->avg_price_per_kg !== null ? round((float) $r->avg_price_per_kg, 4) : null,
            ]);

        // ── Recepciones por semana ────────────────────────────────────────────
        $byWeek = Harvest::where('winery_id', $user->id)
            ->where('status', 'active')
            ->whereYear('harvest_start_date', $year)
            ->selectRaw('WEEK(harvest_start_date, 1) as week, COUNT(*) as count, SUM(total_weight) as total_kg')
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(fn ($r) => [
                'week' => (int) $r->week,
                'count' => (int) $r->count,
                'total_kg' => (float) $r->total_kg,
            ]);

        // ── Viticultores vinculados vs activos ────────────────────────────────
        $linkedCount = WineryViticulturist::where('winery_id', $user->id)->count();
        $activeCount = Harvest::where('harvests.winery_id', $user->id)
            ->where('harvests.status', 'active')
            ->whereYear('harvests.harvest_start_date', $year)
            ->join('grape_reception_batches as b', 'harvests.batch_id', '=', 'b.id')
            ->distinct()
            ->count('b.viticulturist_id');

        return response()->json([
            'year' => $year,
            'totals' => [
                'receptions' => (int) ($totals->total_receptions ?? 0),
                'total_kg' => (float) ($totals->total_kg ?? 0),
                'avg_baume' => $totals->avg_baume !== null ? round((float) $totals->avg_baume, 2) : null,
                'avg_brix' => $totals->avg_brix !== null ? round((float) $totals->avg_brix, 2) : null,
                'avg_ph' => $totals->avg_ph !== null ? round((float) $totals->avg_ph, 2) : null,
                'avg_acidity' => $totals->avg_acidity !== null ? round((float) $totals->avg_acidity, 2) : null,
                'avg_price_per_kg' => $totals->avg_price_per_kg !== null ? round((float) $totals->avg_price_per_kg, 4) : null,
            ],
            'viticulturists' => [
                'linked' => $linkedCount,
                'active' => $activeCount,
            ],
            'by_viticulturist' => $byViticulturist,
            'by_week' => $byWeek,
        ]);
    }
}
