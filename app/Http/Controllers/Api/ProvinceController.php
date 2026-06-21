<?php

namespace App\Http\Controllers\Api;

use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvinceController extends BaseApiController
{
    /**
     * GET /api/v1/provinces?autonomous_community_id=X
     *
     * Returns provinces filtered by autonomous community.
     * Used by mobile for cascading location selector.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $communityId = $request->get('autonomous_community_id');

        $query = Province::orderBy('name');

        if ($communityId) {
            $query->where('autonomous_community_id', $communityId);
        }

        return $this->success($query->get(['id', 'name', 'autonomous_community_id']));
    }
}
