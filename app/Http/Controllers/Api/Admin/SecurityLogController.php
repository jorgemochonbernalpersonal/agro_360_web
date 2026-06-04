<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Admin\SecurityEventResource;
use App\Models\SecurityEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    // ─── GET /admin/security-log ──────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin->isAdmin(), 403);

        $perPage = $this->resolvePerPage($request, 30, 100);

        $query = SecurityEvent::latest('created_at');

        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('message', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('ip', 'like', "%{$term}%");
            });
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $items = $query->paginate($perPage);

        return response()->json([
            'data' => SecurityEventResource::collection($items),
            'meta' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }
}
