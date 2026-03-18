<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crea registros en `organizations` a partir de los usuarios existentes
 * con role winery o denomination_of_origin.
 *
 * Uso: php artisan db:seed --class=OrganizationsFromUsersSeeder
 *
 * Es idempotente: si ya existe una organización para ese usuario (owner_user_id),
 * no la duplica. Se puede ejecutar varias veces.
 */
class OrganizationsFromUsersSeeder extends Seeder
{
    public function run(): void
    {
        $roleToType = [
            'winery'                 => Organization::TYPE_WINERY,
            'denomination_of_origin' => Organization::TYPE_DENOMINATION,
        ];

        $users = User::whereIn('role', array_keys($roleToType))->get();

        $created = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $alreadyExists = Organization::where('owner_user_id', $user->id)->exists();

            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            $org = Organization::create([
                'name'          => $user->name,
                'type'          => $roleToType[$user->role],
                'slug'          => Str::slug($user->name) . '-' . $user->id,
                'email'         => !str_contains($user->email, '@noemail.agro365.es') ? $user->email : null,
                'active'        => true,
                'owner_user_id' => $user->id,
            ]);

            // Vincula el usuario a su organización
            $user->update(['organization_id' => $org->id]);

            $created++;
        }

        $this->command->info("Organizaciones creadas: {$created} | Ya existían: {$skipped}");
    }
}
