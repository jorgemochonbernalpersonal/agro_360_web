<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\StoreLossRequest;
use App\Http\Requests\Api\Winery\StoreTransferRequest;
use App\Http\Requests\Api\Winery\UpdateLossRequest;
use App\Http\Requests\Api\Winery\UpdateTransferRequest;
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

    public function storeTransfer(StoreTransferRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

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

    public function storeLoss(StoreLossRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

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

    public function updateTransfer(UpdateTransferRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $transfer = WineTransfer::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validated();

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

    public function updateLoss(UpdateLossRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $loss = WineLoss::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validated();

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
