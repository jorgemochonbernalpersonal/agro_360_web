<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\CueExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CueExportController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $items = CueExport::whereHas('exploitation', fn ($q) => $q->where('viticulturist_id', $user->id))
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->paginated($items, $items->map(fn ($e) => $this->format($e)));
    }

    private function format(CueExport $e): array
    {
        return [
            'id' => $e->id,
            'exploitation_id' => $e->exploitation_id,
            'campaign_year' => $e->campaign_year,
            'period_type' => $e->period_type,
            'from_date' => $e->from_date,
            'to_date' => $e->to_date,
            'status' => $e->status,
            'status_label' => $e->status_label ?? ucfirst($e->status ?? ''),
            'generated_at' => $e->generated_at?->toIso8601String(),
            'sent_at' => $e->sent_at?->toIso8601String(),
            'error_message' => $e->error_message,
            'created_at' => $e->created_at->toIso8601String(),
        ];
    }
}
