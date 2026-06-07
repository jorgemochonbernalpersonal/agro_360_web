<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends BaseApiController
{
    // ─── GET /winery/clients ──────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request, 20, 50);

        $query = Client::where('user_id', $user->id)->latest();

        if ($request->filled('active')) {
            $query->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate($perPage);

        return $this->paginated($clients, ClientResource::collection($clients));
    }

    // ─── GET /winery/clients/{id} ─────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $client = Client::where('user_id', $user->id)->findOrFail($id);

        return $this->success(new ClientResource($client));
    }

    // ─── POST /winery/clients ─────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'client_type' => 'required|string|in:individual,company',
            'first_name' => 'required_if:client_type,individual|nullable|string|max:100',
            'last_name' => 'required_if:client_type,individual|nullable|string|max:100',
            'company_name' => 'required_if:client_type,company|nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'particular_document' => 'nullable|string|max:20',
            'company_document' => 'nullable|string|max:20',
            'payment_method' => 'nullable|string|in:transfer,cash,card,check,sepa',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client = Client::create(array_merge($validated, ['user_id' => $user->id]));

        return $this->created(new ClientResource($client));
    }

    // ─── PUT /winery/clients/{id} ─────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $client = Client::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'particular_document' => 'nullable|string|max:20',
            'company_document' => 'nullable|string|max:20',
            'payment_method' => 'nullable|string|in:transfer,cash,card,check,sepa',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client->update($validated);

        return $this->success(new ClientResource($client));
    }

    // ─── DELETE /winery/clients/{id} ──────────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $client = Client::where('user_id', $user->id)->findOrFail($id);
        $client->delete();

        return $this->deleted(__('Cliente eliminado correctamente.'));
    }
}
