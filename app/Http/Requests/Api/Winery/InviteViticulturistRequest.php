<?php

namespace App\Http\Requests\Api\Winery;

class InviteViticulturistRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Introduce el email del viticultor.'),
            'email.email' => __('El email no es válido.'),
        ];
    }
}
