<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    // ─── Response helpers ─────────────────────────────────────────────────────

    protected function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    protected function created(mixed $data): JsonResponse
    {
        return $this->success($data, 201);
    }

    protected function deleted(string $message): JsonResponse
    {
        return response()->json(['message' => $message]);
    }

    // ─── Pagination ───────────────────────────────────────────────────────────

    /**
     * Build the standard meta block from a paginator.
     * Merge extra keys (e.g. aggregate stats) before returning.
     *
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    protected function paginationMeta(LengthAwarePaginator $paginator, array $extra = []): array
    {
        return array_merge([
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'has_more' => $paginator->hasMorePages(),
        ], $extra);
    }

    /**
     * Return a paginated JSON response with a standard meta block.
     *
     * @param array<string, mixed> $extraMeta
     */
    protected function paginated(
        LengthAwarePaginator $paginator,
        mixed $data,
        array $extraMeta = [],
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'meta' => $this->paginationMeta($paginator, $extraMeta),
        ]);
    }
}
