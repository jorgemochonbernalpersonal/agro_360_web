<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\User;
use App\Notifications\ContainerMaintenanceAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckContainerMaintenanceCommand extends Command
{
    protected $signature = 'containers:check-maintenance {--days=7 : Días de antelación para alertas próximas}';

    protected $description = 'Envía alertas por email de mantenimientos de contenedores vencidos o próximos';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $today = now()->toDateString();
        $limit = now()->addDays($days)->toDateString();

        // Obtener todos los user_ids que tienen contenedores activos
        $userIds = Container::where('archived', false)
            ->distinct()
            ->pluck('user_id');

        $sent = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (! $user || ! $user->email) {
                continue;
            }

            // Mantenimientos programados vencidos (scheduled_date < hoy, sin completar)
            $overdue = ContainerMaintenance::whereHas('container', fn ($q) => $q->where('user_id', $userId)->where('archived', false)
            )
                ->where('status', 'scheduled')
                ->where('scheduled_date', '<', $today)
                ->with('container')
                ->orderBy('scheduled_date')
                ->get();

            // Contenedores con next_maintenance_date en los próximos N días
            $upcoming = Container::where('user_id', $userId)
                ->where('archived', false)
                ->whereBetween('next_maintenance_date', [$today, $limit])
                ->orderBy('next_maintenance_date')
                ->get();

            if ($overdue->isEmpty() && $upcoming->isEmpty()) {
                continue;
            }

            try {
                $user->notify(new ContainerMaintenanceAlertNotification($overdue, $upcoming));
                $sent++;

                $this->line("  ✓ Notificación enviada a {$user->email} "
                    ."({$overdue->count()} vencidos, {$upcoming->count()} próximos)");

            } catch (\Exception $e) {
                Log::error('CheckContainerMaintenance: error al notificar usuario '.$userId, [
                    'error' => $e->getMessage(),
                ]);
                $this->error("  ✗ Error notificando a {$user->email}: ".$e->getMessage());
            }
        }

        $this->info("Proceso completado. {$sent} notificaciones enviadas.");

        return self::SUCCESS;
    }
}
