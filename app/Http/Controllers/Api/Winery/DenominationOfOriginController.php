<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DoDocument;
use App\Models\DoInspection;
use App\Models\DoLabel;
use App\Models\DoQualification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DenominationOfOriginController extends BaseApiController
{
    // GET /winery/do/qualifications
    public function qualifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DoQualification::where('winery_id', $user->id)
            ->orderByDesc('qualification_date');

        if ($request->filled('result')) {
            $query->where('result', $request->input('result'));
        }
        if ($request->filled('vintage')) {
            $query->where('vintage', $request->integer('vintage'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        return $this->paginated($items, $items->map(fn ($q) => $this->formatQualification($q)), [
            'results' => DoQualification::RESULT_LABELS,
            'colors' => DoQualification::COLOR_LABELS,
        ]);
    }

    // GET /winery/do/qualifications/{id}
    public function showQualification(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $q = DoQualification::where('winery_id', $user->id)->findOrFail($id);

        return $this->success($this->formatQualification($q));
    }

    // GET /winery/do/labels
    public function labels(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DoLabel::where('winery_id', $user->id)
            ->orderByDesc('requested_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('vintage')) {
            $query->where('vintage', $request->integer('vintage'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        return $this->paginated($items, $items->map(fn ($l) => $this->formatLabel($l)), [
            'statuses' => DoLabel::STATUS_LABELS,
        ]);
    }

    // GET /winery/do/labels/{id}
    public function showLabel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $l = DoLabel::where('winery_id', $user->id)->findOrFail($id);

        return $this->success($this->formatLabel($l));
    }

    // GET /winery/do/inspections
    public function inspections(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DoInspection::where('subject_id', $user->id)
            ->orderByDesc('inspection_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('result')) {
            $query->where('result', $request->input('result'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        return $this->paginated($items, $items->map(fn ($i) => $this->formatInspection($i)), [
            'statuses' => DoInspection::STATUS_LABELS,
            'results' => DoInspection::RESULT_LABELS,
        ]);
    }

    // GET /winery/do/documents (reglamentos/pliegos activos del organismo regulador)
    public function documents(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DoDocument::where('status', 'active')
            ->orderByDesc('effective_date');

        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        return $this->paginated($items, $items->map(fn ($d) => $this->formatDocument($d)));
    }

    // ── Formatters ────────────────────────────────────────────────────────────

    private function formatQualification(DoQualification $q): array
    {
        return [
            'id' => $q->id,
            'vintage' => $q->vintage,
            'wine_name' => $q->wine_name,
            'color' => $q->color,
            'color_label' => DoQualification::COLOR_LABELS[$q->color] ?? $q->color,
            'alcohol_percentage' => $q->alcohol_percentage !== null ? (float) $q->alcohol_percentage : null,
            'brix_degree' => $q->brix_degree !== null ? (float) $q->brix_degree : null,
            'acidity_level' => $q->acidity_level !== null ? (float) $q->acidity_level : null,
            'ph_level' => $q->ph_level !== null ? (float) $q->ph_level : null,
            'visual_score' => $q->visual_score,
            'aroma_score' => $q->aroma_score,
            'taste_score' => $q->taste_score,
            'overall_score' => $q->overall_score,
            'result' => $q->result,
            'result_label' => DoQualification::RESULT_LABELS[$q->result] ?? $q->result,
            'tasting_notes' => $q->tasting_notes,
            'qualification_date' => $q->qualification_date?->toDateString(),
            'created_at' => $q->created_at->toIso8601String(),
        ];
    }

    private function formatLabel(DoLabel $l): array
    {
        return [
            'id' => $l->id,
            'vintage' => $l->vintage,
            'batch_number' => $l->batch_number,
            'quantity_requested' => $l->quantity_requested,
            'quantity_issued' => $l->quantity_issued,
            'quantity_stock' => $l->quantity_stock,
            'status' => $l->status,
            'status_label' => DoLabel::STATUS_LABELS[$l->status] ?? $l->status,
            'notes' => $l->notes,
            'requested_at' => $l->requested_at?->toIso8601String(),
            'issued_at' => $l->issued_at?->toIso8601String(),
            'created_at' => $l->created_at->toIso8601String(),
        ];
    }

    private function formatInspection(DoInspection $i): array
    {
        return [
            'id' => $i->id,
            'inspection_date' => $i->inspection_date?->toDateString(),
            'status' => $i->status,
            'status_label' => DoInspection::STATUS_LABELS[$i->status] ?? $i->status,
            'result' => $i->result,
            'result_label' => DoInspection::RESULT_LABELS[$i->result] ?? $i->result,
            'findings' => $i->findings,
            'notes' => $i->notes,
            'reference_number' => $i->reference_number,
            'created_at' => $i->created_at->toIso8601String(),
        ];
    }

    private function formatDocument(DoDocument $d): array
    {
        return [
            'id' => $d->id,
            'type' => $d->type,
            'title' => $d->title,
            'version' => $d->version,
            'effective_date' => $d->effective_date?->toDateString(),
            'content' => $d->content,
            'status' => $d->status,
            'status_label' => DoDocument::STATUS_LABELS[$d->status] ?? $d->status,
            'created_at' => $d->created_at->toIso8601String(),
        ];
    }
}
