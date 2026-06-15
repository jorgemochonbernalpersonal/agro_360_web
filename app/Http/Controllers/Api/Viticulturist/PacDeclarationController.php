<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PacDeclaration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacDeclarationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = PacDeclaration::where('viticulturist_id', $user->id)
            ->orderByDesc('year')
            ->paginate(20);

        return $this->paginated($items, $items->map(fn ($d) => $this->format($d)));
    }

    private function format(PacDeclaration $d): array
    {
        return [
            'id' => $d->id,
            'year' => $d->year,
            'reference_number' => $d->reference_number,
            'status' => $d->status,
            'submitted_at' => $d->submitted_at?->toIso8601String(),
            'total_declared_area' => $d->total_declared_area,
            'total_eligible_area' => $d->total_eligible_area,
            'notes' => $d->notes,
            'created_at' => $d->created_at->toIso8601String(),
        ];
    }
}
