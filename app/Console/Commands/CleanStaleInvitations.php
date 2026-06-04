<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanStaleInvitations extends Command
{
    protected $signature = 'viticulturists:clean-stale-invitations
                            {--dry-run : List stale ghosts without making changes}
                            {--days=30 : Days after token expiry to consider stale}';

    protected $description = 'Limpia tokens de invitación caducados de viticultores ghost';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        // Ghosts cuya invitación expiró hace más de $days días
        $stale = User::where('role', 'viticulturist')
            ->where('can_login', false)
            ->whereNotNull('invitation_expires_at')
            ->where('invitation_expires_at', '<', $cutoff)
            ->get(['id', 'name', 'email', 'invitation_sent_at', 'invitation_expires_at']);

        if ($stale->isEmpty()) {
            $this->info('No hay invitaciones caducadas que limpiar.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nombre', 'Email', 'Invitación enviada', 'Expiró'],
            $stale->map(fn ($u) => [
                $u->id,
                $u->name,
                $u->email,
                $u->invitation_sent_at?->format('Y-m-d H:i'),
                $u->invitation_expires_at?->format('Y-m-d H:i'),
            ])
        );

        if ($dryRun) {
            $this->warn("Modo dry-run: {$stale->count()} invitaciones caducadas encontradas. Sin cambios.");

            return self::SUCCESS;
        }

        if (! $this->confirm("¿Limpiar tokens de {$stale->count()} viticultor(es)? (Los usuarios NO se eliminan)")) {
            $this->info('Operación cancelada.');

            return self::SUCCESS;
        }

        $ids = $stale->pluck('id');

        User::whereIn('id', $ids)->update([
            'invitation_token' => null,
            'invitation_sent_at' => null,
            'invitation_expires_at' => null,
        ]);

        Log::info('CleanStaleInvitations: tokens limpiados', [
            'count' => $stale->count(),
            'user_ids' => $ids->toArray(),
        ]);

        $this->info("✓ Tokens limpiados de {$stale->count()} viticultor(es).");

        return self::SUCCESS;
    }
}
