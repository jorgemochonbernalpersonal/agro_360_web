<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ClearUserCacheAction
{
    /**
     * Limpiar todos los caches relacionados con un usuario
     */
    public function execute(User $user): void
    {
        // Limpiar cache del modelo
        $user->clearAttributeCache();

        // Limpiar cache de Laravel
        Cache::forget("user_{$user->id}_supervisor");
        Cache::forget("user_{$user->id}_wineries");
        Cache::forget("user_{$user->id}_needs_password_change");
        Cache::forget("user_{$user->id}_plots");
        Cache::forget("user_{$user->id}_activities_count");

        // Limpiar cache de sesión
        session()->forget("user_{$user->id}_needs_password_change");
    }

    /**
     * Limpiar cache de múltiples usuarios
     */
    public function executeForMany(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->execute($user);
            }
        }
    }
}
