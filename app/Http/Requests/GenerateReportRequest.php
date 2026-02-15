<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'report_type' => ['required', 'in:phytosanitary_treatments,full_digital_notebook'],
            'password' => ['required', 'string', 'min:6'],
        ];

        // Reglas específicas por tipo de reporte
        if ($this->input('report_type') === 'phytosanitary_treatments') {
            $rules['period_start'] = ['required', 'date'];
            $rules['period_end'] = ['required', 'date', 'after_or_equal:period_start'];
        } else {
            $rules['campaign_id'] = ['required', 'exists:campaigns,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'report_type.required' => 'El tipo de informe es obligatorio.',
            'report_type.in' => 'El tipo de informe no es válido.',
            'password.required' => 'La contraseña de firma digital es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'period_start.required' => 'La fecha de inicio es obligatoria.',
            'period_start.date' => 'La fecha de inicio no es válida.',
            'period_end.required' => 'La fecha de fin es obligatoria.',
            'period_end.date' => 'La fecha de fin no es válida.',
            'period_end.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'campaign_id.required' => 'Debe seleccionar una campaña.',
            'campaign_id.exists' => 'La campaña seleccionada no existe.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'report_type' => 'tipo de informe',
            'password' => 'contraseña de firma',
            'period_start' => 'fecha de inicio',
            'period_end' => 'fecha de fin',
            'campaign_id' => 'campaña',
        ];
    }
}
