<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends BaseApiController
{
    // ─── GET /viticulturist/clients ──────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Client::forUser($user->id)
            ->active()
            ->orderBy('company_name')
            ->orderBy('first_name');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('company_name', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('company_document', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, ClientResource::collection($items->items()));
    }

    // ─── POST /viticulturist/clients ────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'client_type' => 'required|string|in:company,individual',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|required_if:client_type,company|string|max:255',
            'company_document' => 'nullable|string|max:20',
            'particular_document' => 'nullable|string|max:20',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:34',
            'notes' => 'nullable|string|max:2000',
        ]);

        $record = Client::create([
            ...$validated,
            'user_id' => $user->id,
            'active' => true,
        ]);

        return response()->json([
            'data' => new ClientResource($record),
            'message' => __('Cliente registrado correctamente.'),
        ], 201);
    }
}
