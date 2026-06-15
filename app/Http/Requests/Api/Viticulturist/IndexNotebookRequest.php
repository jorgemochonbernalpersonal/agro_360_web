<?php

namespace App\Http\Requests\Api\Viticulturist;

class IndexNotebookRequest extends ViticulturistApiRequest
{
    public function rules(): array
    {
        return [
            'type' => 'nullable|string|in:phytosanitary,fertilization,irrigation,cultural,observation,harvest,pruning,phenology,post_harvest',
            'plot_id' => 'nullable|integer|min:1',
            'campaign_id' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
