<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillOrganizations extends Command
{
    protected $signature   = 'organizations:backfill {--dry-run : List users that would be affected without making changes}';
    protected $description = 'Create Organization records for winery, supervisor and producer users that do not have one yet.';

    public function handle(): int
    {
        $users = User::whereIn('role', [User::ROLE_WINERY, User::ROLE_SUPERVISOR, User::ROLE_PRODUCER])
            ->whereNull('organization_id')
            ->get();

        if ($users->isEmpty()) {
            $this->info('All eligible users already have an organization. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) without an organization.");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Email', 'Role'],
                $users->map(fn ($u) => [$u->id, $u->name, $u->email, $u->role])->toArray()
            );
            $this->line('Dry-run mode: no changes applied.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $created = 0;

        foreach ($users as $user) {
            $type = match ($user->role) {
                User::ROLE_WINERY,
                User::ROLE_PRODUCER   => Organization::TYPE_WINERY,
                User::ROLE_SUPERVISOR => Organization::TYPE_DENOMINATION,
                default               => null,
            };

            if ($type === null) {
                $bar->advance();
                continue;
            }

            $baseSlug = Str::slug($user->name);
            $slug     = $baseSlug;
            $i        = 1;
            while (Organization::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            $org = Organization::create([
                'name'          => $user->name,
                'type'          => $type,
                'slug'          => $slug,
                'email'         => str_contains($user->email, '@noemail.agro365.es') ? null : $user->email,
                'active'        => true,
                'owner_user_id' => $user->id,
            ]);

            $user->updateQuietly(['organization_id' => $org->id]);
            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Created {$created} organization(s).");

        return self::SUCCESS;
    }
}
