<?php

namespace App\Services\Tools;

use App\Models\AppSetting;
use App\Models\PhytoRegistryProduct;
use App\Models\PhytoRegistryUse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Importa el Registro Oficial de Productos Fitosanitarios del MAPA.
 *
 * Fuente: exportación JSON pública de https://servicio.mapa.gob.es/regfiweb
 * (botón "Productos autorizados" → JSON). La reutilización está amparada por la
 * Ley 37/2007 y el RD 1495/2011: hay que citar la fuente y la fecha de actualización.
 *
 * La respuesta viene con doble codificación: un string JSON que contiene un objeto
 * {Id, Tipo, Contenido, Fecha} cuyo Contenido es a su vez un string JSON {Productos: [...]}.
 */
class PhytoRegistryImporter
{
    public const EXPORT_URL = 'https://servicio.mapa.gob.es/regfiweb/Exportaciones/ExportJsonProductosAutorizados';

    /** Si llegan menos productos, la exportación viene rota: no se toca la BD. */
    public const MIN_EXPECTED_PRODUCTS = 500;

    public function download(): string
    {
        $client = Http::timeout(300)->retry(3, 5000, throw: false)->asForm();
        if (app()->environment('local')) {
            $client = $client->withoutVerifying();
        }

        $response = $client->post(self::EXPORT_URL, [
            'tipoExportacion' => 'ProductosAutorizados',
            'dataDto' => '{}',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("El MAPA respondió HTTP {$response->status()} al descargar el registro.");
        }

        return $response->body();
    }

    /**
     * @return array{products: int, uses: int, source_date: string|null}
     */
    public function import(string $rawExport, int $minExpectedProducts = self::MIN_EXPECTED_PRODUCTS): array
    {
        [$products, $sourceDate] = $this->decode($rawExport);

        if (count($products) < $minExpectedProducts) {
            throw new RuntimeException('La exportación del MAPA solo trae '.count($products).' productos: se aborta para no vaciar el catálogo.');
        }

        $useCount = 0;

        DB::transaction(function () use ($products, &$useCount) {
            $seenIds = [];

            foreach ($products as $item) {
                $data = $item['DATOSPRODUCTO'] ?? null;
                if (! is_array($data) || empty($data['IdProducto'])) {
                    continue;
                }

                $product = PhytoRegistryProduct::updateOrCreate(
                    ['mapa_id' => (int) $data['IdProducto']],
                    [
                        'registration_number' => trim((string) ($data['Num_Registro'] ?? '')),
                        'name' => trim((string) ($data['Nombre'] ?? '')),
                        'holder' => $this->nullable($data['Titular'] ?? null),
                        'formulation' => $this->nullable($data['Formulado'] ?? null, 500),
                        'status' => trim((string) ($data['Estado'] ?? '')),
                        'expires_on' => $this->date($data['Fecha_Caducidad'] ?? null),
                        'conditions' => $this->nullable($data['Condicionamiento'] ?? null, null),
                        'composition' => array_map(fn (array $c) => [
                            'substance' => trim((string) ($c['Nombre Sustancia'] ?? '')),
                            'concentration' => isset($c['Concentracion']) ? (float) $c['Concentracion'] : null,
                            'unit' => $this->nullable($c['DescripcionNota'] ?? null),
                        ], $item['COMPOSICION'] ?? []),
                        'active_substances' => $this->nullable(implode(' + ', array_map(
                            fn (array $c) => trim((string) ($c['Nombre Sustancia'] ?? '')),
                            $item['COMPOSICION'] ?? []
                        )), 500),
                        'other_names' => array_values(array_filter(array_map(
                            fn (array $n) => trim((string) ($n['Nombre_Origen'] ?? $n['Nombre'] ?? '')),
                            array_merge($item['OTRASDENOMINACIONES'] ?? [], $item['OTROSNOMBRES'] ?? [])
                        ))),
                    ]
                );
                $seenIds[] = $product->id;

                // Los usos se reemplazan enteros: el MAPA no da un id estable por uso.
                PhytoRegistryUse::where('product_id', $product->id)->delete();

                $rows = array_map(fn (array $u) => $this->useRow($product->id, $u), $item['USOS'] ?? []);
                foreach (array_chunk($rows, 500) as $chunk) {
                    PhytoRegistryUse::insert($chunk);
                }
                $useCount += count($rows);
            }

            // Productos que ya no están en la exportación (retirados del todo).
            PhytoRegistryProduct::whereNotIn('id', $seenIds)->delete();
        });

        if ($sourceDate) {
            AppSetting::set(PhytoRegistryProduct::SOURCE_DATE_SETTING, $sourceDate);
        }

        return ['products' => count($products), 'uses' => $useCount, 'source_date' => $sourceDate];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    private function decode(string $raw): array
    {
        $outer = json_decode($raw, true);
        if (is_string($outer)) {
            $outer = json_decode($outer, true);
        }

        $content = is_array($outer) ? ($outer['Contenido'] ?? null) : null;
        $content = is_string($content) ? json_decode($content, true) : $content;

        if (! is_array($content) || ! is_array($content['Productos'] ?? null)) {
            throw new RuntimeException('Formato inesperado en la exportación del MAPA (falta Contenido.Productos).');
        }

        return [$content['Productos'], $this->date($outer['Fecha'] ?? null)];
    }

    /** @return array<string, mixed> */
    private function useRow(int $productId, array $u): array
    {
        $safety = trim((string) ($u['Plazo Seguridad'] ?? ''));

        return [
            'product_id' => $productId,
            'crop_code' => trim((string) ($u['CodigoCultivo'] ?? '')),
            'crop' => mb_substr(trim((string) ($u['Cultivo'] ?? '')), 0, 150),
            'agent_code' => $this->nullable($u['CodigoAgente'] ?? null, 20),
            'agent' => mb_substr(trim((string) ($u['Agente'] ?? '')), 0, 200),
            'dose_min' => isset($u['Dosis_Min']) ? (float) $u['Dosis_Min'] : null,
            'dose_max' => isset($u['Dosis_Max']) ? (float) $u['Dosis_Max'] : null,
            'dose_unit' => $this->nullable($u['Unidad Medida dosis'] ?? null, 60),
            'safety_period' => $this->nullable($safety, 60),
            'safety_days' => PhytoRegistryUse::parseSafetyDays($safety),
            'broth_volume' => $this->nullable($u['Volumen Caldo'] ?? null, 500),
            'applications' => $this->nullable($u['Aplicaciones'] ?? null, 60),
            'application_interval' => $this->nullable($u['IntervaloAplicaciones'] ?? null, 60),
            'bbch' => $this->nullable($u['Bbch'] ?? null, 500),
            'specific_conditions' => $this->nullable($u['CondicionamientoEspecifico'] ?? null, null),
            'user_type' => $this->nullable($u['TipoUsuario'] ?? null, 60),
            'application_method' => $this->nullable($u['MetodoAplicacion'] ?? null, 100),
        ];
    }

    private function nullable(mixed $value, ?int $max = 255): ?string
    {
        // El MAPA usa \r como salto de línea en los textos largos.
        $value = trim(str_replace("\r", "\n", (string) $value));
        if ($value === '') {
            return null;
        }

        return $max ? mb_substr($value, 0, $max) : $value;
    }

    private function date(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse(str_replace('/', '-', $value))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
