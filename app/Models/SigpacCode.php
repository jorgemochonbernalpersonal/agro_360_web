<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SigpacCode extends Model
{
    protected $table = 'sigpac_code';

    protected $fillable = [
        'code_autonomous_community',
        'code_province',
        'code_municipality',
        'code_aggregate',
        'code_zone',
        'code_polygon',
        'code_plot',
        'code_enclosure',
        'code',
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
     * Construir código SIGPAC completo desde campos individuales
     *
     * Formato: CA(2) - Provincia(2) - Municipio(3) - Agregado(1) - Zona(1) - Polígono(2) - Parcela(5) - Recinto(3)
     * Total: 19 dígitos
     *
     * @param array $fields Campos individuales
     * @return string Código completo sin guiones
     */
    public static function buildCodeFromFields(array $fields): string
    {
        $autonomousCommunity = str_pad($fields['code_autonomous_community'] ?? '', 2, '0', STR_PAD_LEFT);
        $province = str_pad($fields['code_province'] ?? '', 2, '0', STR_PAD_LEFT);
        $municipality = str_pad($fields['code_municipality'] ?? '', 3, '0', STR_PAD_LEFT);
        $aggregate = str_pad($fields['code_aggregate'] ?? '0', 1, '0', STR_PAD_LEFT);
        $zone = str_pad($fields['code_zone'] ?? '0', 1, '0', STR_PAD_LEFT);
        $polygon = str_pad($fields['code_polygon'] ?? '', 2, '0', STR_PAD_LEFT);
        $plot = str_pad($fields['code_plot'] ?? '', 5, '0', STR_PAD_LEFT);
        $enclosure = str_pad($fields['code_enclosure'] ?? '', 3, '0', STR_PAD_LEFT);

        return $autonomousCommunity . $province . $municipality . $aggregate . $zone . $polygon . $plot . $enclosure;
    }

    /**
     * Parsear y validar código SIGPAC completo
     *
     * Formato esperado: 13-28-079-0-0-12-00045-003 o 1328079001200045003
     * Estructura: CA(2) - Provincia(2) - Municipio(3) - Agregado(1) - Zona(1) - Polígono(2) - Parcela(5) - Recinto(3)
     * Total: 19 dígitos
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
                'El código SIGPAC solo puede contener números y guiones. Formato esperado: 13-28-079-0-0-12-00045-003'
            );
        }

        // Validar longitud exacta (19 dígitos)
        if (strlen($cleanCode) !== 19) {
            throw new \InvalidArgumentException(
                'El código SIGPAC debe tener exactamente 19 dígitos. Recibido: ' . strlen($cleanCode) . ' dígitos. '
                . 'Formato esperado: CA(2) - Provincia(2) - Municipio(3) - Agregado(1) - Zona(1) - Polígono(2) - Parcela(5) - Recinto(3)'
            );
        }

        // Extraer cada parte según la estructura SIGPAC
        $autonomousCommunity = substr($cleanCode, 0, 2);  // Posiciones 0-1 (2 dígitos)
        $province = substr($cleanCode, 2, 2);  // Posiciones 2-3 (2 dígitos)
        $municipality = substr($cleanCode, 4, 3);  // Posiciones 4-6 (3 dígitos)
        $aggregate = substr($cleanCode, 7, 1);  // Posición 7 (1 dígito)
        $zone = substr($cleanCode, 8, 1);  // Posición 8 (1 dígito)
        $polygon = substr($cleanCode, 9, 2);  // Posiciones 9-10 (2 dígitos)
        $plot = substr($cleanCode, 11, 5);  // Posiciones 11-15 (5 dígitos)
        $enclosure = substr($cleanCode, 16, 3);  // Posiciones 16-18 (3 dígitos)

        return [
            'code' => $cleanCode,  // Guardar sin guiones
            'code_autonomous_community' => $autonomousCommunity,
            'code_province' => $province,
            'code_municipality' => $municipality,
            'code_aggregate' => $aggregate,
            'code_zone' => $zone,
            'code_polygon' => $polygon,
            'code_plot' => $plot,
            'code_enclosure' => $enclosure,
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
        if (!$this->code || strlen($this->code) !== 19) {
            return $this->code ?? 'N/A';
        }

        // Formatear: 13-28-079-0-0-12-00045-003
        return sprintf(
            '%s-%s-%s-%s-%s-%s-%s-%s',
            substr($this->code, 0, 2),  // CA
            substr($this->code, 2, 2),  // Provincia
            substr($this->code, 4, 3),  // Municipio
            substr($this->code, 7, 1),  // Agregado
            substr($this->code, 8, 1),  // Zona
            substr($this->code, 9, 2),  // Polígono
            substr($this->code, 11, 5),  // Parcela
            substr($this->code, 16, 3)  // Recinto
        );
    }
}
