<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Services\RemoteSensing\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(
        private WeatherService $weatherService,
    ) {}

    // ─── GET /viticulturist/weather?plot_id=X ────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'plot_id' => 'required|integer|exists:plots,id',
        ]);

        $plot = Plot::where('viticulturist_id', $user->id)->findOrFail($request->plot_id);

        $current  = $this->weatherService->getCurrentWeather($plot);
        $forecast = $this->weatherService->getForecast($plot, 7);
        $soil     = $this->weatherService->getSoilData($plot);

        return response()->json([
            'data' => [
                'plot_id'   => $plot->id,
                'plot_name' => $plot->plot_name ?? $plot->name,
                'current'   => $current,
                'forecast'  => $forecast,
                'soil'      => $soil,
            ],
        ]);
    }
}
