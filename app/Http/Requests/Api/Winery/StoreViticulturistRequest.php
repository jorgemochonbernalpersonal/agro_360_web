<?php

namespace App\Http\Requests\Api\Winery;

class StoreViticulturistRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('El nombre es obligatorio.'),
            'email.unique' => __('Ya existe un usuario con este email.'),
        ];
    }
}
