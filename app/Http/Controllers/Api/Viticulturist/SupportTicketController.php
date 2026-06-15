<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends BaseApiController
{
    // ─── GET /viticulturist/support-tickets ──────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = SupportTicket::forUser($user->id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, SupportTicketResource::collection($items->items()));
    }

    // ─── POST /viticulturist/support-tickets ────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'type' => 'required|string|in:bug,feature,improvement,question',
            'priority' => 'nullable|string|in:urgent,high,medium,low',
        ]);

        $record = SupportTicket::create([
            ...$validated,
            'user_id' => $user->id,
            'status' => 'open',
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        return response()->json([
            'data' => new SupportTicketResource($record),
            'message' => __('Ticket de soporte creado correctamente.'),
        ], 201);
    }
}
