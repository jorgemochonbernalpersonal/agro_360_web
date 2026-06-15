<?php

namespace App\Http\Requests\Api\Winery;

class StoreContainerStockEntryRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'wine_id' => 'required|integer|exists:wines,id',
            'container_id' => 'required|integer|exists:containers,id',
            'quantity_liters' => 'required|numeric|min:0.001',
            'entry_date' => 'required|date',
            'source' => 'nullable|string|in:initial_stock,adjustment,correction',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
