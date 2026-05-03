<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LossResource;
use App\Http\Resources\Api\TransferResource;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
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
        $perPage = $this->resolvePerPage($request, 20, 50);

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
        $perPage = $this->resolvePerPage($request, 20, 50);

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
            'wine_id'                => 'required|integer|exists:wines,id',
            'from_container_id'      => 'required|integer|exists:containers,id',
            'to_container_id'        => 'required|integer|exists:containers,id|different:from_container_id',
            'quantity'               => 'required|numeric|min:0.001',
            'unit_of_measurement_id' => 'nullable|integer|exists:units_of_measurement,id',
            'transfer_type'          => 'required|string|in:racking,blending,top_up,other',
            'transfer_date'          => 'required|date',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['from_container_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['to_container_id']);

        // Default to Litros when the mobile client does not send a unit
        $unitId = $validated['unit_of_measurement_id']
            ?? UnitOfMeasurement::where('symbol', 'L')->value('id');

        $transfer = WineTransfer::create([
            ...$validated,
            'unit_of_measurement_id' => $unitId,
            'created_by'             => $user->id,
        ]);

        $transfer->load(['wine', 'fromContainer', 'toContainer']);

        return response()->json([
            'data'    => new TransferResource($transfer),
            'message' => 'Trasvase registrado correctamente.',
        ], 201);
    }

    // ─── POST /winery/losses ──────────────────────────────────────────────────

    public function storeLoss(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'              => 'required|integer|exists:wines,id',
            'container_id'         => 'required|integer|exists:containers,id',
            'loss_type'            => 'required|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization'      => 'required|string|in:authorized,processing,extraordinary,quality',
            'unit_of_measurement_id'  => 'required|integer|exists:units_of_measurement,id',
            'quantity'                => 'required|numeric|min:0.001',
            'loss_date'               => 'required|date',
            'regulatory_reference' => 'nullable|string|max:255',
            'notes'                => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['container_id']);

        $loss = WineLoss::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        $loss->load(['wine', 'container']);

        return response()->json([
            'data'    => new LossResource($loss),
            'message' => 'Merma registrada correctamente.',
        ], 201);
    }

    // ─── PUT /winery/transfers/{id} ──────────────────────────────────────────

    public function updateTransfer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $transfer = WineTransfer::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'from_container_id' => 'sometimes|integer|exists:containers,id',
            'to_container_id'   => 'sometimes|integer|exists:containers,id|different:from_container_id',
            'quantity'          => 'sometimes|numeric|min:0.001',
            'transfer_type'     => 'sometimes|string|in:racking,blending,top_up,other',
            'transfer_date'     => 'sometimes|date',
            'oenologist_id'     => 'sometimes|nullable|integer|exists:oenologists,id',
            'notes'             => 'sometimes|nullable|string|max:1000',
        ]);

        $transfer->update($validated);
        $transfer->load(['wine', 'fromContainer', 'toContainer']);

        return response()->json(['data' => new TransferResource($transfer)]);
    }

    // ─── PUT /winery/losses/{id} ──────────────────────────────────────────────

    public function updateLoss(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $loss = WineLoss::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'container_id'         => 'sometimes|integer|exists:containers,id',
            'loss_type'            => 'sometimes|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization'   => 'sometimes|string|in:authorized,processing,extraordinary,quality',
            'quantity'             => 'sometimes|numeric|min:0.001',
            'loss_date'            => 'sometimes|date',
            'regulatory_reference' => 'sometimes|nullable|string|max:255',
            'notes'                => 'sometimes|nullable|string|max:1000',
        ]);

        $loss->update($validated);
        $loss->load(['wine', 'container']);

        return response()->json(['data' => new LossResource($loss)]);
    }

    // ─── DELETE /winery/transfers/{id} ────────────────────────────────────────

    public function destroyTransfer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $transfer = WineTransfer::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $transfer->delete();

        return response()->json(['message' => 'Trasvase eliminado correctamente.']);
    }

    // ─── DELETE /winery/losses/{id} ───────────────────────────────────────────

    public function destroyLoss(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $loss = WineLoss::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $loss->delete();

        return response()->json(['message' => 'Merma eliminada correctamente.']);
    }
}
