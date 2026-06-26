<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait WithPlotFormRules
{
    protected function plotFormRules(bool $producerRequiresViticulturist = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'required|numeric|min:0.001',
            'active' => 'boolean',
            'code_parcel' => 'nullable|string|max:50',
            'orientation_id' => 'nullable|exists:orientations,id',
            'degree_day_base' => 'nullable|numeric|min:0|max:30',
            'cadastral_area' => 'nullable|numeric|min:0',
            'is_organic' => 'boolean',
            'soil_type_id' => 'nullable|exists:soil_types,id',
            'irrigation_type_id' => 'nullable|exists:irrigation_types,id',
            'topography_id' => 'nullable|exists:topographies,id',
            'property_type_id' => 'nullable|exists:property_types,id',
            'valley_id' => 'nullable|exists:valleys,id',
            'site_id' => 'nullable|exists:sites,id',
            'owner_id' => 'nullable|exists:users,id',
            'enclosure' => 'nullable|string|max:100',
            'planting_pattern' => 'nullable|string|max:50',
            'slope' => 'nullable|numeric|min:0|max:100',
            'pac_eligible_area' => 'nullable|numeric|min:0|lte:area',
            'non_eligible_area' => 'nullable|numeric|min:0|lte:area',
        ];

        $viticulturistRoles = ['admin', 'supervisor', 'winery', 'viticulturist'];
        if ($producerRequiresViticulturist) {
            $viticulturistRoles[] = 'producer';
        }

        if (in_array(Auth::user()->role, $viticulturistRoles)) {
            $rules['viticulturist_id'] = 'required|exists:users,id';
        }

        if ($this->canSelectLocation()) {
            $rules['autonomous_community_id'] = 'required|exists:autonomous_communities,id';
            $rules['province_id'] = 'required|exists:provinces,id';
            $rules['municipality_id'] = 'required|exists:municipalities,id';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'pac_eligible_area.lte' => __('La superficie admisible PAC no puede superar la superficie total de la parcela.'),
            'non_eligible_area.lte' => __('La superficie no admisible no puede superar la superficie total de la parcela.'),
        ];
    }
}
