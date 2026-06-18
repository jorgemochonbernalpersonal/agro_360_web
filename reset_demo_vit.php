<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'demo.viticultor@agro365.es';
$password = 'demo1234';

$user = \App\Models\User::where('role', 'viticulturist')->where('email', 'like', 'demo%')->first();

if (!$user) {
    echo "❌ No se encontró ningún viticultor demo.\n";
    echo "Usuarios con email demo existentes:\n";
    \App\Models\User::where('email', 'like', 'demo%')->get(['id','email','role'])
        ->each(fn($u) => print("  - {$u->id} | {$u->email} | {$u->role}\n"));
} else {
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->save();
    echo "✅ Contraseña actualizada.\n";
    echo "   Email:      {$user->email}\n";
    echo "   Contraseña: {$password}\n";
    echo "   Rol:        {$user->role}\n";
}
