<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FieldEquipmentResource;
use App\Models\FieldEquipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldEquipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search'         => 'nullable|string|max:255',
            'equipment_type' => 'nullable|string|max:100',
        ]);

        $query = FieldEquipment::active()
            ->forViticulturist($user->id)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('registration_number', 'LIKE', $search);
            });
        }

        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', $request->equipment_type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => FieldEquipmentResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
