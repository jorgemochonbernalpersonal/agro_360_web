<?php

namespace App\Http\Requests\Api\Winery;

use App\Models\WineryViticulturist;
use Illuminate\Validation\Validator;

class StoreWineryPlotRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'viticulturist_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'area' => 'required|numeric|min:0.001',
            'autonomous_community_id' => 'required|integer|exists:autonomous_communities,id',
            'province_id' => 'required|integer|exists:provinces,id',
            'municipality_id' => 'required|integer|exists:municipalities,id',
            'is_organic' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            if (! WineryViticulturist::where('winery_id', $this->user()->id)
                ->where('viticulturist_id', $this->input('viticulturist_id'))
                ->exists()) {
                $v->errors()->add('viticulturist_id', __('El viticultor no está vinculado a esta bodega.'));
            }
        });
    }
}
