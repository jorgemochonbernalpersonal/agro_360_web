<?php

namespace App\Http\Requests\Api\Viticulturist;

class StoreContainerReturnRequest extends ViticulturistApiRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'phytosanitary_product_id' => 'nullable|integer|exists:phytosanitary_products,id',
            'date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'container_type' => 'required|string|in:plastic,glass,metal,cardboard,flexible,other',
            'container_size_liters' => 'nullable|numeric|min:0',
            'containers_quantity' => 'required|integer|min:1',
            'total_weight_kg' => 'nullable|numeric|min:0',
            'collection_system' => 'nullable|string|in:sigfito,field,other',
            'collection_point' => 'required|string|max:255',
            'transport_document' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
