<?php

namespace App\Http\Requests\Api\Winery;

class UpdateTransferRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'from_container_id' => 'sometimes|integer|exists:containers,id',
            'to_container_id' => 'sometimes|integer|exists:containers,id|different:from_container_id',
            'quantity' => 'sometimes|numeric|min:0.001',
            'transfer_type' => 'sometimes|string|in:racking,blending,top_up,other',
            'transfer_date' => 'sometimes|date',
            'oenologist_id' => 'sometimes|nullable|integer|exists:oenologists,id',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}
