<?php

namespace App\Enums;

enum TenureRegime: string
{
    /**
     * Get the label for the tenure regime
     */
    public function label(): string
    {
        return match ($this) {
            self::OWNERSHIP => __('Propiedad'),
            self::LEASE => __('Arrendamiento'),
            self::SHARECROPPING => __('Aparcería'),
            self::GRANT => __('Cesión'),
            self::USUFRUCT => __('Usufructo'),
        };
    }

    /**
     * Get all options for select inputs
     */
    public static function options(): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
    case OWNERSHIP = 'propiedad';
    case LEASE = 'arrendamiento';
    case SHARECROPPING = 'aparceria';
    case GRANT = 'cesion';
    case USUFRUCT = 'usufructo';
}
