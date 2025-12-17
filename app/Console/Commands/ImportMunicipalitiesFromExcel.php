<?php

namespace App\Console\Commands;

use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMunicipalitiesFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'municipalities:import-excel {file : Ruta al archivo Excel o CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa municipios desde un archivo Excel o CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'xlsx' || $extension === 'xls') {
            $this->info('Archivo Excel detectado. Por favor, conviértelo a CSV primero.');
            $this->info('En Excel: Archivo > Guardar como > CSV (delimitado por comas)');
            $this->info('O usa este comando después de convertir: php artisan municipalities:import-csv ' . str_replace(['.xlsx', '.xls'], '.csv', $filePath));
            return 1;
        }

        if ($extension === 'csv') {
            $this->importFromCsv($filePath);
            return 0;
        }

        $this->error('Formato de archivo no soportado. Use CSV o Excel (convertido a CSV).');
        return 1;
    }

    /**
     * Importa desde archivo CSV
     */
    private function importFromCsv(string $csvPath): void
    {
        $provinces = Province::all()->keyBy('code');
        $municipalities = [];

        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Leer cabeceras
            $headers = fgetcsv($handle);

            // Normalizar headers (minúsculas, sin espacios, sin acentos)
            $headers = array_map(function ($h) {
                $normalized = strtolower(trim($h));
                // Normalizar caracteres especiales
                $normalized = str_replace(['ó', 'í', 'é', 'à', 'è', 'ò', 'ú', 'ü', 'ç'], ['o', 'i', 'e', 'a', 'e', 'o', 'u', 'u', 'c'], $normalized);
                return $normalized;
            }, $headers);

            // Buscar índices de columnas (acepta múltiples variantes)
            $codeIndex = $this->findColumnIndex($headers, ['code', 'codi', 'codigo', 'cod', 'código']);
            $nameIndex = $this->findColumnIndex($headers, ['name', 'nombre', 'nom', 'municipio', 'municipi']);
            $provinceCodeIndex = $this->findColumnIndex($headers, ['province_code', 'provincia', 'province', 'codi provincia', 'codigo provincia']);

            if ($codeIndex === false || $nameIndex === false) {
                $this->error('El CSV debe tener al menos las columnas: code, name');
                fclose($handle);
                return;
            }

            $this->info('Leyendo archivo CSV...');
            $lineNumber = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if (count($data) > max($codeIndex, $nameIndex)) {
                    $code = trim($data[$codeIndex]);
                    $name = trim($data[$nameIndex]);

                    // Prioridad 1: Usar la columna "Codi Província" si existe y tiene valor
                    if ($provinceCodeIndex !== false && isset($data[$provinceCodeIndex]) && !empty(trim($data[$provinceCodeIndex]))) {
                        $provinceCode = trim($data[$provinceCodeIndex]);
                        // Normalizar a 2 dígitos (agregar cero a la izquierda si es necesario)
                        // Ejemplo: "5" -> "05", "6" -> "06"
                        $provinceCode = str_pad($provinceCode, 2, '0', STR_PAD_LEFT);
                    } else {
                        // Prioridad 2: Extraer de los primeros 2 dígitos del código del municipio
                        // Ejemplo: 04013 -> 04, 11012 -> 11
                        $provinceCode = substr($code, 0, 2);
                        $provinceCode = str_pad($provinceCode, 2, '0', STR_PAD_LEFT);
                    }

                    if (!empty($code) && !empty($name) && !empty($provinceCode)) {
                        $municipalities[] = [
                            'code' => $code,
                            'name' => $name,
                            'province_code' => $provinceCode,
                        ];
                    } else {
                        $this->warn("Línea {$lineNumber} ignorada: datos incompletos");
                    }
                }
            }
            fclose($handle);
        }

        $this->info('Procesando ' . count($municipalities) . ' municipios...');
        $this->processMunicipalities($municipalities, $provinces);
    }

    /**
     * Procesa e inserta los municipios
     */
    private function processMunicipalities(array $municipalities, $provinces): void
    {
        $bar = $this->output->createProgressBar(count($municipalities));
        $bar->start();

        $inserted = 0;
        $errors = 0;
        $skipped = 0;

        foreach (array_chunk($municipalities, 500) as $chunk) {
            $dataToInsert = [];

            foreach ($chunk as $municipality) {
                $provinceCode = $municipality['province_code'];
                $province = $provinces->get($provinceCode);

                if (!$province) {
                    $this->warn("\nProvincia no encontrada: {$provinceCode} para municipio: {$municipality['name']}");
                    $skipped++;
                    continue;
                }

                $dataToInsert[] = [
                    'code' => $municipality['code'],
                    'name' => $municipality['name'],
                    'province_id' => $province->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($dataToInsert)) {
                try {
                    DB::table('municipalities')->insertOrIgnore($dataToInsert);
                    $inserted += count($dataToInsert);
                } catch (\Exception $e) {
                    $this->error("\nError: " . $e->getMessage());
                    $errors++;
                }
            }

            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Municipios procesados: {$inserted} insertados, {$skipped} omitidos, {$errors} errores.");
    }

    /**
     * Busca el índice de una columna probando múltiples variantes de nombre
     */
    private function findColumnIndex(array $headers, array $variants): int|false
    {
        foreach ($variants as $variant) {
            $index = array_search($variant, $headers);
            if ($index !== false) {
                return $index;
            }
        }
        return false;
    }
}
