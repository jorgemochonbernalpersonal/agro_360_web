<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorización en policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'plot_id' => ['required', 'exists:plots,id'],
            'plot_planting_id' => ['nullable', 'exists:plot_plantings,id'],
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'activity_type' => ['required', 'in:phytosanitary,fertilization,irrigation,cultural,observation,harvest'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'crew_id' => ['nullable', 'exists:crews,id'],
            'crew_member_id' => ['nullable', 'exists:crew_members,id'],
            'machinery_id' => ['nullable', 'exists:machineries,id'],
            'temperature' => ['nullable', 'numeric', 'min:-50', 'max:60'],
            'humidity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'wind_speed' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'plot_id.required' => 'Debe seleccionar una parcela.',
            'plot_id.exists' => 'La parcela seleccionada no existe.',
            'campaign_id.required' => 'Debe seleccionar una campaña.',
            'activity_type.required' => 'El tipo de actividad es obligatorio.',
            'activity_type.in' => 'El tipo de actividad no es válido.',
            'activity_date.required' => 'La fecha es obligatoria.',
            'activity_date.date' => 'La fecha no es válida.',
            'activity_date.before_or_equal' => 'La fecha no puede ser futura.',
            'temperature.numeric' => 'La temperatura debe ser un número.',
            'temperature.min' => 'La temperatura mínima es -50°C.',
            'temperature.max' => 'La temperatura máxima es 60°C.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'plot_id' => 'parcela',
            'campaign_id' => 'campaña',
            'activity_type' => 'tipo de actividad',
            'activity_date' => 'fecha',
            'temperature' => 'temperatura',
            'humidity' => 'humedad',
            'wind_speed' => 'velocidad del viento',
        ];
    }
}
