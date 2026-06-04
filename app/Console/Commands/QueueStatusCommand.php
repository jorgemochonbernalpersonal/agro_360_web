<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueStatusCommand extends Command
{
    protected $signature = 'queue:status';

    protected $description = 'Muestra el estado de la cola (jobs pendientes y fallidos)';

    public function handle(): int
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();

        $this->info('📋 Estado de la cola:');
        $this->table(
            ['Cola', 'Cantidad'],
            [
                ['Jobs pendientes', $pending],
                ['Jobs fallidos', $failed],
            ]
        );

        if ($pending > 0) {
            $this->warn("Hay {$pending} job(s) esperando. Si no disminuyen, el cron podría no estar procesando la cola.");
        }

        if ($failed > 0) {
            $this->error("Hay {$failed} job(s) fallidos. Revisa: php artisan queue:failed");
        }

        return Command::SUCCESS;
    }
}
