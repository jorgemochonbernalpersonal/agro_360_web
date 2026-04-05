<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeAbandonedGhosts extends Command
{
    protected $signature = 'viticulturists:purge-abandoned-ghosts
                            {--dry-run : List abandoned ghosts without deleting}
                            {--days=90 : Days since creation to consider abandoned}';

    protected $description = 'Elimina viticultores ghost que nunca fueron invitados ni activados tras un periodo de inactividad';

    /**
     * Tablas con FK a users sin CASCADE que podrían causar orphans o FK errors.
     */
    private const BLOCKING_TABLES = [
        'agricultural_activities' => 'viticulturist_id',
        'campaigns'              => 'viticulturist_id',
    ];

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        // Ghosts que: nunca se activaron, sin token de invitación activo,
        // sin invitación enviada, y creados hace más de $days días
        $abandoned = User::where('role', 'viticulturist')
            ->where('can_login', false)
            ->whereNull('invitation_token')
            ->whereNull('invitation_sent_at')
            ->where('created_at', '<', $cutoff)
            ->get(['id', 'name', 'email', 'dni', 'created_at']);

        if ($abandoned->isEmpty()) {
            $this->info('No hay viticultores ghost abandonados.');
            return self::SUCCESS;
        }

        // Separar ghosts seguros de los que tienen datos asociados
        $ids       = $abandoned->pluck('id');
        $blockedIds = collect();

        foreach (self::BLOCKING_TABLES as $table => $column) {
            $blocked = DB::table($table)->whereIn($column, $ids)->pluck($column)->unique();
            $blockedIds = $blockedIds->merge($blocked);
        }

        $blockedIds = $blockedIds->unique();
        $safeIds    = $ids->diff($blockedIds);
        $safeGhosts = $abandoned->whereIn('id', $safeIds);
        $blockedGhosts = $abandoned->whereIn('id', $blockedIds);

        // Mostrar ghosts seguros para eliminar
        if ($safeGhosts->isNotEmpty()) {
            $this->info("── Ghosts sin datos asociados ({$safeGhosts->count()}) ──");
            $this->table(
                ['ID', 'Nombre', 'Email', 'DNI', 'Creado'],
                $safeGhosts->map(fn ($u) => [
                    $u->id,
                    $u->name,
                    str_starts_with($u->email, 'viticultores.') ? '(placeholder)' : $u->email,
                    $u->dni ?? '—',
                    $u->created_at->format('Y-m-d H:i'),
                ])
            );
        }

        // Mostrar ghosts con datos (no se pueden eliminar automáticamente)
        if ($blockedGhosts->isNotEmpty()) {
            $this->warn("── Ghosts CON datos asociados ({$blockedGhosts->count()}) — requieren revisión manual ──");
            $this->table(
                ['ID', 'Nombre', 'Email', 'DNI', 'Creado'],
                $blockedGhosts->map(fn ($u) => [
                    $u->id,
                    $u->name,
                    str_starts_with($u->email, 'viticultores.') ? '(placeholder)' : $u->email,
                    $u->dni ?? '—',
                    $u->created_at->format('Y-m-d H:i'),
                ])
            );
        }

        if ($safeGhosts->isEmpty()) {
            $this->warn('Todos los ghosts tienen datos asociados. No se puede eliminar ninguno automáticamente.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("Modo dry-run: {$safeGhosts->count()} eliminable(s), {$blockedGhosts->count()} con datos. Sin cambios.");
            return self::SUCCESS;
        }

        if (!$this->confirm("¿Eliminar {$safeGhosts->count()} viticultor(es) ghost sin datos? (Se eliminan también sus relaciones winery_viticulturist via CASCADE)")) {
            $this->info('Operación cancelada.');
            return self::SUCCESS;
        }

        User::whereIn('id', $safeIds)->delete();

        Log::info('PurgeAbandonedGhosts: usuarios eliminados', [
            'count'      => $safeGhosts->count(),
            'user_ids'   => $safeIds->values()->toArray(),
            'blocked_ids' => $blockedIds->values()->toArray(),
        ]);

        $this->info("✓ {$safeGhosts->count()} viticultor(es) ghost eliminados.");

        if ($blockedGhosts->isNotEmpty()) {
            $this->warn("⚠ {$blockedGhosts->count()} ghost(s) con datos en agricultural_activities/campaigns no fueron eliminados.");
        }

        return self::SUCCESS;
    }
}
