<?php

namespace App\Console\Commands;

use App\Services\Tools\PhytoRegistryImporter;
use Illuminate\Console\Command;

/**
 * Descarga e importa el Registro Oficial de Productos Fitosanitarios del MAPA
 * (el MAPA lo actualiza los viernes).
 */
class ImportPhytoRegistryCommand extends Command
{
    protected $signature = 'phyto:import-registry
        {--file= : Importar desde un JSON ya descargado en lugar de llamar al MAPA}';

    protected $description = 'Importa el registro oficial de productos fitosanitarios del MAPA';

    public function handle(PhytoRegistryImporter $importer): int
    {
        // La exportación pesa ~90 MB y decodificarla necesita ~250 MB.
        ini_set('memory_limit', '768M');

        try {
            $file = $this->option('file');
            if ($file) {
                if (! is_readable($file)) {
                    $this->error("No se puede leer {$file}");

                    return self::FAILURE;
                }
                $raw = (string) file_get_contents($file);
            } else {
                $this->info('Descargando el registro del MAPA…');
                $raw = $importer->download();
            }

            $result = $importer->import($raw);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->info("Importados {$result['products']} productos y {$result['uses']} usos (datos del MAPA a {$result['source_date']}).");

        return self::SUCCESS;
    }
}
