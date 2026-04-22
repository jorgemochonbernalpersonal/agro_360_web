<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea 450 viticultores demo y los vincula a la bodega (user_id = 1).
 *
 *   · 300 activos    (can_login=true, email verificado, activated_at)
 *   · 150 ghost      (can_login=false, invitación pendiente)
 *
 *   · ~380 source='own'           (vinculados por la bodega)
 *   · ~ 70 source='viticulturist' (auto-registrados)
 *
 *   · ~220 con cuaderno_access=true
 *
 * Todos usan email *@vit.bodegaagaete.demo para identificarlos en cleanup.
 *
 * Debe ejecutarse ANTES de WineryGrapeReceptionsSeeder.
 */
class WineryViticulturistsSeeder extends Seeder
{
    private const WINERY_USER_ID  = 1;
    private const EMAIL_DOMAIN    = 'vit.bodegaagaete.demo';
    // Bcrypt hash de "password" — evita 450 llamadas lentas a bcrypt()
    private const PASSWORD_HASH   = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    // ── Nombres ───────────────────────────────────────────────────────────────

    private const FIRST_NAMES = [
        'Carlos', 'Ana', 'Pedro', 'María', 'José', 'Laura', 'Antonio', 'Isabel',
        'Francisco', 'Carmen', 'Juan', 'Elena', 'Manuel', 'Rosa', 'Miguel',
        'Pilar', 'David', 'Lucía', 'Rafael', 'Teresa', 'Alejandro', 'Sofía',
        'Javier', 'Marta', 'Sergio', 'Patricia', 'Fernando', 'Cristina',
        'Roberto', 'Sandra', 'Adrián', 'Beatriz', 'Óscar', 'Silvia', 'Diego',
        'Verónica', 'Pablo', 'Nuria', 'Eduardo', 'Raquel', 'Marcos', 'Alba',
        'Tomás', 'Inés', 'Andrés', 'Alicia', 'Rubén', 'Esther', 'Ignacio',
        'Lorena',
    ];

    private const LAST_NAMES = [
        'González Pérez',    'Rodríguez Martín',  'López García',      'Fernández Díaz',
        'Martínez Jiménez',  'Sánchez Romero',     'Pérez Torres',      'Gómez Vargas',
        'Díaz Ruiz',         'Hernández Moreno',   'Álvarez Castro',    'Muñoz Reyes',
        'Romero Blanco',     'Alonso Vega',        'Gutiérrez Molina',  'Navarro Santos',
        'Torres Ortega',     'Domínguez Ramos',    'Vázquez Serrano',   'Ramos Herrera',
        'Suárez Medina',     'Ramírez Gil',        'Flores Cruz',       'Morales León',
        'Ortiz Delgado',     'Ruiz Ríos',          'Jiménez Cabrera',   'Moreno Guerrero',
        'Aguilar Melo',      'Castro Cabrera',     'Mendez Fuentes',    'Peña Lara',
        'Cabrera Santana',   'Santana Hernández',  'Afonso Rodríguez',  'Bethencourt Pérez',
        'Perdomo García',    'Armas Díaz',         'Suárez Acosta',     'Trujillo Sosa',
        'Medina Quintero',   'Quintero Alvarado',  'Alvarado Rivero',   'Rivero Estévez',
        'Estévez Padilla',   'Padilla Montes',     'Montes Castellano', 'Castellano Leiva',
        'Leiva Benítez',     'Benítez Espino',
    ];

    // ── DNI letter table ──────────────────────────────────────────────────────
    private const DNI_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    public function run(): void
    {
        $this->cleanup();

        $now          = now();
        $userRows     = [];
        $firstNames   = self::FIRST_NAMES;
        $lastNames    = self::LAST_NAMES;
        $fnCount      = count($firstNames);
        $lnCount      = count($lastNames);

        for ($i = 0; $i < 450; $i++) {
            $firstName  = $firstNames[$i % $fnCount];
            $lastName   = $lastNames[$i % $lnCount];
            $name       = $firstName . ' ' . $lastName;
            $email      = strtolower(
                preg_replace('/\s+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $firstName))
                . '.' .
                preg_replace('/\s+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $lastName)[0]))
                . ($i + 1)
                . '@' . self::EMAIL_DOMAIN
            );

            $isActive     = $i < 300;  // primeros 300 activos
            $dniNumber    = 10000000 + ($i * 197) % 89999999; // pseudoaleatorio, sin colisiones en 450
            $dniLetter    = self::DNI_LETTERS[$dniNumber % 23];
            $dni          = $dniNumber . $dniLetter;

            $verifiedAt   = $isActive ? $now->copy()->subDays(365 - ($i % 300))->toDateTimeString() : null;
            $activatedAt  = $isActive ? $now->copy()->subDays(360 - ($i % 300))->toDateTimeString() : null;

            $invToken     = !$isActive ? bin2hex(random_bytes(16)) : null;
            $invSentAt    = !$isActive ? $now->copy()->subDays(30 - ($i % 25))->toDateTimeString() : null;
            $invExpiresAt = !$isActive ? $now->copy()->addDays(7)->toDateTimeString() : null;

            $userRows[] = [
                'name'                  => $name,
                'dni'                   => $dni,
                'email'                 => $email,
                'password'              => self::PASSWORD_HASH,
                'role'                  => 'viticulturist',
                'can_login'             => $isActive,
                'email_verified_at'     => $verifiedAt,
                'activated_at'          => $activatedAt,
                'invitation_token'      => $invToken,
                'invitation_sent_at'    => $invSentAt,
                'invitation_expires_at' => $invExpiresAt,
                'password_must_reset'   => false,
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
        }

        // Insertar usuarios en lotes y recoger IDs
        $insertedIds = [];
        foreach (array_chunk($userRows, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Recuperar IDs en orden de inserción (por email domain)
        $insertedIds = DB::table('users')
            ->where('email', 'like', '%@' . self::EMAIL_DOMAIN)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($insertedIds)) {
            $this->command->error('No se pudieron recuperar los IDs de viticultores creados.');
            return;
        }

        // ── winery_viticulturist pivot ────────────────────────────────────────
        $pivotRows = [];

        // sources: primeros 380 = 'own', últimos 70 = 'viticulturist'
        // cuaderno_access: activos con índice par (hasta ~220)
        foreach ($insertedIds as $idx => $vitId) {
            $isActive        = $idx < 300;
            $source          = $idx < 380 ? 'own' : 'viticulturist';
            $cuadernoAccess  = $isActive && ($idx % 3 !== 2); // ~200 de los 300 activos
            $cuadernoGranted = $cuadernoAccess
                ? now()->subDays(200 - ($idx % 180))->toDateTimeString()
                : null;

            $pivotRows[] = [
                'winery_id'           => self::WINERY_USER_ID,
                'viticulturist_id'    => $vitId,
                'assigned_by'         => self::WINERY_USER_ID,
                'source'              => $source,
                'supervisor_id'       => null,
                'parent_viticulturist_id' => null,
                'notes'               => null,
                'cuaderno_access'     => $cuadernoAccess,
                'cuaderno_granted_at' => $cuadernoGranted,
                'cuaderno_revoked_at' => null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        foreach (array_chunk($pivotRows, 100) as $chunk) {
            DB::table('winery_viticulturist')->insert($chunk);
        }

        $active    = count(array_filter($pivotRows, fn($r) => $r['cuaderno_access']));
        $own       = count(array_filter($pivotRows, fn($r) => $r['source'] === 'own'));
        $this->command->info(
            '✅ Viticultores vinculados: ' . count($pivotRows) . " registros" .
            " (300 activos · 150 ghost · {$active} con cuaderno · {$own} own)"
        );
    }

    private function cleanup(): void
    {
        // Localizar usuarios demo por dominio de email
        $demoVitIds = DB::table('users')
            ->where('email', 'like', '%@' . self::EMAIL_DOMAIN)
            ->pluck('id');

        if ($demoVitIds->isNotEmpty()) {
            // Pivot primero (FK child)
            DB::table('winery_viticulturist')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('viticulturist_id', $demoVitIds)
                ->delete();

            // Eliminar usuarios demo
            DB::table('users')->whereIn('id', $demoVitIds)->delete();
        }
    }
}
