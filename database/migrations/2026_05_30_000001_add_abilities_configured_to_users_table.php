<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Añade users.abilities_configured.
 *
 * Distingue "bodega sin configurar por la DO" (acceso total, retrocompatible) de
 * "bodega configurada" (el set de abilities es vinculante; vacío = ningún módulo).
 * Antes, un set vacío significaba siempre "acceso total", por lo que la DO no podía
 * restringir una bodega a cero módulos y quitar la última ability re-otorgaba todo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('abilities_configured')
                ->default(false)
                ->after('can_login');
        });

        // Preservar el comportamiento actual: cualquier bodega/producer que ya
        // tenga abilities asignadas se considera "configurada" (la DO ya definió su
        // set, incluido el caso de tenerlas todas tras el backfill). Las que no
        // tienen ninguna mantienen acceso total (flag=false) por retrocompatibilidad.
        DB::table('users')
            ->whereIn('role', ['winery', 'producer'])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('user_abilities')
                    ->whereColumn('user_abilities.user_id', 'users.id');
            })
            ->update(['abilities_configured' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('abilities_configured');
        });
    }
};
