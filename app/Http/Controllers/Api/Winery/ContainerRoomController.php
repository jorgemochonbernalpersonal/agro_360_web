<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\StoreContainerRoomRequest;
use App\Http\Requests\Api\Winery\UpdateContainerRoomRequest;
use App\Http\Requests\Api\Winery\WineryApiRequest;
use App\Models\ContainerRoom;
use Illuminate\Http\JsonResponse;

class ContainerRoomController extends BaseApiController
{
    public function index(WineryApiRequest $request): JsonResponse
    {
        $rooms = ContainerRoom::where('user_id', $request->user()->id)
            ->withCount('containers')
            ->orderBy('name')
            ->get();

        return $this->success($rooms->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'description' => $r->description,
            'temperature' => $r->temperature !== null ? (float) $r->temperature : null,
            'humidity' => $r->humidity !== null ? (float) $r->humidity : null,
            'capacity' => $r->capacity,
            'containers_count' => $r->containers_count,
            'created_at' => $r->created_at->toIso8601String(),
        ]));
    }

    public function store(StoreContainerRoomRequest $request): JsonResponse
    {
        $room = ContainerRoom::create([...$request->validated(), 'user_id' => $request->user()->id]);

        return $this->created($room);
    }

    public function show(WineryApiRequest $request, int $id): JsonResponse
    {
        $room = ContainerRoom::where('user_id', $request->user()->id)
            ->withCount('containers')
            ->findOrFail($id);

        return $this->success([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'temperature' => $room->temperature !== null ? (float) $room->temperature : null,
            'humidity' => $room->humidity !== null ? (float) $room->humidity : null,
            'capacity' => $room->capacity,
            'containers_count' => $room->containers_count,
            'created_at' => $room->created_at->toIso8601String(),
        ]);
    }

    public function update(UpdateContainerRoomRequest $request, int $id): JsonResponse
    {
        $room = ContainerRoom::where('user_id', $request->user()->id)->findOrFail($id);

        $room->update($request->validated());

        return $this->success($room->fresh());
    }

    public function destroy(WineryApiRequest $request, int $id): JsonResponse
    {
        $room = ContainerRoom::where('user_id', $request->user()->id)->findOrFail($id);
        abort_if($room->containers()->exists(), 422, 'No se puede eliminar una sala con depósitos asignados.');

        $room->delete();

        return $this->deleted(__('Sala eliminada correctamente.'));
    }
}
