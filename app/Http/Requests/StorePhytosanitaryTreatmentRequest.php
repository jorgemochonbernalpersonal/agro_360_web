<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhytosanitaryTreatmentRequest extends FormRequest
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
        return [
            // Actividad agrícola base
            'plot_id' => ['required', 'exists:plots,id'],
            'plot_planting_id' => ['nullable', 'exists:plot_plantings,id'],
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'crew_id' => ['nullable', 'exists:crews,id'],
            'crew_member_id' => ['nullable', 'exists:crew_members,id'],
            'machinery_id' => ['nullable', 'exists:machineries,id'],
            'temperature' => ['nullable', 'numeric', 'min:-50', 'max:60'],
            'humidity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'wind_speed' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'notes' => ['nullable', 'string', 'max:2000'],
            
            // Tratamiento fitosanitario específico
            'product_id' => ['required', 'exists:phytosanitary_products,id'],
            'dose' => ['required', 'numeric', 'min:0.001'],
            'dose_unit' => ['required', 'in:l/ha,kg/ha,g/ha,ml/ha'],
            'area_treated' => ['required', 'numeric', 'min:0.001'],
            'total_product_used' => ['nullable', 'numeric', 'min:0'],
            'water_volume' => ['nullable', 'numeric', 'min:0'],
            'target_pest' => ['nullable', 'string', 'max:255'],
            'application_method' => ['required', 'in:pulverizacion,espolvoreo,riego,fumigacion'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => __('Debe seleccionar un producto fitosanitario.'),
            'product_id.exists' => __('El producto seleccionado no existe.'),
            'dose.required' => __('La dosis es obligatoria.'),
            'dose.numeric' => __('La dosis debe ser un número.'),
            'dose.min' => __('La dosis debe ser mayor a 0.'),
            'dose_unit.required' => __('La unidad de dosis es obligatoria.'),
            'area_treated.required' => __('La superficie tratada es obligatoria.'),
            'application_method.required' => __('El método de aplicación es obligatorio.'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'producto fitosanitario',
            'dose' => 'dosis',
            'dose_unit' => 'unidad de dosis',
            'area_treated' => 'superficie tratada',
            'target_pest' => 'plaga objetivo',
            'application_method' => 'método de aplicación',
        ];
    }
}
