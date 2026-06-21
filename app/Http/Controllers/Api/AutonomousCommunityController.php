<?php

namespace App\Http\Controllers\Api;

use App\Models\AutonomousCommunity;
use Illuminate\Http\JsonResponse;

class AutonomousCommunityController extends BaseApiController
{
    /**
     * GET /api/v1/autonomous-communities
     *
     * Returns all autonomous communities sorted by name.
     * Used by mobile for cascading location selector.
     */
    public function __invoke(): JsonResponse
    {
        $communities = AutonomousCommunity::orderBy('name')
            ->get(['id', 'name', 'code']);

        return $this->success($communities);
    }
}
