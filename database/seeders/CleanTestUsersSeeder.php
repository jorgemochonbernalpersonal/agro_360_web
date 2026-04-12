<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Elimina usuarios de prueba/demo de producción junto con todos sus datos.
 *
 * Algunas tablas (campaigns.viticulturist_id, agricultural_activities.viticulturist_id,
 * wine_subproducts.created_by) no tienen ON DELETE CASCADE, lo que impide el borrado
 * directo. Se deshabilitan las FK checks solo durante el delete del usuario para que
 * MySQL no bloquee — los registros huérfanos son irrelevantes para cuentas de prueba.
 *
 * Tablas con ON DELETE CASCADE (se limpian automáticamente al borrar el usuario):
 *   user_profiles, subscriptions, payments, clients, user_taxes, invoice_groups,
 *   invoices, invoicing_settings, support_tickets, digital_signatures, containers,
 *   agricultural_activity_audit_logs, warehouses, product_stocks, container_rooms,
 *   crews (viticulturist_id), winery_viticulturist (viticulturist_id+winery_id),
 *   supervisor_winery (supervisor_id), etc.
 *
 * Tablas con ON DELETE SET NULL (quedan con null, sin datos huérfanos problemáticos):
 *   harvest_stocks, invoice_audit_logs, plot_audit_logs, plot_planting_audit_logs,
 *   winery_viticulturist.assigned_by, crew_members.assigned_by,
 *   organizations.owner_user_id
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
            // Limpiar tablas sin FK formal
            DB::table('sessions')->whereIn('user_id', $ids)->delete();
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->whereIn('tokenable_id', $ids)
                ->delete();

            // Deshabilitar FK checks para poder borrar el usuario aunque haya tablas
            // con RESTRICT (campaigns.viticulturist_id, agricultural_activities.viticulturist_id,
            // wine_subproducts.created_by). Los registros huérfanos de cuentas de prueba
            // son inocuos y nunca aparecerán en ningún listado activo.
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $deleted = DB::table('users')->whereIn('id', $ids)->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->info("Eliminados {$deleted} usuario(s) y todos sus datos asociados (cascade).");
            $this->command->warn('Nota: tablas con RESTRICT (campaigns, agricultural_activities, wine_subproducts) pueden tener registros huérfanos de estas cuentas de prueba.');
        });
    }
}
