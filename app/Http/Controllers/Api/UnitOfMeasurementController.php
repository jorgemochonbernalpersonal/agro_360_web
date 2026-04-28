<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\JsonResponse;

class UnitOfMeasurementController extends Controller
{
    /**
     * GET /api/v1/units-of-measurement
     *
     * Returns all units of measurement sorted by name.
     * Used by mobile for dropdown selectors (supplies, etc.).
     */
    public function __invoke(): JsonResponse
    {
        $units = UnitOfMeasurement::orderBy('name')
            ->get(['id', 'name', 'symbol', 'type']);

        return response()->json(['data' => $units]);
    }
}
