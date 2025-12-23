<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OfficialReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'period_start',
        'period_end',
        'signature_hash',
        'signed_at',
        'signed_ip',
        'signature_metadata',
        'verification_code',
        'report_metadata',
        'pdf_path',
        'pdf_size',
        'pdf_filename',
        'is_valid',
        'invalidation_reason',
        'invalidated_at',
        'invalidated_by',
        'processing_status',
        'processing_error',
        'completed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'signed_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_valid' => 'boolean',
        'signature_metadata' => 'array',
        'report_metadata' => 'array',
    ];

    /**
     * ========================================
     * RELACIONES
     * ========================================
     */

    /**
     * Usuario que generó el informe
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario que invalidó el informe (si aplica)
     */
    public function invalidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invalidated_by');
    }

    /**
     * ========================================
     * MÉTODOS DE FIRMA ELECTRÓNICA
     * ========================================
     */

    /**
     * Generar código de verificación único (para QR)
     */
    public static function generateVerificationCode(): string
    {
        do {
            // Código de 32 caracteres alfanuméricos
            $code = strtoupper(Str::random(32));
        } while (self::where('verification_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generar hash temporal único para uso durante la creación del informe
     * Este hash será reemplazado por el hash real después de generar el PDF
     */
    public static function generateTemporaryHash(): string
    {
        do {
            // Generar un hash SHA-256 temporal único
            // Usamos timestamp + random bytes para garantizar unicidad
            $tempData = now()->timestamp . Str::random(32) . uniqid('', true);
            $tempHash = hash('sha256', $tempData);
        } while (self::where('signature_hash', $tempHash)->exists());
        
        return $tempHash;
    }

    /**
     * Generar hash de firma basado en contenido del informe
     * 
     * @param array $data Datos del informe a firmar
     * @return array ['hash' => string, 'nonce' => string, 'version' => string]
     */
    public static function generateSignatureHash(array $data): array
    {
        // Añadir nonce único para prevenir replay attacks
        if (!isset($data['nonce'])) {
            $data['nonce'] = bin2hex(random_bytes(16));
        }
        
        // Añadir versión de firma para compatibilidad futura
        if (!isset($data['signature_version'])) {
            $data['signature_version'] = config('reports.signature_version', '1.0');
        }
        
        // Ordenar arrays recursivamente para garantizar hash consistente
        // Esto previene falsos positivos de "modificado" por orden de arrays
        $data = self::sortArraysRecursively($data);
        
        // Crear string único del contenido
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Añadir APP_KEY para mayor seguridad
        $secretKey = config('app.key');
        
        // Generar hash SHA-256
        $hash = hash('sha256', $content . $secretKey);
        
        return [
            'hash' => $hash,
            'nonce' => $data['nonce'],
            'version' => $data['signature_version'],
        ];
    }

    /**
     * Ordenar arrays recursivamente para hash consistente
     * 
     * @param mixed $data
     * @return mixed
     */
    protected static function sortArraysRecursively($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        // Ordenar por claves
        ksort($data);
        
        // Procesar cada valor
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Si es array numérico (lista), ordenar valores
                if (array_keys($value) === range(0, count($value) - 1)) {
                    sort($value);
                }
                // Recursión para arrays anidados
                $data[$key] = self::sortArraysRecursively($value);
            }
        }
        
        return $data;
    }

    /**
     * Verificar si un hash coincide con los datos
     * 
     * @param array $data Datos a verificar
     * @param string $hash Hash a comparar
     * @return bool
     */
    public static function verifySignatureHash(array $data, string $hash): bool
    {
        $result = self::generateSignatureHash($data);
        return hash_equals($result['hash'], $hash);
    }

    /**
     * ========================================
     * MÉTODOS DE VERIFICACIÓN
     * ========================================
     */

    /**
     * Obtener URL de verificación pública
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('reports.verify', ['code' => $this->verification_code]);
    }

    /**
     * Incrementar contador de verificaciones
     */
    public function incrementVerificationCount(): void
    {
        $this->increment('verification_count');
        $this->update(['last_verified_at' => now()]);
    }

    /**
     * Verificar si el informe es válido
     */
    public function isValid(): bool
    {
        return $this->is_valid && is_null($this->invalidated_at);
    }

    /**
     * Verificar si el informe puede ser invalidado
     * Por seguridad, solo se pueden invalidar informes recientes
     * El número de días se configura en config/reports.php
     * 
     * @return bool
     */
    public function canBeInvalidated(): bool
    {
        $maxDaysToInvalidate = config('reports.max_days_to_invalidate', 30);
        $daysSinceSigned = $this->signed_at->diffInDays(now());
        
        return $daysSinceSigned <= $maxDaysToInvalidate;
    }

    /**
     * Obtener días restantes para poder invalidar
     * 
     * @return int|null Retorna null si ya no se puede invalidar
     */
    public function getDaysRemainingToInvalidate(): ?int
    {
        if (!$this->canBeInvalidated()) {
            return null;
        }
        
        $maxDaysToInvalidate = config('reports.max_days_to_invalidate', 30);
        $daysSinceSigned = $this->signed_at->diffInDays(now());
        
        return max(0, $maxDaysToInvalidate - $daysSinceSigned);
    }

    /**
     * Invalidar informe
     * 
     * @param string $reason Motivo de invalidación
     * @param int|null $userId ID del usuario que invalida
     * @throws \Exception Si el informe no puede ser invalidado
     */
    public function invalidate(string $reason, ?int $userId = null): void
    {
        // Verificar si se puede invalidar
        if (!$this->canBeInvalidated()) {
            $maxDays = config('reports.max_days_to_invalidate', 30);
            throw new \Exception("Este informe no puede ser invalidado. Solo se pueden invalidar informes con menos de {$maxDays} días desde su firma.");
        }

        $this->update([
            'is_valid' => false,
            'invalidation_reason' => $reason,
            'invalidated_at' => now(),
            'invalidated_by' => $userId ?? auth()->id(),
        ]);

        // Log de auditoría
        \Log::warning('Informe oficial invalidado', [
            'report_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'reason' => $reason,
            'days_since_signed' => $this->signed_at->diffInDays(now()),
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Revalidar informe previamente invalidado
     */
    public function revalidate(): void
    {
        $this->update([
            'is_valid' => true,
            'invalidation_reason' => null,
            'invalidated_at' => null,
            'invalidated_by' => null,
        ]);

        // Log de auditoría
        \Log::info('Informe oficial revalidado', [
            'report_id' => $this->id,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }

    /**
     * ========================================
     * ACCESSORS & HELPERS
     * ========================================
     */

    /**
     * Obtener nombre legible del tipo de informe
     */
    public function getReportTypeNameAttribute(): string
    {
        $types = config('reports.types', []);
        return $types[$this->report_type]['name'] ?? 'Informe';
    }

    /**
     * Obtener icono según tipo de informe
     */
    public function getReportIconAttribute(): string
    {
        $types = config('reports.types', []);
        return $types[$this->report_type]['icon'] ?? '📄';
    }

    /**
     * Obtener descripción del tipo de informe
     */
    public function getReportTypeDescriptionAttribute(): string
    {
        $types = config('reports.types', []);
        return $types[$this->report_type]['description'] ?? '';
    }

    /**
     * Obtener tipos de informes disponibles
     * 
     * @param bool $onlyImplemented Filtrar solo tipos implementados
     * @return array
     */
    public static function getAvailableTypes(bool $onlyImplemented = true): array
    {
        $types = config('reports.types', []);
        
        if ($onlyImplemented) {
            return array_filter($types, fn($type) => $type['implemented'] ?? false);
        }
        
        return $types;
    }

    /**
     * Obtener tamaño del PDF formateado
     */
    public function getFormattedPdfSizeAttribute(): string
    {
        if (!$this->pdf_size) {
            return 'N/A';
        }

        $bytes = $this->pdf_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' B';
    }

    /**
     * Verificar si el PDF existe
     */
    public function pdfExists(): bool
    {
        if (!$this->pdf_path) {
            return false;
        }

        // Si el path es relativo (usando Storage), verificar con Storage
        if (!str_starts_with($this->pdf_path, storage_path())) {
            return \Storage::disk('local')->exists($this->pdf_path);
        }

        // Si es path absoluto, verificar con file_exists
        return file_exists($this->pdf_path);
    }

    /**
     * Obtener hash corto para mostrar (primeros 16 caracteres)
     */
    public function getShortHashAttribute(): string
    {
        return substr($this->signature_hash, 0, 16) . '...';
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    /**
     * Filtrar por usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtrar por tipo de informe
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Filtrar solo informes válidos
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true)
                     ->whereNull('invalidated_at');
    }

    /**
     * Filtrar solo informes invalidados
     */
    public function scopeInvalid($query)
    {
        return $query->where('is_valid', false)
                     ->orWhereNotNull('invalidated_at');
    }

    /**
     * Filtrar por rango de fechas del periodo
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('period_start', [$startDate, $endDate])
              ->orWhereBetween('period_end', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('period_start', '<=', $startDate)
                     ->where('period_end', '>=', $endDate);
              });
        });
    }

    /**
     * Ordenar por más reciente
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
