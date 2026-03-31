<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\WineryAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryAlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = WineryAlert::forUser($user->id)
            ->active()
            ->orderByDesc('triggered_at');

        if ($request->filled('severity')) {
            $query->bySeverity($request->input('severity'));
        }
        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->input('alert_type'));
        }
        if ($request->boolean('unread_only', false)) {
            $query->unread();
        }

        $perPage = min($request->integer('per_page', 30), 100);
        $items   = $query->paginate($perPage);

        $unreadCount = WineryAlert::forUser($user->id)->active()->unread()->count();

        return response()->json([
            'data' => $items->map(fn ($a) => $this->format($a)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'unread_count' => $unreadCount,
                'types'        => WineryAlert::ALERT_TYPES,
                'severities'   => WineryAlert::SEVERITIES,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'alert_type'     => 'required|string|in:' . implode(',', array_keys(WineryAlert::ALERT_TYPES)),
            'severity'       => 'required|string|in:' . implode(',', array_keys(WineryAlert::SEVERITIES)),
            'title'          => 'required|string|max:255',
            'message'        => 'nullable|string|max:2000',
            'reference_type' => 'nullable|string|max:100',
            'reference_id'   => 'nullable|integer',
            'expires_at'     => 'nullable|date|after:now',
        ]);

        $validated['user_id']       = $user->id;
        $validated['auto_generated']= false;
        $validated['triggered_at']  = now();

        $alert = WineryAlert::create($validated);

        return response()->json([
            'data'    => $this->format($alert),
            'message' => 'Alerta creada correctamente.',
        ], 201);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $alert = WineryAlert::forUser($user->id)->findOrFail($id);

        $alert->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['data' => $this->format($alert)]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        WineryAlert::forUser($user->id)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Todas las alertas marcadas como leídas.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $alert = WineryAlert::forUser($user->id)->findOrFail($id);
        $alert->delete();

        return response()->json(['message' => 'Alerta eliminada.']);
    }

    private function format(WineryAlert $a): array
    {
        return [
            'id'             => $a->id,
            'alert_type'     => $a->alert_type,
            'type_label'     => $a->type_label,
            'severity'       => $a->severity,
            'severity_label' => $a->severity_label,
            'title'          => $a->title,
            'message'        => $a->message,
            'reference_type' => $a->reference_type,
            'reference_id'   => $a->reference_id,
            'is_read'        => $a->is_read,
            'read_at'        => $a->read_at?->toIso8601String(),
            'auto_generated' => $a->auto_generated,
            'triggered_at'   => $a->triggered_at?->toIso8601String(),
            'expires_at'     => $a->expires_at?->toIso8601String(),
            'is_expired'     => $a->isExpired(),
            'created_at'     => $a->created_at?->toIso8601String(),
        ];
    }
}
