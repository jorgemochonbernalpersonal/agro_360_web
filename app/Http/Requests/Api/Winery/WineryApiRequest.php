<?php

namespace App\Http\Requests\Api\Winery;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de los FormRequests del API de bodega.
 *
 * Centraliza la autorización por rol (antes repetida como
 * `abort_unless($user->hasWineryAccess(), 403)` en cada acción del controlador).
 * Devolver false aquí produce un 403, idéntico al comportamiento anterior.
 *
 * La propiedad/ownership de cada factura (scoping a user_id) y las reglas de
 * máquina de estados (422) permanecen en el controlador, porque dependen del
 * modelo ya cargado y deben preservar sus códigos de respuesta (404/422).
 */
class WineryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasWineryAccess();
    }

    public function rules(): array
    {
        return [];
    }
}
