<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LossResource;
use App\Http\Resources\Api\TransferResource;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineProcessController extends Controller
{
    // ─── GET /winery/transfers ────────────────────────────────────────────────

    public function indexTransfers(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 50);

        $transfers = WineTransfer::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'fromContainer', 'toContainer'])
            ->latest('transfer_date')
            ->paginate($perPage);

        return response()->json([
            'data' => TransferResource::collection($transfers),
            'meta' => [
                'total'        => $transfers->total(),
                'current_page' => $transfers->currentPage(),
                'last_page'    => $transfers->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/losses ───────────────────────────────────────────────────

    public function indexLosses(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 50);

        $losses = WineLoss::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'container'])
            ->latest('loss_date')
            ->paginate($perPage);

        return response()->json([
            'data' => LossResource::collection($losses),
            'meta' => [
                'total'        => $losses->total(),
                'current_page' => $losses->currentPage(),
                'last_page'    => $losses->lastPage(),
            ],
        ]);
    }

    // ─── POST /winery/transfers ───────────────────────────────────────────────

    public function storeTransfer(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'           => 'required|integer',
            'from_container_id' => 'required|integer',
            'to_container_id'   => 'required|integer|different:from_container_id',
            'quantity'          => 'required|numeric|min:0.001',
            'transfer_type'     => 'required|string|in:racking,blending,top_up,other',
            'transfer_date'     => 'required|date',
            'notes'             => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['from_container_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['to_container_id']);

        $transfer = WineTransfer::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'data'    => $transfer,
            'message' => 'Trasvase registrado correctamente.',
        ], 201);
    }

    // ─── POST /winery/losses ──────────────────────────────────────────────────

    public function storeLoss(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'              => 'required|integer',
            'container_id'         => 'required|integer',
            'loss_type'            => 'required|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization'   => 'required|string|in:authorized,processing,extraordinary,quality',
            'quantity'             => 'required|numeric|min:0.001',
            'loss_date'            => 'required|date',
            'regulatory_reference' => 'nullable|string|max:255',
            'notes'                => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['container_id']);

        $loss = WineLoss::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'data'    => $loss,
            'message' => 'Merma registrada correctamente.',
        ], 201);
    }
}
