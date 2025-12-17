/**
 * Script para crear usuario de prueba para tests E2E
 * Ejecutar en tinker: php artisan tinker
 * Luego copiar y pegar el código de abajo
 */

// Crear usuario viticultor de prueba
$user = \App\Models\User::firstOrCreate(
    ['email' => 'viticulturist@example.com'],
    [
        'name' => 'Test Viticulturist',
        'password' => bcrypt('password'),
        'role' => 'viticulturist',
        'email_verified_at' => now(),
    ]
);

echo "Usuario creado: {$user->email}\n";
echo "Contraseña: password\n";

