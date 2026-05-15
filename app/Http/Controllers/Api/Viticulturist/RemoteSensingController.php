<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\RemoteSensingRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemoteSensingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $items = RemoteSensingRecord::whereHas('plot', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->with('plot:id,name')
            ->orderByDesc('image_date')
            ->paginate(20);

        return response()->json([
            'data' => $items->map(fn ($r) => $this->format($r)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    private function format(RemoteSensingRecord $r): array
    {
        return [
            'id'               => $r->id,
            'plot_id'          => $r->plot_id,
            'plot_name'        => $r->plot?->name,
            'image_date'       => $r->image_date,
            'ndvi_mean'        => $r->ndvi_mean,
            'health_status'    => $r->health_status,
            'cloud_coverage'   => $r->cloud_coverage,
            'ndwi_mean'        => $r->ndwi_mean,
            'temperature'      => $r->temperature,
            'humidity'         => $r->humidity,
            'precipitation'    => $r->precipitation,
            'soil_moisture'    => $r->soil_moisture,
            'trend'            => $r->trend,
            'anomaly_detected' => (bool) $r->anomaly_detected,
        ];
    }
}
