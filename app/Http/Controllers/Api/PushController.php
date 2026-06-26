<?php

namespace App\Http\Controllers\Api;

use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushController extends BaseApiController
{
    // ─── POST /push/register ──────────────────────────────────────────────────

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:512',
            'platform' => 'required|string|in:android,ios',
            'device_name' => 'sometimes|nullable|string|max:255',
        ]);

        // Upsert: si el token ya existe lo reasignamos al usuario autenticado
        // (útil cuando un dispositivo se desloguea y loguea con otra cuenta)
        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? null,
            ]
        );

        return response()->json(['message' => __('Token registrado correctamente.')]);
    }

    // ─── DELETE /push/token ───────────────────────────────────────────────────

    public function unregister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        DeviceToken::where('token', $validated['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return $this->deleted(__('Token eliminado correctamente.'));
    }
}
