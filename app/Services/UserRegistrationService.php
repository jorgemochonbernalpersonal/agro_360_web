<?php

namespace App\Services;

use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRegistrationService
{
    /**
     * Activa un viticultor "ghost" (can_login=false) cuyo email ya existía.
     * Devuelve el usuario fresco tras la actualización.
     */
    public function activateGhostByEmail(
        User $ghost,
        string $name,
        string $password,
        ?string $normalizedDni,
        string $email
    ): User {
        $ghostEmailMatches = strtolower($ghost->email) === strtolower($email);

        $ghost->update([
            'name' => $name,
            'password' => Hash::make($password),
            'can_login' => true,
            'password_must_reset' => false,
            'email_verified_at' => $ghostEmailMatches ? ($ghost->email_verified_at ?? now()) : null,
            'dni' => $normalizedDni ?? $ghost->dni,
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'invitation_sent_at' => null,
        ]);

        return $ghost->fresh();
    }

    /**
     * Intenta activar un ghost viticulturist encontrado por DNI.
     *
     * @return User   éxito — usuario activado y listo para Auth::login
     * @return string 'email_taken' — el email elegido ya pertenece a otra cuenta activa
     * @return null   no hay ghost con ese DNI
     */
    public function mergeGhostByDni(
        string $normalizedDni,
        string $email,
        string $name,
        string $password
    ): User|string|null {
        $ghost = User::where('dni', $normalizedDni)
            ->where('role', User::ROLE_VITICULTURIST)
            ->where('can_login', false)
            ->first();

        if (! $ghost) {
            return null;
        }

        $emailTaken = User::where('email', $email)
            ->where('id', '!=', $ghost->id)
            ->where('can_login', true)
            ->exists();

        if ($emailTaken) {
            return 'email_taken';
        }

        $ghost->update([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'can_login' => true,
            'password_must_reset' => false,
            'email_verified_at' => now(),
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'invitation_sent_at' => null,
        ]);

        return $ghost->fresh();
    }

    /**
     * Crea un usuario nuevo y sus relaciones organizativas en una transacción.
     * $userData debe incluir: name, email, password (hashed), role, dni, can_login,
     * password_must_reset, is_founder.
     */
    public function createUserWithRelationships(array $userData, ?User $creator): User
    {
        return DB::transaction(function () use ($userData, $creator) {
            $user = User::create($userData);

            if (! $creator) {
                return $user;
            }

            if ($creator->isSupervisor() && $userData['role'] === 'winery') {
                SupervisorWinery::create([
                    'supervisor_id' => $creator->id,
                    'winery_id' => $user->id,
                    'assigned_by' => $creator->id,
                ]);
            }

            if ($creator->hasWineryAccess() && $userData['role'] === 'viticulturist') {
                WineryViticulturist::create([
                    'winery_id' => $creator->id,
                    'viticulturist_id' => $user->id,
                    'source' => 'own',
                    'assigned_by' => $creator->id,
                ]);
            }

            if ($creator->hasViticulturistAccess() && $userData['role'] === 'viticulturist') {
                $creatorWinery = $creator->wineries->first();

                if ($creatorWinery) {
                    WineryViticulturist::create([
                        'winery_id' => $creatorWinery->id,
                        'viticulturist_id' => $user->id,
                        'source' => WineryViticulturist::SOURCE_VITICULTURIST,
                        'parent_viticulturist_id' => $creator->id,
                        'assigned_by' => $creator->id,
                    ]);
                }
            }

            return $user;
        });
    }

    /**
     * Devuelve true si el código es válido y quedan plazas de fundador.
     */
    public function isFounder(string $founderCode): bool
    {
        if ($founderCode === '') {
            return false;
        }

        $configCode = config('app.founder_code', '');

        return $configCode !== ''
            && hash_equals($configCode, $founderCode)
            && User::where('is_founder', true)->count() < config('app.founder_max_slots', 25);
    }
}
