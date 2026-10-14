<?php

namespace App\Http\Requests\Api\Winery;

use Illuminate\Validation\Rule;

class UpdateLossRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'container_id' => ['sometimes', 'integer', Rule::exists('containers', 'id')->where('user_id', $this->user()->id)],
            'loss_type' => 'sometimes|string|in:evaporation,filtration,sampling,spillage,other',
            'loss_authorization' => 'sometimes|string|in:authorized,processing,extraordinary,quality',
            'quantity' => 'sometimes|numeric|min:0.001',
            'loss_date' => 'sometimes|date',
            'regulatory_reference' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }
}
