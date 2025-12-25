<?php

namespace App\Enums;

enum TenureRegime: string
{
    case OWNERSHIP = 'propiedad';
    case LEASE = 'arrendamiento';
    case SHARECROPPING = 'aparceria';
    case GRANT = 'cesion';
    case USUFRUCT = 'usufructo';

    /**
     * Get the label for the tenure regime
     */
    public function label(): string
    {
        return match($this) {
            self::OWNERSHIP => 'Propiedad',
            self::LEASE => 'Arrendamiento',
            self::SHARECROPPING => 'Aparcería',
            self::GRANT => 'Cesión',
            self::USUFRUCT => 'Usufructo',
        };
    }

    /**
     * Get all options for select inputs
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
