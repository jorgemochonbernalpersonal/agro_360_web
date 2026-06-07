<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\ProducerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends BaseApiController
{
    public function __construct(private readonly ProducerDashboardService $dashboard) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = Cache::remember("producer_dashboard:{$user->id}", 60, fn () => $this->dashboard->stats($user));

        return response()->json($data)->header('Cache-Control', 'private, max-age=120');
    }
}
