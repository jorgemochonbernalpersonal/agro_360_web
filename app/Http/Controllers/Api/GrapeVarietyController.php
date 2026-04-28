<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GrapeVariety;
use Illuminate\Http\JsonResponse;

class GrapeVarietyController extends Controller
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

        return response()->json(['data' => $varieties]);
    }
}
