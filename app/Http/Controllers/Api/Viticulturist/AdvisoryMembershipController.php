<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AdvisoryMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryMembershipController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = AdvisoryMembership::where('viticulturist_id', $user->id)
            ->orderBy('advisor_name')
            ->paginate(20);

        return $this->paginated($items, $items->map(fn ($m) => $this->format($m)));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'advisor_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:50',
            'specialty' => 'required|in:'.implode(',', array_keys(AdvisoryMembership::SPECIALTIES)),
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'campaign_id' => 'nullable|exists:campaigns,id',
        ]);

        $membership = AdvisoryMembership::create([
            ...$data,
            'viticulturist_id' => $user->id,
            'active' => true,
        ]);

        return $this->created($this->format($membership));
    }

    private function format(AdvisoryMembership $m): array
    {
        return [
            'id' => $m->id,
            'advisor_name' => $m->advisor_name,
            'license_number' => $m->license_number,
            'specialty' => $m->specialty,
            'specialty_label' => AdvisoryMembership::SPECIALTIES[$m->specialty] ?? $m->specialty,
            'company_name' => $m->company_name,
            'phone' => $m->phone,
            'email' => $m->email,
            'campaign_id' => $m->campaign_id,
            'active' => (bool) $m->active,
            'created_at' => $m->created_at->toIso8601String(),
        ];
    }
}
