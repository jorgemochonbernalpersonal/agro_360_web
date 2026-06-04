<?php

namespace App\Enums;

enum PlantingRightType: string
{
    /**
     * Get the label for the planting right type
     */
    public function label(): string
    {
        return match ($this) {
            self::NEW_PLANTING => __('Nueva Plantación'),
            self::REPLANTING => __('Replantación'),
            self::CONVERSION => __('Conversión'),
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
    case NEW_PLANTING = 'nueva';
    case REPLANTING = 'replantacion';
    case CONVERSION = 'conversion';
}
