<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Uso autorizado (cultivo + agente) de un producto del registro oficial del MAPA.
 *
 * @property int         $product_id
 * @property string      $crop_code
 * @property string      $crop
 * @property string|null $agent_code
 * @property string      $agent
 * @property float|null  $dose_min
 * @property float|null  $dose_max
 * @property string|null $dose_unit
 * @property string|null $safety_period
 * @property int|null    $safety_days
 * @property string|null $broth_volume
 * @property string|null $applications
 * @property string|null $application_interval
 * @property string|null $bbch
 * @property string|null $specific_conditions
 * @property string|null $user_type
 * @property string|null $application_method
 */
class PhytoRegistryUse extends Model
{
    /** Códigos de cultivo de la vid en el registro (vid, vinificación, mesa). */
    public const VINE_CROP_CODE_PREFIX = '01010207';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'crop_code',
        'crop',
        'agent_code',
        'agent',
        'dose_min',
        'dose_max',
        'dose_unit',
        'safety_period',
        'safety_days',
        'broth_volume',
        'applications',
        'application_interval',
        'bbch',
        'specific_conditions',
        'user_type',
        'application_method',
    ];

    protected $casts = [
        'dose_min' => 'float',
        'dose_max' => 'float',
        'safety_days' => 'integer',
    ];

    /** @return BelongsTo<PhytoRegistryProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PhytoRegistryProduct::class, 'product_id');
    }

    public function doseLabel(): string
    {
        $fmt = fn (float $n) => rtrim(rtrim(number_format($n, 4, ',', '.'), '0'), ',');

        // El MAPA pone 0 en el extremo que no aplica (dosis única): no es un rango "0,3–0".
        $min = (float) $this->dose_min;
        $max = (float) $this->dose_max;

        if ($min <= 0 && $max <= 0) {
            return $this->dose_unit ?: '—';
        }

        $range = $min > 0 && $max > 0 && $min != $max
            ? $fmt(min($min, $max)).'–'.$fmt(max($min, $max))
            : $fmt($max > 0 ? $max : $min);

        return trim($range.' '.$this->dose_unit);
    }

    public function safetyLabel(): string
    {
        if ($this->safety_days !== null) {
            return $this->safety_days === 1 ? '1 día' : $this->safety_days.' días';
        }

        $raw = trim((string) $this->safety_period);

        if (self::isNotApplicable($raw)) {
            return 'No procede';
        }

        // Textos como "10 (AIRE LIBRE) 3 (INVERNADERO)": se muestran tal cual, sin reinterpretar.
        return in_array($raw, ['', '-', '*'], true) ? 'Ver condiciones' : $raw;
    }

    /** "NO PROCEDE", "NP", "N.P.", "NA", "N0 PROCEDE", "NO RELEVANTE"… */
    public static function isNotApplicable(string $raw): bool
    {
        return (bool) preg_match('/^(N[O0]\s+PROCEDE|N\.?P\.?|N\.?A\.?|NO\s+RELEVANTE)$/iu', trim($raw));
    }

    /** Días del plazo de seguridad solo cuando el registro da un número limpio. */
    public static function parseSafetyDays(string $raw): ?int
    {
        return preg_match('/^\d{1,3}$/', trim($raw)) ? (int) trim($raw) : null;
    }
}
