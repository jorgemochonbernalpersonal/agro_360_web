<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineTastingNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TastingNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = WineTastingNote::forUser($user->id)
            ->with(['wine', 'oenologist'])
            ->orderByDesc('evaluation_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $notes   = $query->paginate($perPage);

        return response()->json([
            'data' => $notes->map(fn ($n) => $this->format($n)),
            'meta' => [
                'total'        => $notes->total(),
                'per_page'     => $notes->perPage(),
                'current_page' => $notes->currentPage(),
                'last_page'    => $notes->lastPage(),
                'enums'        => [
                    'visual_clarity'   => WineTastingNote::visualClarityOptions(),
                    'visual_intensity' => WineTastingNote::visualIntensityOptions(),
                    'aroma_intensity'  => WineTastingNote::aromaIntensityOptions(),
                    'palate_level'     => WineTastingNote::palateLevelOptions(),
                    'palate_body'      => WineTastingNote::palateBodyOptions(),
                    'palate_finish'    => WineTastingNote::palateFinishOptions(),
                ],
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $note = WineTastingNote::forUser($user->id)->with(['wine', 'oenologist'])->findOrFail($id);

        return response()->json(['data' => $this->format($note)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'             => 'required|integer|exists:wines,id',
            'oenologist_id'       => 'nullable|integer|exists:oenologists,id',
            'evaluation_date'     => 'required|date',
            'evaluator_name'      => 'nullable|string|max:150',
            'visual_color'        => 'nullable|string|max:100',
            'visual_clarity'      => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::VISUAL_CLARITY)),
            'visual_intensity'    => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::VISUAL_INTENSITY)),
            'aroma_intensity'     => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::AROMA_INTENSITY)),
            'aroma_descriptors'   => 'nullable|string|max:500',
            'palate_acidity'      => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_LEVEL)),
            'palate_tannins'      => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_LEVEL)),
            'palate_body'         => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_BODY)),
            'palate_finish'       => 'nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_FINISH)),
            'overall_score'       => 'nullable|numeric|min:0|max:100',
            'overall_conclusion'  => 'nullable|string|max:2000',
            'notes'               => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $note = WineTastingNote::create([...$validated, 'user_id' => $user->id, 'created_by' => $user->id]);
        $note->load(['wine', 'oenologist']);

        return response()->json([
            'data'    => $this->format($note),
            'message' => __('Nota de cata registrada correctamente.'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $note = WineTastingNote::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'oenologist_id'       => 'sometimes|nullable|integer|exists:oenologists,id',
            'evaluation_date'     => 'sometimes|date',
            'evaluator_name'      => 'sometimes|nullable|string|max:150',
            'visual_color'        => 'sometimes|nullable|string|max:100',
            'visual_clarity'      => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::VISUAL_CLARITY)),
            'visual_intensity'    => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::VISUAL_INTENSITY)),
            'aroma_intensity'     => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::AROMA_INTENSITY)),
            'aroma_descriptors'   => 'sometimes|nullable|string|max:500',
            'palate_acidity'      => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_LEVEL)),
            'palate_tannins'      => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_LEVEL)),
            'palate_body'         => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_BODY)),
            'palate_finish'       => 'sometimes|nullable|string|in:' . implode(',', array_keys(WineTastingNote::PALATE_FINISH)),
            'overall_score'       => 'sometimes|nullable|numeric|min:0|max:100',
            'overall_conclusion'  => 'sometimes|nullable|string|max:2000',
            'notes'               => 'sometimes|nullable|string|max:1000',
        ]);

        $note->update($validated);
        $note->load(['wine', 'oenologist']);

        return response()->json(['data' => $this->format($note)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $note = WineTastingNote::forUser($user->id)->findOrFail($id);
        $note->delete();

        return response()->json(['message' => __('Nota de cata eliminada correctamente.')]);
    }

    private function format(WineTastingNote $n): array
    {
        return [
            'id'                 => $n->id,
            'wine_id'            => $n->wine_id,
            'wine_name'          => $n->wine?->name,
            'oenologist'         => $n->oenologist ? ['id' => $n->oenologist->id, 'name' => $n->oenologist->full_name] : null,
            'evaluation_date'    => $n->evaluation_date?->toDateString(),
            'evaluator_display'  => $n->evaluator_display,
            'visual_color'       => $n->visual_color,
            'visual_clarity'     => $n->visual_clarity,
            'visual_intensity'   => $n->visual_intensity,
            'aroma_intensity'    => $n->aroma_intensity,
            'aroma_descriptors'  => $n->aroma_descriptors,
            'palate_acidity'     => $n->palate_acidity,
            'palate_tannins'     => $n->palate_tannins,
            'palate_body'        => $n->palate_body,
            'palate_finish'      => $n->palate_finish,
            'overall_score'      => $n->overall_score !== null ? (float) $n->overall_score : null,
            'score_color'        => $n->score_badge_color,
            'overall_conclusion' => $n->overall_conclusion,
            'notes'              => $n->notes,
            'created_at'         => $n->created_at->toIso8601String(),
        ];
    }
}
