<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Console\Command;

class FixOrphanedWineryLinks extends Command
{
    protected $signature = 'fix:orphaned-winery-links {--dry-run : Solo mostrar, no borrar}';
    protected $description = 'Elimina WineryViticulturist huerfanos: winery_id NULL (bug registro) o apuntando a user inexistente';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $totalCleaned = 0;

        // 1. Links con winery_id = NULL (bug del registro público que creaba SOURCE_SELF sin bodega)
        $nullWinery = WineryViticulturist::whereNull('winery_id')
            ->with('viticulturist:id,email,name')
            ->get();

        if ($nullWinery->isNotEmpty()) {
            $this->warn("Links con winery_id NULL: {$nullWinery->count()}");
            foreach ($nullWinery as $link) {
                $this->line("  ID:{$link->id} | {$link->viticulturist?->email} | source:{$link->source}");
            }

            if (!$dryRun) {
                $this->cleanup($nullWinery);
                $totalCleaned += $nullWinery->count();
            }
        }

        // 2. Links con winery_id apuntando a user que ya no existe
        $orphaned = WineryViticulturist::whereNotNull('winery_id')
            ->whereNotIn('winery_id', User::pluck('id'))
            ->with('viticulturist:id,email,name')
            ->get();

        if ($orphaned->isNotEmpty()) {
            $this->warn("Links con winery_id inexistente: {$orphaned->count()}");
            foreach ($orphaned as $link) {
                $this->line("  ID:{$link->id} | {$link->viticulturist?->email} | winery_id:{$link->winery_id} (NO EXISTE) | source:{$link->source}");
            }

            if (!$dryRun) {
                $this->cleanup($orphaned);
                $totalCleaned += $orphaned->count();
            }
        }

        if ($nullWinery->isEmpty() && $orphaned->isEmpty()) {
            $this->info('Todo limpio. No hay links huerfanos.');
            return 0;
        }

        if ($dryRun) {
            $this->info('Modo --dry-run. No se ha borrado nada.');
        } else {
            $this->info("Limpiados {$totalCleaned} link(s). Cache invalidado.");
        }

        return 0;
    }

    private function cleanup($links): void
    {
        $links->each(function (WineryViticulturist $link) {
            $viticulturistId = $link->viticulturist_id;
            $link->delete();

            $user = User::find($viticulturistId);
            $user?->clearAttributeCache();
        });
    }
}
