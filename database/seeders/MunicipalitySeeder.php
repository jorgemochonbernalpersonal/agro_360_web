<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = Province::all()->keyBy('code');

        // Intentar importar desde archivo local primero
        $csvPath = database_path('seeders/data/municipalities.csv');
        $jsonPath = database_path('seeders/data/municipalities.json');
        $excelPath = database_path('seeders/data/municipalities.xlsx');
        $excelPath2 = database_path('seeders/data/municipalities.xls');

        if (file_exists($csvPath)) {
            $this->command->info('✅ Archivo CSV encontrado: ' . $csvPath);
            $this->command->info('Importando desde archivo CSV...');
            $this->importFromCsv($csvPath, $provinces);
        } elseif (file_exists($jsonPath)) {
            $this->command->info('Importando desde archivo JSON...');
            $this->importFromJson($jsonPath, $provinces);
        } elseif (file_exists($excelPath) || file_exists($excelPath2)) {
            $this->command->warn('Archivo Excel detectado. Por favor, conviértelo a CSV:');
            $this->command->info('  1. Abre el archivo en Excel');
            $this->command->info('  2. Archivo > Guardar como > CSV (delimitado por comas)');
            $this->command->info('  3. Guarda como: municipalities.csv en la carpeta data/');
            $this->command->info('  4. O ejecuta: php artisan municipalities:import-excel ruta/al/archivo.csv');
            $this->downloadAndImport($provinces);
        } else {
            $this->command->warn('No se encontró archivo de datos. Usando datos de ejemplo...');
            $this->command->info('Para importar todos los municipios:');
            $this->command->info('  1. Convierte tu Excel a CSV (Archivo > Guardar como > CSV)');
            $this->command->info('  2. Colócalo en: ' . $csvPath);
            $this->command->info('  3. O ejecuta: php artisan municipalities:import-excel ruta/al/archivo.csv');
            $this->downloadAndImport($provinces);
        }
    }

    /**
     * Descarga e importa municipios desde una fuente externa
     */
    private function downloadAndImport($provinces): void
    {
        // URL alternativa: puedes usar una API o archivo público
        // Por ahora, usaremos datos básicos de ejemplo
        $this->command->info('Usando datos de ejemplo. Para datos completos, coloca un archivo CSV o JSON en:');
        $this->command->info('  - ' . database_path('seeders/data/municipalities.csv'));
        $this->command->info('  - ' . database_path('seeders/data/municipalities.json'));

        // Insertar algunos municipios de ejemplo por provincia
        $this->insertSampleMunicipalities($provinces);
    }

    /**
     * Inserta municipios de ejemplo (solo si no hay archivo CSV)
     * NOTA: Estos son solo datos de ejemplo (capitales de provincia).
     * Los datos reales se importan desde el archivo CSV.
     */
    private function insertSampleMunicipalities($provinces): void
    {
        // Datos de ejemplo: capitales de provincia (solo para testing)
        // Los datos reales están en: database/seeders/data/municipalities.csv
        $sampleMunicipalities = [
            ['code' => '04013', 'name' => 'Almería', 'province_code' => '04'],  // Capital de Almería
            ['code' => '11012', 'name' => 'Cádiz', 'province_code' => '11'],  // Capital de Cádiz
            ['code' => '14021', 'name' => 'Córdoba', 'province_code' => '14'],  // Capital de Córdoba
            ['code' => '18087', 'name' => 'Granada', 'province_code' => '18'],  // Capital de Granada
            ['code' => '21041', 'name' => 'Huelva', 'province_code' => '21'],  // Capital de Huelva
            ['code' => '23050', 'name' => 'Jaén', 'province_code' => '23'],  // Capital de Jaén
            ['code' => '29067', 'name' => 'Málaga', 'province_code' => '29'],  // Capital de Málaga
            ['code' => '41091', 'name' => 'Sevilla', 'province_code' => '41'],  // Capital de Sevilla
            ['code' => '22125', 'name' => 'Huesca', 'province_code' => '22'],  // Capital de Huesca
            ['code' => '44231', 'name' => 'Teruel', 'province_code' => '44'],  // Capital de Teruel
            ['code' => '50297', 'name' => 'Zaragoza', 'province_code' => '50'],  // Capital de Zaragoza
            ['code' => '33016', 'name' => 'Oviedo', 'province_code' => '33'],  // Capital de Asturias
            ['code' => '07040', 'name' => 'Palma', 'province_code' => '07'],  // Capital de Baleares
            ['code' => '35016', 'name' => 'Las Palmas de Gran Canaria', 'province_code' => '35'],  // Capital de Las Palmas
            ['code' => '38025', 'name' => 'Santa Cruz de Tenerife', 'province_code' => '38'],  // Capital de Santa Cruz
            ['code' => '39075', 'name' => 'Santander', 'province_code' => '39'],  // Capital de Cantabria
            ['code' => '05019', 'name' => 'Ávila', 'province_code' => '05'],  // Capital de Ávila
            ['code' => '09059', 'name' => 'Burgos', 'province_code' => '09'],  // Capital de Burgos
            ['code' => '24089', 'name' => 'León', 'province_code' => '24'],  // Capital de León
            ['code' => '34120', 'name' => 'Palencia', 'province_code' => '34'],  // Capital de Palencia
            ['code' => '37274', 'name' => 'Salamanca', 'province_code' => '37'],  // Capital de Salamanca
            ['code' => '40194', 'name' => 'Segovia', 'province_code' => '40'],  // Capital de Segovia
            ['code' => '42173', 'name' => 'Soria', 'province_code' => '42'],  // Capital de Soria
            ['code' => '47186', 'name' => 'Valladolid', 'province_code' => '47'],  // Capital de Valladolid
            ['code' => '49275', 'name' => 'Zamora', 'province_code' => '49'],  // Capital de Zamora
            ['code' => '02003', 'name' => 'Albacete', 'province_code' => '02'],  // Capital de Albacete
            ['code' => '13034', 'name' => 'Ciudad Real', 'province_code' => '13'],  // Capital de Ciudad Real
            ['code' => '16078', 'name' => 'Cuenca', 'province_code' => '16'],  // Capital de Cuenca
            ['code' => '19130', 'name' => 'Guadalajara', 'province_code' => '19'],  // Capital de Guadalajara
            ['code' => '45168', 'name' => 'Toledo', 'province_code' => '45'],  // Capital de Toledo
            ['code' => '08019', 'name' => 'Barcelona', 'province_code' => '08'],  // Capital de Barcelona
            ['code' => '17079', 'name' => 'Girona', 'province_code' => '17'],  // Capital de Girona
            ['code' => '25120', 'name' => 'Lleida', 'province_code' => '25'],  // Capital de Lleida
            ['code' => '43148', 'name' => 'Tarragona', 'province_code' => '43'],  // Capital de Tarragona
            ['code' => '03014', 'name' => 'Alicante', 'province_code' => '03'],  // Capital de Alicante
            ['code' => '12040', 'name' => 'Castellón de la Plana', 'province_code' => '12'],  // Capital de Castellón
            ['code' => '46250', 'name' => 'Valencia', 'province_code' => '46'],  // Capital de Valencia
            ['code' => '06015', 'name' => 'Badajoz', 'province_code' => '06'],  // Capital de Badajoz
            ['code' => '10037', 'name' => 'Cáceres', 'province_code' => '10'],  // Capital de Cáceres
            ['code' => '15030', 'name' => 'A Coruña', 'province_code' => '15'],  // Capital de A Coruña
            ['code' => '27028', 'name' => 'Lugo', 'province_code' => '27'],  // Capital de Lugo
            ['code' => '32054', 'name' => 'Ourense', 'province_code' => '32'],  // Capital de Ourense
            ['code' => '36038', 'name' => 'Pontevedra', 'province_code' => '36'],  // Capital de Pontevedra
            ['code' => '28079', 'name' => 'Madrid', 'province_code' => '28'],  // Capital de Madrid
            ['code' => '30030', 'name' => 'Murcia', 'province_code' => '30'],  // Capital de Murcia
            ['code' => '31201', 'name' => 'Pamplona', 'province_code' => '31'],  // Capital de Navarra
            ['code' => '01001', 'name' => 'Alegría-Dulantzi', 'province_code' => '01'],  // Municipio de Álava
            ['code' => '48020', 'name' => 'Bilbao', 'province_code' => '48'],  // Capital de Vizcaya
            ['code' => '20045', 'name' => 'Donostia-San Sebastián', 'province_code' => '20'],  // Capital de Guipúzcoa
            ['code' => '26089', 'name' => 'Logroño', 'province_code' => '26'],  // Capital de La Rioja
            ['code' => '51001', 'name' => 'Ceuta', 'province_code' => '51'],  // Ceuta
            ['code' => '52001', 'name' => 'Melilla', 'province_code' => '52'],  // Melilla
        ];

        $this->command->info('Insertando ' . count($sampleMunicipalities) . ' municipios de ejemplo (capitales de provincia)...');
        $this->processMunicipalities($sampleMunicipalities, $provinces);
    }

    /**
     * Importa desde archivo CSV
     * Si el CSV solo tiene 'code' y 'name', extrae automáticamente el province_code
     * de los primeros 2 dígitos del código del municipio
     */
    private function importFromCsv(string $csvPath, $provinces): void
    {
        $municipalities = [];

        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Leer cabeceras
            $headers = fgetcsv($handle);

            // Normalizar headers (minúsculas, sin espacios, sin acentos)
            $headers = array_map(function ($h) {
                $normalized = strtolower(trim($h));
                // Normalizar caracteres especiales (catalán/valenciano)
                $normalized = str_replace(['ó', 'í', 'é', 'à', 'è', 'ò', 'ú', 'ü', 'ç', 'ñ'], ['o', 'i', 'e', 'a', 'e', 'o', 'u', 'u', 'c', 'n'], $normalized);
                return $normalized;
            }, $headers);

            // Buscar índices de columnas (acepta múltiples variantes)
            $codeIndex = $this->findColumnIndex($headers, ['code', 'codi', 'codigo', 'cod', 'código']);
            $nameIndex = $this->findColumnIndex($headers, ['name', 'nombre', 'nom', 'municipio', 'municipi']);
            // "Codi Província" es opcional, se extrae del code si no existe
            $provinceCodeIndex = $this->findColumnIndex($headers, ['province_code', 'provincia', 'province', 'codi provincia', 'codigo provincia']);

            if ($codeIndex === false || $nameIndex === false) {
                $this->command->error('El CSV debe tener al menos las columnas: code, name');
                fclose($handle);
                return;
            }

            $this->command->info('Leyendo archivo CSV...');

            while (($data = fgetcsv($handle)) !== false) {
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
                        // Ejemplo: 04013 -> 04, 11012 -> 11, 10014 -> 10
                        $provinceCode = substr($code, 0, 2);
                        // Normalizar a 2 dígitos
                        $provinceCode = str_pad($provinceCode, 2, '0', STR_PAD_LEFT);
                    }

                    // Ignorar códigos especiales del INE (98, 99) que no son provincias reales
                    // Ejemplo: 999998 "No consta", 999999 "Altres/Diversos"
                    if (in_array($provinceCode, ['98', '99']) || str_starts_with($code, '99')) {
                        continue;  // Saltar estos casos especiales
                    }

                    if (!empty($code) && !empty($name) && !empty($provinceCode) && strlen($code) >= 5) {
                        $municipalities[] = [
                            'code' => $code,
                            'name' => $name,
                            'province_code' => $provinceCode,
                        ];
                    }
                }
            }
            fclose($handle);
        }

        $this->command->info('Importando ' . count($municipalities) . ' municipios desde CSV...');
        $this->processMunicipalities($municipalities, $provinces);
    }

    /**
     * Importa desde archivo JSON
     */
    private function importFromJson(string $jsonPath, $provinces): void
    {
        $json = file_get_contents($jsonPath);
        $municipalities = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Error al parsear JSON: ' . json_last_error_msg());
            return;
        }

        $this->command->info('Importando ' . count($municipalities) . ' municipios desde JSON...');
        $this->processMunicipalities($municipalities, $provinces);
    }

    /**
     * Procesa e inserta los municipios
     */
    private function processMunicipalities(array $municipalities, $provinces): void
    {
        $bar = $this->command->getOutput()->createProgressBar(count($municipalities));
        $bar->start();

        $inserted = 0;
        $errors = 0;

        $errorDetails = [];
        $missingProvinces = [];

        foreach (array_chunk($municipalities, 500) as $chunk) {
            $dataToInsert = [];

            foreach ($chunk as $municipality) {
                $provinceCode = $municipality['province_code'] ?? substr($municipality['code'], 0, 2);
                $province = $provinces->get($provinceCode);

                if (!$province) {
                    $errors++;
                    if (!isset($missingProvinces[$provinceCode])) {
                        $missingProvinces[$provinceCode] = 0;
                    }
                    $missingProvinces[$provinceCode]++;
                    if (count($errorDetails) < 10) {
                        $errorDetails[] = "Provincia '{$provinceCode}' no encontrada para: {$municipality['name']} (code: {$municipality['code']})";
                    }
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
                    $this->command->error("\nError al insertar lote: " . $e->getMessage());
                    $errors += count($dataToInsert);
                }
            }

            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Municipios procesados: {$inserted} insertados, {$errors} errores.");

        // Mostrar detalles de errores
        if ($errors > 0) {
            if (count($missingProvinces) > 0) {
                $this->command->warn("\n⚠️  Provincias no encontradas en la base de datos:");
                foreach ($missingProvinces as $code => $count) {
                    $this->command->line("  - Código '{$code}': {$count} municipios afectados");
                }
                $this->command->info("\n💡 Solución: Ejecuta primero el ProvinceSeeder para crear todas las provincias.");
            }

            if (count($errorDetails) > 0) {
                $this->command->warn("\n📋 Primeros errores (máx. 10):");
                foreach ($errorDetails as $error) {
                    $this->command->line("  - {$error}");
                }
            }
        }
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
