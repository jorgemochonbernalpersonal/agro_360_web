<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SigpacCode extends Model
{
    protected $table = 'sigpac_code';

    protected $fillable = [
        'code_polygon',
        'code_plot',
        'code_enclosure',
        'code_aggregate',
        'code_province',
        'code_zone',
        'code',
        'code_municipality',
    ];

    /**
     * Parcelas que usan este código SIGPAC (relación antigua via plot_sigpac_code)
     */
    public function plotsOld(): BelongsToMany
    {
        return $this->belongsToMany(Plot::class, 'plot_sigpac_code', 'sigpac_code_id', 'plot_id');
    }

    /**
     * Parcelas que usan este código SIGPAC (nueva estructura via multiple_plot_sigpac)
     */
    public function plots(): BelongsToMany
    {
        return $this
            ->belongsToMany(Plot::class, 'multipart_plot_sigpac', 'sigpac_code_id', 'plot_id')
            ->withPivot('plot_geometry_id')
            ->withTimestamps();
    }

    /**
     * Relaciones múltiples plot-sigpac
     */
    public function multiplePlotSigpacs(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'sigpac_code_id');
    }

    /**
     * Coordenadas multiparte (estructura antigua)
     */
    public function multipartCoordinates(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'sigpac_code_id');
    }

    /**
     * Obtener código completo formateado
     */
    public function getFullCodeAttribute(): string
    {
        if ($this->code) {
            return $this->code;
        }

        return trim(
            ($this->code_polygon ?? '')
            . ($this->code_plot ?? '')
            . ($this->code_enclosure ?? '')
            . ($this->code_aggregate ?? '')
        ) ?: 'N/A';
    }

    /**
     * Parsear y validar código SIGPAC completo
     *
     * Formato esperado: 28-005-1-0032-015-002 o 2800510032015002
     * Estructura: Provincia(2) - Municipio(3) - Zona(1) - Polígono(4) - Parcela(3) - Recinto(3)
     *
     * @param string $code Código completo con o sin guiones
     * @return array Array con los campos parseados
     * @throws \InvalidArgumentException Si el formato no es válido
     */
    public static function parseSigpacCode(string $code): array
    {
        // Limpiar el código: eliminar espacios y guiones
        $cleanCode = preg_replace('/[-\s]/', '', $code);

        // Validar que solo contenga dígitos
        if (!preg_match('/^\d+$/', $cleanCode)) {
            throw new \InvalidArgumentException(
                'El código SIGPAC solo puede contener números y guiones. Formato esperado: 28-005-1-0032-015-002'
            );
        }

        // Validar longitud exacta (16 dígitos)
        if (strlen($cleanCode) !== 16) {
            throw new \InvalidArgumentException(
                'El código SIGPAC debe tener exactamente 16 dígitos. Recibido: ' . strlen($cleanCode) . ' dígitos. '
                . 'Formato esperado: Provincia(2) - Municipio(3) - Zona(1) - Polígono(4) - Parcela(3) - Recinto(3)'
            );
        }

        // Extraer cada parte según la estructura SIGPAC
        $province = substr($cleanCode, 0, 2);  // Posiciones 0-1 (2 dígitos)
        $municipality = substr($cleanCode, 2, 3);  // Posiciones 2-4 (3 dígitos)
        $zone = substr($cleanCode, 5, 1);  // Posición 5 (1 dígito)
        $polygon = substr($cleanCode, 6, 4);  // Posiciones 6-9 (4 dígitos)
        $plot = substr($cleanCode, 10, 3);  // Posiciones 10-12 (3 dígitos)
        $enclosure = substr($cleanCode, 13, 3);  // Posiciones 13-15 (3 dígitos)

        return [
            'code' => $cleanCode,  // Guardar sin guiones
            'code_province' => $province,
            'code_municipality' => $municipality,
            'code_zone' => $zone,
            'code_polygon' => $polygon,
            'code_plot' => $plot,
            'code_enclosure' => $enclosure,
            'code_aggregate' => null,  // Opcional, se puede añadir después si es necesario
        ];
    }

    /**
     * Validar formato de código SIGPAC sin parsearlo
     */
    public static function validateSigpacFormat(string $code): bool
    {
        try {
            self::parseSigpacCode($code);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Formatear código SIGPAC con guiones para mostrar
     */
    public function getFormattedCodeAttribute(): string
    {
        if (!$this->code || strlen($this->code) !== 16) {
            return $this->code ?? 'N/A';
        }

        // Formatear: 28-005-1-0032-015-002
        return sprintf(
            '%s-%s-%s-%s-%s-%s',
            substr($this->code, 0, 2),  // Provincia
            substr($this->code, 2, 3),  // Municipio
            substr($this->code, 5, 1),  // Zona
            substr($this->code, 6, 4),  // Polígono
            substr($this->code, 10, 3),  // Parcela
            substr($this->code, 13, 3)  // Recinto
        );
    }
}
