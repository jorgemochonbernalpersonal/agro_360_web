<?php

namespace App\Http\Requests\Api\Viticulturist;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de los FormRequests del API de viticultor.
 *
 * Centraliza la autorización por rol (antes `abort_unless($user->hasViticulturistAccess(), 403)`
 * en cada acción). Devolver false produce un 403, idéntico al comportamiento previo.
 */
class ViticulturistApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasViticulturistAccess();
    }

    public function rules(): array
    {
        return [];
    }
}
