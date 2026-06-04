<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Validation\Validator;

class StoreGrapePurchaseInvoiceRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'viticulturist_id' => 'required|integer|exists:users,id',
            'invoice_number' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'payment_type' => 'nullable|string|in:transfer,cash,card,check,other',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'payment_details' => 'nullable|string|max:500',
            'observations' => 'nullable|string|max:2000',
            // Líneas de la liquidación
            'items' => 'required|array|min:1',
            'items.*.harvest_id' => 'nullable|integer|exists:harvests,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Reglas cross-entity que antes eran abort_unless/abort_if (422) en el
     * controlador. Como FormRequest validation también producen 422.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $wineryId = $this->user()->id;

            // El viticultor debe estar vinculado a esta bodega.
            $isLinked = WineryViticulturist::where('winery_id', $wineryId)
                ->where('viticulturist_id', $this->input('viticulturist_id'))
                ->exists();

            if (! $isLinked) {
                $validator->errors()->add('viticulturist_id', __('El viticultor no está vinculado a esta bodega.'));
            }

            // Toda recepción de uva referenciada debe pertenecer a esta bodega.
            $harvestIds = collect($this->input('items', []))
                ->pluck('harvest_id')
                ->filter()
                ->unique();

            if ($harvestIds->isNotEmpty()) {
                $invalid = Harvest::whereIn('id', $harvestIds)
                    ->where('winery_id', '!=', $wineryId)
                    ->exists();

                if ($invalid) {
                    $validator->errors()->add('items', __('Alguna recepción de uva no pertenece a esta bodega.'));
                }
            }
        });
    }
}
