<?php

namespace App\Http\Controllers\Api;

use App\Models\GrapeVariety;
use Illuminate\Http\JsonResponse;

class GrapeVarietyController extends BaseApiController
{
    /**
     * GET /api/v1/grape-varieties
     *
     * Returns active grape varieties sorted by name.
     * Used by mobile for dropdown selectors (external purchases, etc.).
     */
    public function __invoke(): JsonResponse
    {
        $varieties = GrapeVariety::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return $this->success($varieties);
    }
}
