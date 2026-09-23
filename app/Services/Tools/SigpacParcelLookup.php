<?php

namespace App\Services\Tools;

use App\Models\Province;
use App\Models\SigpacUse;
use App\Services\SigpacGeometryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consulta pública de un recinto SIGPAC (visor de la herramienta gratuita).
 * Devuelve los datos del recinto normalizados para mostrarlos, sin guardar nada.
 */
class SigpacParcelLookup
{
    public const FIELDS = ['province', 'municipality', 'aggregate', 'zone', 'polygon', 'parcel', 'enclosure'];

    public const STATUS_OK = 'ok';

    public const STATUS_NOT_FOUND = 'not_found';

    public const STATUS_UNAVAILABLE = 'unavailable';

    /** Los recintos cambian poco: un día de caché evita martillear la API pública. */
    private const CACHE_TTL = 86400;

    /**
     * Separa una referencia pegada ("13:87:0:0:10:20:1", "13-87-0-0-10-20-1", etc.).
     *
     * @return array<string, int>|null
     */
    public function parseReference(string $reference): ?array
    {
        $parts = preg_split('/\D+/', trim($reference), -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false || count($parts) !== count(self::FIELDS)) {
            return null;
        }

        return array_combine(self::FIELDS, array_map('intval', $parts));
    }

    /**
     * @param array<string, int> $codes claves de self::FIELDS
     *
     * @return array{status: string, data: array<string, mixed>|null}
     */
    public function lookup(array $codes): array
    {
        $url = vsprintf(SigpacGeometryService::RECINFO_URL, array_map(
            fn (string $field) => (int) $codes[$field],
            self::FIELDS
        ));

        // Solo se cachean respuestas válidas (incluido "no existe"); los fallos no.
        $record = Cache::get($url);
        if ($record === null) {
            try {
                $client = Http::timeout(10);
                if (app()->environment('local')) {
                    $client = $client->withoutVerifying();
                }
                $response = $client->get($url);
            } catch (\Throwable $e) {
                Log::warning('Visor SIGPAC: API no disponible', ['error' => $e->getMessage()]);

                return ['status' => self::STATUS_UNAVAILABLE, 'data' => null];
            }

            $json = $response->successful() ? $response->json() : null;
            if (! is_array($json)) {
                return ['status' => self::STATUS_UNAVAILABLE, 'data' => null];
            }

            $record = $json[0] ?? [];
            Cache::put($url, $record, self::CACHE_TTL);
        }

        if ($record === []) {
            return ['status' => self::STATUS_NOT_FOUND, 'data' => null];
        }

        return ['status' => self::STATUS_OK, 'data' => $this->normalize($record)];
    }

    /**
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    private function normalize(array $record): array
    {
        $useCode = $record['uso_sigpac'] ?? null;
        $provinceCode = isset($record['provincia']) ? str_pad((string) $record['provincia'], 2, '0', STR_PAD_LEFT) : null;

        return [
            'reference' => implode(':', array_map(
                fn (string $key) => $record[$key] ?? '0',
                ['provincia', 'municipio', 'agregado', 'zona', 'poligono', 'parcela', 'recinto']
            )),
            'province' => $provinceCode ? Province::where('code', $provinceCode)->value('name') : null,
            'area_ha' => isset($record['superficie']) ? (float) $record['superficie'] : null,
            'use_code' => $useCode,
            'use_label' => $useCode ? SigpacUse::where('code', $useCode)->value('description') : null,
            'slope_percent' => isset($record['pendiente_media']) ? (float) $record['pendiente_media'] : null,
            'irrigation_coef' => isset($record['coef_regadio']) ? (float) $record['coef_regadio'] : null,
            'eligibility' => isset($record['admisibilidad']) ? (float) $record['admisibilidad'] : null,
            'altitude_m' => isset($record['altitud']) ? (int) $record['altitud'] : null,
            'incidences' => array_values(array_filter(array_map('trim', explode(',', (string) ($record['incidencias'] ?? ''))))),
            'wkt' => is_string($record['wkt'] ?? null) && preg_match(SigpacGeometryService::WKT_PATTERN, $record['wkt'])
                ? $record['wkt']
                : null,
        ];
    }
}
