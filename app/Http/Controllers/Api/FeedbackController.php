<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // ─── POST /feedback ───────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'        => 'required|in:bug,sugerencia,otro',
            'message'     => 'required|string|min:10|max:2000',
            'app_version' => 'sometimes|string|max:20',
        ]);

        Feedback::create([
            'user_id'     => $request->user()->id,
            'type'        => $validated['type'],
            'message'     => $validated['message'],
            'app_version' => $validated['app_version'] ?? null,
            'role'        => $request->user()->role,
        ]);

        return response()->json(['message' => __('Feedback recibido. ¡Gracias!')], 201);
    }
}
