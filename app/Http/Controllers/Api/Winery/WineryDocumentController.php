<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\WineryDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = WineryDocument::forUser($user->id)
            ->orderByDesc('issue_date');

        if ($request->filled('type')) {
            $query->where('document_type', $request->input('type'));
        }
        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($d) => $this->format($d)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'types'        => WineryDocument::DOCUMENT_TYPES,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $document = WineryDocument::forUser($user->id)->findOrFail($id);

        return response()->json(['data' => $this->format($document)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'document_type'      => 'required|string|in:' . implode(',', array_keys(WineryDocument::DOCUMENT_TYPES)),
            'reference_number'   => 'nullable|string|max:100',
            'issue_date'         => 'nullable|date',
            'expiry_date'        => 'nullable|date',
            'issuing_authority'  => 'nullable|string|max:255',
            'notes'              => 'nullable|string|max:1000',
            'active'             => 'boolean',
        ]);

        $validated['user_id'] = $user->id;
        $validated['active'] ??= true;

        $document = WineryDocument::create($validated);

        return response()->json([
            'data'    => $this->format($document),
            'message' => 'Documento creado correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $document = WineryDocument::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'title'             => 'sometimes|string|max:255',
            'document_type'     => 'sometimes|string|in:' . implode(',', array_keys(WineryDocument::DOCUMENT_TYPES)),
            'reference_number'  => 'sometimes|nullable|string|max:100',
            'issue_date'        => 'sometimes|nullable|date',
            'expiry_date'       => 'sometimes|nullable|date',
            'issuing_authority' => 'sometimes|nullable|string|max:255',
            'notes'             => 'sometimes|nullable|string|max:1000',
            'active'            => 'sometimes|boolean',
        ]);

        $document->update($validated);

        return response()->json(['data' => $this->format($document)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $document = WineryDocument::forUser($user->id)->findOrFail($id);
        $document->delete();

        return response()->json(['message' => 'Documento eliminado correctamente.']);
    }

    private function format(WineryDocument $d): array
    {
        return [
            'id'                => $d->id,
            'title'             => $d->title,
            'document_type'     => $d->document_type,
            'type_label'        => $d->type_label,
            'reference_number'  => $d->reference_number,
            'issue_date'        => $d->issue_date?->toDateString(),
            'expiry_date'       => $d->expiry_date?->toDateString(),
            'issuing_authority' => $d->issuing_authority,
            'active'            => $d->active,
            'expiring_soon'     => $d->isExpiringSoon(),
            'expired'           => $d->isExpired(),
            'notes'             => $d->notes,
            'created_at'        => $d->created_at->toIso8601String(),
        ];
    }
}
