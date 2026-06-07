<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Oenologist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OenologistController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = Oenologist::forUser($user->id);

        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        $oenologists = $query->orderBy('surname')->orderBy('name')->get();

        return $this->success($oenologists->map(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'surname' => $o->surname,
            'full_name' => $o->full_name,
            'license_number' => $o->license_number,
            'email' => $o->email,
            'phone' => $o->phone,
            'active' => $o->active,
            'notes' => $o->notes,
            'created_at' => $o->created_at->toIso8601String(),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'surname' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oenologist = Oenologist::create([...$validated, 'user_id' => $user->id, 'active' => $validated['active'] ?? true]);

        return $this->created(['id' => $oenologist->id, 'full_name' => $oenologist->full_name, ...$oenologist->toArray()]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $oenologist = Oenologist::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'surname' => 'sometimes|nullable|string|max:100',
            'license_number' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|nullable|email|max:150',
            'phone' => 'sometimes|nullable|string|max:30',
            'active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $oenologist->update($validated);

        return $this->success(['id' => $oenologist->id, 'full_name' => $oenologist->full_name, ...$oenologist->fresh()->toArray()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $oenologist = Oenologist::forUser($user->id)->findOrFail($id);
        $oenologist->update(['active' => false]);

        return $this->deleted(__('Enólogo desactivado correctamente.'));
    }
}
