<?php

namespace App\Http\Requests\Api\Viticulturist;

class IndexContainerReturnRequest extends ViticulturistApiRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
