<?php

namespace App\Http\Requests\Api\Viticulturist;

class IndexTypedNotebookRequest extends ViticulturistApiRequest
{
    public function rules(): array
    {
        return [
            'plot_id' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
