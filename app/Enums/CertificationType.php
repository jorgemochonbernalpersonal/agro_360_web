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
            self::ECOLOGICO => 'Ecológico',
            self::DO => 'Denominación de Origen (DO)',
            self::DOCA => 'Denominación de Origen Calificada (DOCa)',
            self::IGP => 'Indicación Geográfica Protegida (IGP)',
            self::VINO_PAGO => 'Vino de Pago',
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
