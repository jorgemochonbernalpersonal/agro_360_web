<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    // ─── GET /viticulturist/support-tickets ──────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'status'   => 'nullable|string|in:open,in_progress,resolved,closed',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = SupportTicket::forUser($user->id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => SupportTicketResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/support-tickets ────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'type'        => 'required|string|in:bug,feature,improvement,question',
            'priority'    => 'nullable|string|in:urgent,high,medium,low',
        ]);

        $record = SupportTicket::create([
            ...$validated,
            'user_id'  => $user->id,
            'status'   => 'open',
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        return response()->json([
            'data'    => new SupportTicketResource($record),
            'message' => __('Ticket de soporte creado correctamente.'),
        ], 201);
    }
}
