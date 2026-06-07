<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\InvoicingSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WinerySettingController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $settings = InvoicingSetting::getOrCreateForUser($user->id);

        return $this->success($this->format($settings));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'issuer_legal_name' => 'sometimes|nullable|string|max:255',
            'invoice_prefix' => 'sometimes|string|max:50',
            'invoice_padding' => 'sometimes|integer|min:1|max:10',
            'invoice_year_reset' => 'sometimes|boolean',
            'delivery_note_prefix' => 'sometimes|string|max:50',
            'delivery_note_padding' => 'sometimes|integer|min:1|max:10',
            'delivery_note_year_reset' => 'sometimes|boolean',
        ]);

        $settings = InvoicingSetting::getOrCreateForUser($user->id);
        $settings->update($validated);

        return $this->success($this->format($settings));
    }

    private function format(InvoicingSetting $s): array
    {
        return [
            'issuer_legal_name' => $s->issuer_legal_name,
            'invoice_prefix' => $s->invoice_prefix,
            'invoice_padding' => $s->invoice_padding,
            'invoice_counter' => $s->invoice_counter,
            'invoice_year_reset' => $s->invoice_year_reset,
            'invoice_preview' => $s->getInvoicePreview(),
            'delivery_note_prefix' => $s->delivery_note_prefix,
            'delivery_note_padding' => $s->delivery_note_padding,
            'delivery_note_counter' => $s->delivery_note_counter,
            'delivery_note_year_reset' => $s->delivery_note_year_reset,
            'delivery_note_preview' => $s->getDeliveryNotePreview(),
        ];
    }
}
