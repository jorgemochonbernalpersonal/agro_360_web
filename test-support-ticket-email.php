<?php

/**
 * Script de prueba para verificar el envío de emails de tickets de soporte
 * 
 * Uso: php test-support-ticket-email.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\SupportTicket;
use App\Notifications\SupportTicketCreatedNotification;

echo "=== Test de Email de Ticket de Soporte ===\n\n";

// Verificar configuración de mail
echo "1. Verificando configuración de mail:\n";
echo "   MAIL_MAILER: " . env('MAIL_MAILER', 'no configurado') . "\n";
echo "   MAIL_HOST: " . env('MAIL_HOST', 'no configurado') . "\n";
echo "   MAIL_PORT: " . env('MAIL_PORT', 'no configurado') . "\n";
echo "   MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'no configurado') . "\n";
echo "   APP_ENV: " . env('APP_ENV', 'no configurado') . "\n\n";

// Verificar administradores
echo "2. Verificando administradores:\n";
$admins = User::where('role', User::ROLE_ADMIN)->get();
echo "   Total de administradores: " . $admins->count() . "\n";

if ($admins->count() === 0) {
    echo "   ⚠️  No hay administradores en la base de datos.\n";
    echo "   Creando un administrador de prueba...\n";
    
    $admin = User::firstOrCreate(
        ['email' => 'admin@agro365.local'],
        [
            'name' => 'Administrador de Prueba',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'can_login' => true,
        ]
    );
    echo "   ✅ Administrador creado: {$admin->email}\n\n";
    $admins = collect([$admin]);
} else {
    foreach ($admins as $admin) {
        echo "   - {$admin->name} ({$admin->email})\n";
    }
    echo "\n";
}

// Crear un ticket de prueba
echo "3. Creando ticket de prueba:\n";
$user = User::where('role', '!=', User::ROLE_ADMIN)->first();
if (!$user) {
    $user = User::firstOrCreate(
        ['email' => 'test@agro365.local'],
        [
            'name' => 'Usuario de Prueba',
            'password' => bcrypt('password'),
            'role' => User::ROLE_VITICULTURIST,
            'email_verified_at' => now(),
            'can_login' => true,
        ]
    );
    echo "   Usuario de prueba creado: {$user->email}\n";
}

$ticket = SupportTicket::create([
    'user_id' => $user->id,
    'title' => 'Ticket de Prueba - ' . now()->format('Y-m-d H:i:s'),
    'description' => 'Este es un ticket de prueba para verificar el envío de emails.',
    'type' => 'question',
    'priority' => 'medium',
    'status' => 'open',
]);
$ticket->load('user');
echo "   ✅ Ticket creado: ID {$ticket->id}\n\n";

// Enviar notificaciones
echo "4. Enviando notificaciones:\n";
$sent = 0;
$errors = 0;

foreach ($admins as $admin) {
    try {
        $admin->notify(new SupportTicketCreatedNotification($ticket));
        echo "   ✅ Email enviado a: {$admin->email}\n";
        $sent++;
    } catch (\Exception $e) {
        echo "   ❌ Error al enviar a {$admin->email}: {$e->getMessage()}\n";
        $errors++;
    }
}

echo "\n=== Resumen ===\n";
echo "Emails enviados: {$sent}\n";
echo "Errores: {$errors}\n";

if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
    echo "\n💡 En desarrollo, revisa MailHog en: http://localhost:8025\n";
}

echo "\n✅ Test completado.\n";

