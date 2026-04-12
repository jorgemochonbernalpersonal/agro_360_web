<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Elimina usuarios de prueba/demo de producción junto con todos sus datos.
 *
 * La mayoría de tablas tienen onDelete('cascade'), así que borrar el usuario
 * limpia automáticamente: plots, harvests, containers, invoices, profiles, etc.
 *
 * Tablas sin cascade (set null): harvest_stocks, invoice_audit_logs,
 * plot_audit_logs, plot_planting_audit_logs, winery_viticulturist.assigned_by,
 * crew_members.assigned_by, organizations.owner_user_id — se dejan con null.
 *
 * Se borran también sessions y personal_access_tokens (Sanctum) explícitamente
 * porque no tienen FK formal en todos los entornos.
 */
class CleanTestUsersSeeder extends Seeder
{
    private const USER_IDS = [6, 7, 8, 10, 335, 336, 337];

    public function run(): void
    {
        $ids = self::USER_IDS;

        $found = DB::table('users')
            ->whereIn('id', $ids)
            ->pluck('email', 'id');

        if ($found->isEmpty()) {
            $this->command->warn('No se encontró ninguno de los usuarios especificados. Nada que limpiar.');
            return;
        }

        $this->command->info('Usuarios a eliminar:');
        foreach ($found as $id => $email) {
            $this->command->line("  [{$id}] {$email}");
        }

        $missing = array_diff($ids, $found->keys()->toArray());
        if ($missing) {
            $this->command->warn('No encontrados (ya borrados o nunca existieron): ' . implode(', ', $missing));
        }

        if (!$this->command->confirm('¿Confirmas el borrado permanente de estos usuarios y TODOS sus datos?', false)) {
            $this->command->info('Cancelado.');
            return;
        }

        DB::transaction(function () use ($ids) {
            // Limpieza explícita de tablas sin FK formal
            DB::table('sessions')->whereIn('user_id', $ids)->delete();
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $ids)
                ->delete();

            // El cascade en users borra el resto automáticamente
            $deleted = DB::table('users')->whereIn('id', $ids)->delete();

            $this->command->info("Eliminados {$deleted} usuario(s) y todos sus datos asociados.");
        });
    }
}
