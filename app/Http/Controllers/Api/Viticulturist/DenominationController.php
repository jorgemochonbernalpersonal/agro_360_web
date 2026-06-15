<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DoDocument;
use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DenominationController extends BaseApiController
{
    // ─── GET /viticulturist/denomination ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $relation = SupervisorViticulturist::where('viticulturist_id', $user->id)
            ->with('supervisor')
            ->first();

        if (! $relation) {
            return $this->success(null);
        }

        $supervisor = $relation->supervisor;

        // Wineries assigned by this DO to the viticulturist
        $wineries = WineryViticulturist::where('viticulturist_id', $user->id)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->where('supervisor_id', $supervisor->id)
            ->with('winery')
            ->get()
            ->map(fn ($rel) => [
                'id' => $rel->winery_id,
                'name' => $rel->winery?->name,
            ])
            ->filter(fn ($w) => $w['name'] !== null)
            ->values();

        // Active DO documents (pliego, reglamento)
        $documents = DoDocument::where('supervisor_id', $supervisor->id)
            ->where('status', DoDocument::STATUS_ACTIVE)
            ->orderByDesc('effective_date')
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => $doc->type,
                'version' => $doc->version,
                'effective_date' => $doc->effective_date?->toDateString(),
            ]);

        return $this->success([
            'supervisor_id' => $supervisor->id,
            'supervisor_name' => $supervisor->name,
            'supervisor_email' => $supervisor->email,
            'joined_at' => $relation->created_at->toIso8601String(),
            'notebook_access' => (bool) $relation->notebook_access,
            'notebook_granted_at' => $relation->notebook_granted_at?->toIso8601String(),
            'wineries' => $wineries,
            'documents' => $documents,
        ]);
    }
}
