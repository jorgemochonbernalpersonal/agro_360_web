<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {user : ID o email del usuario a convertir en admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte un usuario existente en administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userIdentifier = $this->argument('user');

        // Buscar usuario por ID o email
        $user = is_numeric($userIdentifier)
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();

        if (! $user) {
            $this->error("❌ No se encontró el usuario: {$userIdentifier}");

            return 1;
        }

        // Verificar si ya es admin
        if ($user->role === 'admin') {
            $this->warn("⚠️  El usuario {$user->name} ({$user->email}) ya es administrador.");

            return 0;
        }

        // Confirmar acción
        $this->info('📋 Usuario encontrado:');
        $this->info("   ID: {$user->id}");
        $this->info("   Nombre: {$user->name}");
        $this->info("   Email: {$user->email}");
        $this->info("   Rol actual: {$user->role}");
        $this->newLine();

        if (! $this->confirm('¿Convertir este usuario en administrador?', true)) {
            $this->info('❌ Operación cancelada.');

            return 0;
        }

        // Cambiar rol a admin
        $oldRole = $user->role;
        $user->role = 'admin';
        $user->save();

        $this->newLine();
        $this->info('✅ Usuario convertido a administrador exitosamente.');
        $this->info("   Rol anterior: {$oldRole}");
        $this->info('   Rol nuevo: admin');
        $this->newLine();
        $this->info("🔐 El usuario {$user->name} ahora tiene acceso completo al panel de administración.");

        return 0;
    }
}
