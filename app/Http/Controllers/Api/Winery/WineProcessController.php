<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\LossResource;
use App\Http\Resources\Api\TransferResource;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WineProcessController extends BaseApiController
{
    // ─── GET /winery/transfers ────────────────────────────────────────────────

    public function indexTransfers(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request, 20, 50);

        $transfers = WineTransfer::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'fromContainer', 'toContainer', 'unitOfMeasurement'])
            ->latest('transfer_date')
            ->paginate($perPage);

        return response()->json([
            'data' => TransferResource::collection($transfers),
            'meta' => [
                'total' => $transfers->total(),
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/losses ───────────────────────────────────────────────────

    public function indexLosses(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request, 20, 50);

        $losses = WineLoss::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'container', 'unitOfMeasurement'])
            ->latest('loss_date')
            ->paginate($perPage);

        return response()->json([
            'data' => LossResource::collection($losses),
            'meta' => [
                'total' => $losses->total(),
                'current_page' => $losses->currentPage(),
                'last_page' => $losses->lastPage(),
            ],
        ]);
    }

    // ─── POST /winery/transfers ───────────────────────────────────────────────

    public function storeTransfer(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'wine_id' => 'required|integer|exists:wines,id',
            'from_container_id' => 'required|integer|exists:containers,id',
            'to_container_id' => 'required|integer|exists:containers,id|different:from_container_id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_of_measurement_id' => 'nullable|integer|exists:units_of_measurement,id',
            'transfer_type' => 'required|string|in:racking,blending,top_up,other',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['from_container_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['to_container_id']);

        // Default to Litros when the mobile client does not send a unit
        $unitId = $validated['unit_of_measurement_id']
            ?? UnitOfMeasurement::where('symbol', 'L')->value('id');

        $transfer = DB::transaction(function () use ($validated, $unitId, $user) {
            $transfer = WineTransfer::create([
                ...$validated,
                'unit_of_measurement_id' => $unitId,
                'created_by' => $user->id,
            ]);
            app(WineContainerStockService::class)->recordTransfer($transfer);

            return $transfer;
        });

        $transfer->load(['wine', 'fromContainer', 'toContainer', 'unitOfMeasurement']);

        return $this->created(new TransferResource($transfer));
    }

    // ─── POST /winery/losses ──────────────────────────────────────────────────

    public function storeLoss(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'wine_id' => 'required|integer|exists:wines,id',
            'container_id' => 'required|integer|exists:containers,id',
            'loss_type' => 'required|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization' => 'required|string|in:authorized,processing,extraordinary,quality',
            'unit_of_measurement_id' => 'required|integer|exists:units_of_measurement,id',
            'quantity' => 'required|numeric|min:0.001',
            'loss_date' => 'required|date',
            'regulatory_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['container_id']);

        $loss = DB::transaction(function () use ($validated, $user) {
            $loss = WineLoss::create([
                ...$validated,
                'created_by' => $user->id,
            ]);
            app(WineContainerStockService::class)->recordLoss($loss);

            return $loss;
        });

        $loss->load(['wine', 'container', 'unitOfMeasurement']);

        return $this->created(new LossResource($loss));
    }

    // ─── PUT /winery/transfers/{id} ──────────────────────────────────────────

    public function updateTransfer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transfer = WineTransfer::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'from_container_id' => 'sometimes|integer|exists:containers,id',
            'to_container_id' => 'sometimes|integer|exists:containers,id|different:from_container_id',
            'quantity' => 'sometimes|numeric|min:0.001',
            'transfer_type' => 'sometimes|string|in:racking,blending,top_up,other',
            'transfer_date' => 'sometimes|date',
            'oenologist_id' => 'sometimes|nullable|integer|exists:oenologists,id',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $oldData = [
            'wine_id' => $transfer->wine_id,
            'from_container_id' => $transfer->from_container_id,
            'to_container_id' => $transfer->to_container_id,
            'quantity' => $transfer->quantity,
            'source_wine_id' => $transfer->source_wine_id,
        ];

        DB::transaction(function () use ($transfer, $validated, $oldData) {
            $transfer->update($validated);
            app(WineContainerStockService::class)->updateTransfer($transfer->fresh(), $oldData);
        });

        $transfer->load(['wine', 'fromContainer', 'toContainer', 'unitOfMeasurement']);

        return $this->success(new TransferResource($transfer));
    }

    // ─── PUT /winery/losses/{id} ──────────────────────────────────────────────

    public function updateLoss(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $loss = WineLoss::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'container_id' => 'sometimes|integer|exists:containers,id',
            'loss_type' => 'sometimes|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization' => 'sometimes|string|in:authorized,processing,extraordinary,quality',
            'quantity' => 'sometimes|numeric|min:0.001',
            'loss_date' => 'sometimes|date',
            'regulatory_reference' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $oldData = [
            'wine_id' => $loss->wine_id,
            'container_id' => $loss->container_id,
            'quantity' => $loss->quantity,
        ];

        DB::transaction(function () use ($loss, $validated, $oldData) {
            $loss->update($validated);
            app(WineContainerStockService::class)->updateLoss($loss->fresh(), $oldData);
        });

        $loss->load(['wine', 'container', 'unitOfMeasurement']);

        return $this->success(new LossResource($loss));
    }

    // ─── DELETE /winery/transfers/{id} ────────────────────────────────────────

    public function destroyTransfer(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transfer = WineTransfer::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        DB::transaction(function () use ($transfer) {
            app(WineContainerStockService::class)->revertTransfer($transfer);
            $transfer->delete();
        });

        return $this->deleted(__('Trasvase eliminado correctamente.'));
    }

    // ─── DELETE /winery/losses/{id} ───────────────────────────────────────────

    public function destroyLoss(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $loss = WineLoss::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        DB::transaction(function () use ($loss) {
            app(WineContainerStockService::class)->revertLoss($loss);
            $loss->delete();
        });

        return $this->deleted(__('Merma eliminada correctamente.'));
    }
}
