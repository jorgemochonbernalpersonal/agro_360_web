<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'client_id' => ['required', 'exists:clients,id'],
            'client_address_id' => ['nullable', 'exists:client_addresses,id'],
            'invoice_date' => ['required', 'date', 'before_or_equal:today'],
            'delivery_note_date' => ['required', 'date', 'before_or_equal:today'],
            'delivery_note_code' => ['required', 'string', 'max:100'],
            'observations' => ['nullable', 'string', 'max:1000'],
            'observations_invoice' => ['nullable', 'string', 'max:1000'],
            
            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_id' => ['required', 'exists:taxes,id'],
            'items.*.harvest_id' => ['nullable', 'exists:harvests,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => __('Debe seleccionar un cliente.'),
            'invoice_date.required' => __('La fecha de factura es obligatoria.'),
            'delivery_note_code.required' => __('El código de albarán es obligatorio.'),
            'items.required' => __('Debe agregar al menos un ítem a la factura.'),
            'items.min' => __('Debe agregar al menos un ítem a la factura.'),
            'items.*.description.required' => __('La descripción del ítem es obligatoria.'),
            'items.*.quantity.required' => __('La cantidad es obligatoria.'),
            'items.*.quantity.min' => __('La cantidad debe ser mayor a 0.'),
            'items.*.unit_price.required' => __('El precio unitario es obligatorio.'),
            'items.*.tax_id.required' => __('Debe seleccionar un impuesto.'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => __('cliente'),
            'invoice_date' => __('fecha de factura'),
            'delivery_note_code' => __('código de albarán'),
            'items' => __('ítems'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asegurar que delivery_note_date tenga un valor por defecto
        if (!$this->has('delivery_note_date')) {
            $this->merge([
                'delivery_note_date' => $this->input('invoice_date', now()->format('Y-m-d')),
            ]);
        }
    }
}
