<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\ContainerRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContainerRoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $rooms = ContainerRoom::where('user_id', $user->id)
            ->withCount('containers')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $rooms->map(fn ($r) => [
                'id'               => $r->id,
                'name'             => $r->name,
                'description'      => $r->description,
                'temperature'      => $r->temperature !== null ? (float) $r->temperature : null,
                'humidity'         => $r->humidity !== null ? (float) $r->humidity : null,
                'capacity'         => $r->capacity,
                'containers_count' => $r->containers_count,
                'created_at'       => $r->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'temperature' => 'nullable|numeric|between:-10,40',
            'humidity'    => 'nullable|numeric|between:0,100',
            'capacity'    => 'nullable|integer|min:1',
        ]);

        $room = ContainerRoom::create([...$validated, 'user_id' => $user->id]);

        return response()->json([
            'data'    => $room,
            'message' => 'Sala creada correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $room = ContainerRoom::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'description' => 'sometimes|nullable|string|max:500',
            'temperature' => 'sometimes|nullable|numeric|between:-10,40',
            'humidity'    => 'sometimes|nullable|numeric|between:0,100',
            'capacity'    => 'sometimes|nullable|integer|min:1',
        ]);

        $room->update($validated);

        return response()->json(['data' => $room->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $room = ContainerRoom::where('user_id', $user->id)->findOrFail($id);
        abort_if($room->containers()->exists(), 422, 'No se puede eliminar una sala con depósitos asignados.');

        $room->delete();

        return response()->json(['message' => 'Sala eliminada correctamente.']);
    }
}
