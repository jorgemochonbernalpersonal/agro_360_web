<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Plot;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends BaseApiController
{
    public function __construct(
        private WeatherService $weatherService,
    ) {}

    // ─── GET /viticulturist/weather?plot_id=X ────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'plot_id' => 'required|integer|exists:plots,id',
        ]);

        /** @var Plot $plot */
        $plot = Plot::where('viticulturist_id', $user->id)->findOrFail($request->plot_id);

        $current = $this->weatherService->getCurrentWeather($plot);
        $forecast = $this->weatherService->getForecast($plot, 7);
        $soil = $this->weatherService->getSoilData($plot);

        return $this->success([
            'plot_id' => $plot->id,
            'plot_name' => $plot->plot_name ?? $plot->name,
            'current' => $current,
            'forecast' => $forecast,
            'soil' => $soil,
        ]);
    }
}
