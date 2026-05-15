<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\AdvisoryMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryMembershipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $items = AdvisoryMembership::where('viticulturist_id', $user->id)
            ->orderBy('advisor_name')
            ->paginate(20);

        return response()->json([
            'data' => $items->map(fn ($m) => $this->format($m)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $data = $request->validate([
            'advisor_name'   => 'required|string|max:255',
            'license_number' => 'required|string|max:50',
            'specialty'      => 'required|in:' . implode(',', array_keys(AdvisoryMembership::SPECIALTIES)),
            'company_name'   => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'campaign_id'    => 'nullable|exists:campaigns,id',
        ]);

        $membership = AdvisoryMembership::create([
            ...$data,
            'viticulturist_id' => $user->id,
            'active'           => true,
        ]);

        return response()->json(['data' => $this->format($membership)], 201);
    }

    private function format(AdvisoryMembership $m): array
    {
        return [
            'id'               => $m->id,
            'advisor_name'     => $m->advisor_name,
            'license_number'   => $m->license_number,
            'specialty'        => $m->specialty,
            'specialty_label'  => AdvisoryMembership::SPECIALTIES[$m->specialty] ?? $m->specialty,
            'company_name'     => $m->company_name,
            'phone'            => $m->phone,
            'email'            => $m->email,
            'campaign_id'      => $m->campaign_id,
            'active'           => (bool) $m->active,
            'created_at'       => $m->created_at->toIso8601String(),
        ];
    }
}
