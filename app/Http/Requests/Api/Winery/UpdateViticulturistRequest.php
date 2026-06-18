<?php

namespace App\Http\Requests\Api\Winery;

use Illuminate\Validation\Rule;

class UpdateViticulturistRequest extends WineryApiRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
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
