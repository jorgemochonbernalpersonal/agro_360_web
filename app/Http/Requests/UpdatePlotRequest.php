<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('plot'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'area' => ['sometimes', 'required', 'numeric', 'min:0.001', 'max:99999.999'],
            'pac_eligible_area' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'tenure_regime' => ['sometimes', 'required', 'in:propiedad,arrendamiento,aparceria,cesion'],
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
            'area.required' => 'La superficie es obligatoria.',
            'area.numeric' => 'La superficie debe ser un número.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Recalcular área no elegible si se actualizan las áreas
        if ($this->has('area') || $this->has('pac_eligible_area')) {
            $plot = $this->route('plot');
            $area = $this->input('area', $plot->area);
            $pacEligible = $this->input('pac_eligible_area', $plot->pac_eligible_area);
            
            $this->merge([
                'non_eligible_area' => max(0, $area - $pacEligible),
            ]);
        }
    }
}
