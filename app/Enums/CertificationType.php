<?php

namespace App\Enums;

enum CertificationType: string
{
    case ECOLOGICO = 'ecologico';
    case DO = 'do';
    case DOCA = 'doca';
    case IGP = 'igp';
    case VINO_PAGO = 'vino_pago';

    public function label(): string
    {
        return match($this) {
            self::ECOLOGICO => __('Ecológico'),
            self::DO => __('Denominación de Origen (DO)'),
            self::DOCA => __('Denominación de Origen Calificada (DOCa)'),
            self::IGP => __('Indicación Geográfica Protegida (IGP)'),
            self::VINO_PAGO => __('Vino de Pago'),
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::ECOLOGICO => '🌿',
            self::DO => '🏆',
            self::DOCA => '👑',
            self::IGP => '📍',
            self::VINO_PAGO => '🍷',
        };
    }
}
