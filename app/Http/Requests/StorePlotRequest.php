<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'area' => ['required', 'numeric', 'min:0.001', 'max:99999.999'],
            'pac_eligible_area' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'tenure_regime' => ['required', 'in:propiedad,arrendamiento,aparceria,cesion'],
            'viticulturist_id' => ['required', 'exists:users,id'],
            'autonomous_community_id' => ['required', 'exists:autonomous_communities,id'],
            'province_id' => ['required', 'exists:provinces,id'],
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'active' => ['boolean'],
            'ndvi_alert_threshold' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'alert_email_enabled' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la parcela es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'area.required' => 'La superficie es obligatoria.',
            'area.numeric' => 'La superficie debe ser un número.',
            'area.min' => 'La superficie debe ser mayor a 0.',
            'tenure_regime.required' => 'El régimen de tenencia es obligatorio.',
            'tenure_regime.in' => 'El régimen de tenencia no es válido.',
            'viticulturist_id.required' => 'Debe seleccionar un viticultor.',
            'viticulturist_id.exists' => 'El viticultor seleccionado no existe.',
            'province_id.required' => 'La provincia es obligatoria.',
            'municipality_id.required' => 'El municipio es obligatorio.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'area' => 'superficie',
            'pac_eligible_area' => 'superficie elegible PAC',
            'tenure_regime' => 'régimen de tenencia',
            'viticulturist_id' => 'viticultor',
            'province_id' => 'provincia',
            'municipality_id' => 'municipio',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Calcular área elegible PAC si no se proporciona
        if (! $this->has('pac_eligible_area') && $this->has('area')) {
            $this->merge([
                'pac_eligible_area' => $this->input('area'),
            ]);
        }

        // Calcular área no elegible
        if ($this->has('area') && $this->has('pac_eligible_area')) {
            $this->merge([
                'non_eligible_area' => max(0, $this->input('area') - $this->input('pac_eligible_area')),
            ]);
        }
    }
}
