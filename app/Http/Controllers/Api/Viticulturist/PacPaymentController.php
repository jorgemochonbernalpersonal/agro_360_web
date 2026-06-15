<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PacPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacPaymentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = PacPayment::where('viticulturist_id', $user->id)
            ->orderByDesc('payment_date')
            ->paginate(20);

        return $this->paginated($items, $items->map(fn ($p) => $this->format($p)));
    }

    private function format(PacPayment $p): array
    {
        return [
            'id' => $p->id,
            'year' => $p->year,
            'payment_type' => $p->payment_type,
            'payment_type_label' => $p->payment_type_label ?? ucfirst(str_replace('_', ' ', $p->payment_type ?? '')),
            'amount' => $p->amount,
            'payment_date' => $p->payment_date,
            'reference' => $p->reference,
            'declaration_id' => $p->declaration_id,
            'created_at' => $p->created_at->toIso8601String(),
        ];
    }
}
