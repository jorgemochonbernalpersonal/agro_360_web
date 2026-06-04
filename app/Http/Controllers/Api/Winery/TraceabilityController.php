<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TraceabilityController extends Controller
{
    // ─── GET /winery/grape-receptions/{id}/traceability ───────────────────────
    // Vinos que se elaboraron con esta recepción de uva

    public function receptionWines(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $harvest = Harvest::where('winery_id', $user->id)->findOrFail($id);

        $wines = $harvest->wines()
            ->select(['wines.id', 'wines.name', 'wines.vintage', 'wines.wine_type', 'wines.status', 'wines.variety', 'wines.internal_code'])
            ->withPivot(['quantity_kg', 'percentage'])
            ->get()
            ->map(fn ($wine) => [
                'id' => $wine->id,
                'name' => $wine->name,
                'vintage' => $wine->vintage,
                'wine_type' => $wine->wine_type,
                'status' => $wine->status,
                'variety' => $wine->variety,
                'internal_code' => $wine->internal_code,
                'quantity_kg' => $wine->pivot->quantity_kg !== null ? (float) $wine->pivot->quantity_kg : null,
                'percentage' => $wine->pivot->percentage !== null ? (float) $wine->pivot->percentage : null,
            ]);

        return response()->json([
            'reception_id' => $harvest->id,
            'data' => $wines,
        ]);
    }

    // ─── GET /winery/wines/{id}/traceability ─────────────────────────────────
    // Recepciones de uva que entraron en este vino

    public function wineReceptions(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $wine = Wine::where('user_id', $user->id)->findOrFail($id);

        $harvests = $wine->harvests()
            ->with(['batch.viticulturist', 'plotPlanting.grapeVariety'])
            ->withPivot(['quantity_kg', 'percentage'])
            ->get()
            ->map(fn ($harvest) => [
                'id' => $harvest->id,
                'date' => $harvest->harvest_start_date?->toDateString(),
                'vintage' => $harvest->vintage,
                'total_weight' => (float) $harvest->total_weight,
                'brix_degree' => $harvest->brix_degree !== null ? (float) $harvest->brix_degree : null,
                'price_per_kg' => $harvest->price_per_kg !== null ? (float) $harvest->price_per_kg : null,
                'viticulturist' => $harvest->batch?->viticulturist
                    ? ['id' => $harvest->batch->viticulturist->id, 'name' => $harvest->batch->viticulturist->name]
                    : null,
                'grape_variety' => $harvest->plotPlanting?->grapeVariety?->name,
                'quantity_kg' => $harvest->pivot->quantity_kg !== null ? (float) $harvest->pivot->quantity_kg : null,
                'percentage' => $harvest->pivot->percentage !== null ? (float) $harvest->pivot->percentage : null,
            ]);

        return response()->json([
            'wine_id' => $wine->id,
            'data' => $harvests,
        ]);
    }
}
