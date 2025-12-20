<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

/**
 * Seeder para poblar datos de un usuario específico
 * Uso: php artisan db:seed --class=SeedUserDataSeeder -- --user=9
 */
class SeedUserDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el ID del usuario desde los argumentos de línea de comandos
        // Laravel pasa los argumentos después de -- como opciones
        $userId = null;
        
        // Intentar obtener de diferentes formas
        if ($this->command->hasOption('user')) {
            $userId = $this->command->option('user');
        } elseif (isset($_SERVER['argv'])) {
            // Buscar --user=ID en los argumentos
            foreach ($_SERVER['argv'] as $arg) {
                if (strpos($arg, '--user=') === 0) {
                    $userId = (int) str_replace('--user=', '', $arg);
                    break;
                }
            }
        }
        
        if (!$userId) {
            $this->command->error('❌ Debes especificar el ID del usuario con --user=ID');
            $this->command->info('');
            $this->command->info('Ejemplo:');
            $this->command->info('  php artisan db:seed --class=SeedUserDataSeeder -- --user=9');
            return;
        }
        
        // Verificar que el usuario existe
        $user = User::find($userId);
        if (!$user) {
            $this->command->error("❌ No se encontró el usuario con ID: {$userId}");
            return;
        }
        
        $this->command->info("🌱 Poblando datos para el usuario: {$user->name} ({$user->email})");
        $this->command->info("📊 Generando 20 elementos por cada tipo de dato...");
        $this->command->info('');
        
        // Ejecutar el seeder completo para este usuario
        $completeSeeder = new CompleteTestUserSeeder();
        $completeSeeder->setCommand($this->command);
        $completeSeeder->run($userId);
        
        $this->command->info("");
        $this->command->info("✅ Datos poblados exitosamente para el usuario ID: {$userId}");
    }
}

