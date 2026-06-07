<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Municipality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MunicipalityController extends BaseApiController
{
    /**
     * GET /api/v1/municipalities?province_id=X
     * GET /api/v1/municipalities?search=xxx
     *
     * Returns municipalities filtered by province (for cascading selector)
     * or by name search. province_id takes precedence.
     * Used by mobile for cascading location selector in plot creation.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $provinceId = $request->get('province_id');
        $search = $request->get('search', '');

        if ($provinceId) {
            $municipalities = Municipality::where('province_id', $provinceId)
                ->orderBy('name')
                ->get(['id', 'name', 'province_id']);

            return $this->success($municipalities);
        }

        if (strlen($search) < 2) {
            return $this->success([]);
        }

        $municipalities = Municipality::where('name', 'like', '%'.$search.'%')
            ->with('province:id,name')
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'province_id']);

        $data = $municipalities->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'province_id' => $m->province_id,
            'province' => $m->province ? ['id' => $m->province->id, 'name' => $m->province->name] : null,
        ]);

        return $this->success($data);
    }
}
