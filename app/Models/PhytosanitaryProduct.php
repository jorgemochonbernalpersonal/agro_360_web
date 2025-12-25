<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhytosanitaryProduct extends Model
{
    protected $fillable = [
        'name',
        'active_ingredient',
        'registration_number',
        'registration_expiry_date',
        'registration_status',
        'manufacturer',
        'type',
        'toxicity_class',
        'withdrawal_period_days',
        'description',
        'active',
    ];

    protected $casts = [
        'registration_expiry_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Validación de número de registro MAPA
     * Formato esperado: ES-00000000 (ES seguido de 8 dígitos)
     */
    public static function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'regex:/^ES-\d{8}$/'],
            'withdrawal_period_days' => ['required', 'integer', 'min:0'],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:herbicida,fungicida,insecticida,acaricida,nematicida,otro'],
            'toxicity_class' => ['nullable', 'string', 'in:I,II,III,IV'],
            'registration_expiry_date' => ['nullable', 'date', 'after:today'],
            'registration_status' => ['nullable', 'string', 'in:active,expired,revoked'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Verificar si el registro está activo y vigente
     */
    public function isRegistrationValid(): bool
    {
        if ($this->registration_status !== 'active') {
            return false;
        }

        if ($this->registration_expiry_date && $this->registration_expiry_date < now()) {
            return false;
        }

        return true;
    }

    /**
     * Tratamientos que usan este producto
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(PhytosanitaryTreatment::class, 'product_id');
    }
}
