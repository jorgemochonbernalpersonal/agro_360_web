<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // ─── GET /winery/clients ──────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 20), 50);

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

        return response()->json([
            'data' => ClientResource::collection($clients),
            'meta' => [
                'total'        => $clients->total(),
                'current_page' => $clients->currentPage(),
                'last_page'    => $clients->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/clients/{id} ─────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user   = $request->user();
        $client = Client::where('user_id', $user->id)->findOrFail($id);

        return response()->json(['data' => new ClientResource($client)]);
    }
}
