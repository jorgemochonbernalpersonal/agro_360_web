<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('viticulturist');  // viticulturist, winery, supervisor, admin
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('email');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * ⚠️ ADVERTENCIA: Este método elimina la tabla 'users' y todos los usuarios.
     * Solo se ejecuta en entornos de desarrollo/test. En producción, este método
     * está protegido y lanzará una excepción.
     */
    public function down(): void
    {
        // Protección: No permitir borrar usuarios en producción
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'No se puede ejecutar migrate:rollback en producción. '
                . 'Este comando eliminaría todos los usuarios. '
                . 'Si necesitas revertir cambios, crea una nueva migración.'
            );
        }

        // Verificar que no hay usuarios antes de borrar (doble protección)
        $userCount = DB::table('users')->count();
        if ($userCount > 0) {
            throw new \RuntimeException(
                "No se puede eliminar la tabla 'users' porque contiene {$userCount} usuario(s). "
                . 'Si estás seguro, elimina los usuarios manualmente primero.'
            );
        }

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
