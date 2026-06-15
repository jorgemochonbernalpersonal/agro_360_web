<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PlotPlanting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlantingController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = PlotPlanting::whereHas('plot', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->with(['plot:id,name', 'grapeVariety:id,name'])
            ->orderByDesc('planting_year')
            ->paginate(20);

        return $this->paginated($items, $items->map(fn ($p) => $this->format($p)));
    }

    private function format(PlotPlanting $p): array
    {
        return [
            'id' => $p->id,
            'plot_id' => $p->plot_id,
            'plot_name' => $p->plot?->name,
            'grape_variety' => $p->grapeVariety?->name,
            'area_ha' => $p->area_planted,
            'plant_count' => $p->vine_count,
            'planting_year' => $p->planting_year,
            'rootstock' => $p->rootstock,
            'row_spacing' => $p->row_spacing,
            'plant_spacing' => $p->vine_spacing,
            'training_system' => $p->training_system,
            'is_organic' => false,
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }
}
