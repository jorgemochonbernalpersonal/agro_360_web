<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates 450 demo viticulturists and links them to the winery (user_id = 1).
 *
 *   · 300 active  (can_login=true, email verified, activated_at)
 *   · 150 ghost   (can_login=false, pending invitation)
 *
 *   · ~380 source='own'           (linked by the winery)
 *   · ~ 70 source='viticulturist' (self-registered)
 *
 *   · ~200 with notebook access (cuaderno_access=true, only if column exists)
 *
 * All use email *@vit.bodegaagaete.demo for idempotent cleanup.
 *
 * Must run BEFORE WineryGrapeReceptionsSeeder.
 */
class WineryViticulturistsSeeder extends Seeder
{
    private const WINERY_USER_ID  = 1;
    private const EMAIL_DOMAIN    = 'vit.bodegaagaete.demo';
    // Bcrypt hash of "password" — avoids 450 slow bcrypt() calls
    private const PASSWORD_HASH   = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    // ── Names ────────────────────────────────────────────────────────────────

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

    // ── DNI check-letter table ────────────────────────────────────────────────
    private const DNI_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    public function run(): void
    {
        $this->cleanup();

        $now        = now();
        $firstNames = self::FIRST_NAMES;
        $lastNames  = self::LAST_NAMES;
        $fnCount    = count($firstNames);
        $lnCount    = count($lastNames);

        // Detect optional users columns added by later migrations
        $hasDni          = Schema::hasColumn('users', 'dni');
        $hasCanLogin     = Schema::hasColumn('users', 'can_login');
        $hasActivatedAt  = Schema::hasColumn('users', 'activated_at');
        $hasInvToken     = Schema::hasColumn('users', 'invitation_token');
        $hasInvSentAt    = Schema::hasColumn('users', 'invitation_sent_at');
        $hasInvExpiresAt = Schema::hasColumn('users', 'invitation_expires_at');
        $hasPwdReset     = Schema::hasColumn('users', 'password_must_reset');

        $userRows = [];

        for ($i = 0; $i < 450; $i++) {
            $firstName = $firstNames[$i % $fnCount];
            $lastName  = $lastNames[$i % $lnCount];
            $name      = $firstName . ' ' . $lastName;
            $email     = strtolower(
                preg_replace('/\s+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $firstName))
                . '.' .
                preg_replace('/\s+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $lastName)[0]))
                . ($i + 1)
                . '@' . self::EMAIL_DOMAIN
            );

            $isActive  = $i < 300; // first 300 are active

            $row = [
                'name'              => $name,
                'email'             => $email,
                'password'          => self::PASSWORD_HASH,
                'role'              => 'viticulturist',
                'email_verified_at' => $isActive ? $now->copy()->subDays(365 - ($i % 300))->toDateTimeString() : null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            if ($hasDni) {
                $dniNumber    = 10000000 + ($i * 197) % 89999999; // pseudo-random, no collisions across 450
                $row['dni']   = $dniNumber . self::DNI_LETTERS[$dniNumber % 23];
            }

            if ($hasCanLogin) {
                $row['can_login'] = $isActive;
            }

            if ($hasActivatedAt) {
                $row['activated_at'] = $isActive
                    ? $now->copy()->subDays(360 - ($i % 300))->toDateTimeString()
                    : null;
            }

            if ($hasInvToken) {
                $row['invitation_token'] = !$isActive ? bin2hex(random_bytes(16)) : null;
            }

            if ($hasInvSentAt) {
                $row['invitation_sent_at'] = !$isActive
                    ? $now->copy()->subDays(30 - ($i % 25))->toDateTimeString()
                    : null;
            }

            if ($hasInvExpiresAt) {
                $row['invitation_expires_at'] = !$isActive
                    ? $now->copy()->addDays(7)->toDateTimeString()
                    : null;
            }

            if ($hasPwdReset) {
                $row['password_must_reset'] = false;
            }

            $userRows[] = $row;
        }

        // Insert users in chunks and retrieve their IDs
        foreach (array_chunk($userRows, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Fetch inserted IDs ordered by insertion sequence (via email domain)
        $insertedIds = DB::table('users')
            ->where('email', 'like', '%@' . self::EMAIL_DOMAIN)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($insertedIds)) {
            $this->command->error('Could not retrieve IDs for the created viticulturist users.');
            return;
        }

        // ── winery_viticulturist pivot ────────────────────────────────────────
        // Detect optional columns that may not exist on older DB schema versions
        $hasNotebookAccess = Schema::hasColumn('winery_viticulturist', 'notebook_access');
        $hasNotes = Schema::hasColumn('winery_viticulturist', 'notes');

        $pivotRows     = [];
        $notebookCount = 0;

        // First 380 linked by winery ('own'), last 70 self-registered ('viticulturist')
        foreach ($insertedIds as $idx => $vitId) {
            $isActive = $idx < 300;
            $source   = $idx < 380 ? 'own' : 'viticulturist';

            $row = [
                'winery_id'               => self::WINERY_USER_ID,
                'viticulturist_id'        => $vitId,
                'assigned_by'             => self::WINERY_USER_ID,
                'source'                  => $source,
                'supervisor_id'           => null,
                'parent_viticulturist_id' => null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];

            if ($hasNotes) {
                $row['notes'] = null;
            }

            if ($hasNotebookAccess) {
                $notebookAccess  = $isActive && ($idx % 3 !== 2); // ~200 of 300 active users
                $notebookGranted = $notebookAccess
                    ? now()->subDays(200 - ($idx % 180))->toDateTimeString()
                    : null;

                $row['notebook_access']     = $notebookAccess;
                $row['notebook_granted_at'] = $notebookGranted;
                $row['notebook_revoked_at'] = null;

                if ($notebookAccess) {
                    $notebookCount++;
                }
            }

            $pivotRows[] = $row;
        }

        foreach (array_chunk($pivotRows, 100) as $chunk) {
            DB::table('winery_viticulturist')->insert($chunk);
        }

        $own = count(array_filter($pivotRows, fn($r) => $r['source'] === 'own'));
        $this->command->info(
            '✅ Linked viticulturists: ' . count($pivotRows) . " records" .
            " (300 active · 150 ghost · {$notebookCount} with notebook · {$own} own)"
        );
    }

    private function cleanup(): void
    {
        // Locate demo users by their email domain
        $demoVitIds = DB::table('users')
            ->where('email', 'like', '%@' . self::EMAIL_DOMAIN)
            ->pluck('id');

        if ($demoVitIds->isNotEmpty()) {
            // Delete pivot records first (FK child)
            DB::table('winery_viticulturist')
                ->where('winery_id', self::WINERY_USER_ID)
                ->whereIn('viticulturist_id', $demoVitIds)
                ->delete();

            // Delete demo user accounts
            DB::table('users')->whereIn('id', $demoVitIds)->delete();
        }
    }
}
