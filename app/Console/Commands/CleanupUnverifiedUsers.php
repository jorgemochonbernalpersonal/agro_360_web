<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\SupervisorWinery;
use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use App\Models\ViticulturistHierarchy;
use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\Plot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-unverified {--hours=24 : Número de horas antes de eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina usuarios no verificados después de 24 horas (o el tiempo especificado)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoffDate = Carbon::now()->subHours($hours);

        $this->info("Buscando usuarios no verificados creados antes de: {$cutoffDate->format('Y-m-d H:i:s')}");

        $unverifiedUsers = User::whereNull('email_verified_at')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        if ($unverifiedUsers->isEmpty()) {
            $this->info('No se encontraron usuarios no verificados para eliminar.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$unverifiedUsers->count()} usuario(s) no verificado(s) para eliminar.");

        $deletedCount = 0;

        DB::transaction(function () use ($unverifiedUsers, &$deletedCount) {
            foreach ($unverifiedUsers as $user) {
                $this->line("Eliminando usuario: {$user->email} (ID: {$user->id}, Rol: {$user->role})");

                SupervisorWinery::where('supervisor_id', $user->id)->delete();
                SupervisorViticulturist::where('supervisor_id', $user->id)->delete();

                SupervisorWinery::where('winery_id', $user->id)->delete();
                WineryViticulturist::where('winery_id', $user->id)->delete();
                Crew::where('winery_id', $user->id)->delete();
                Plot::where('winery_id', $user->id)->delete();

                SupervisorViticulturist::where('viticulturist_id', $user->id)->delete();
                WineryViticulturist::where('viticulturist_id', $user->id)->delete();
                ViticulturistHierarchy::where('parent_viticulturist_id', $user->id)->delete();
                ViticulturistHierarchy::where('child_viticulturist_id', $user->id)->delete();
                Crew::where('viticulturist_id', $user->id)->delete();
                CrewMember::where('viticulturist_id', $user->id)->delete();
                Plot::where('viticulturist_id', $user->id)->delete();

                $user->delete();
                $deletedCount++;
            }
        });

        $this->info("✅ Se eliminaron {$deletedCount} usuario(s) no verificado(s) y todas sus relaciones.");

        return Command::SUCCESS;
    }
}
