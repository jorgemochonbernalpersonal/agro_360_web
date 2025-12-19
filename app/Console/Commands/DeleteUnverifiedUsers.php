<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-unverified 
                            {--hours=24 : Number of hours after which unverified users are deleted}
                            {--dry-run : Run the command without actually deleting users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users who have not verified their email within the specified time period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        $this->info("Looking for unverified users created more than {$hours} hours ago...");
        
        // Buscar usuarios no verificados creados hace más de X horas
        $unverifiedUsers = User::whereNull('email_verified_at')
            ->where('created_at', '<', now()->subHours($hours))
            ->get();
        
        if ($unverifiedUsers->isEmpty()) {
            $this->info('No unverified users found to delete.');
            return Command::SUCCESS;
        }
        
        $this->warn("Found {$unverifiedUsers->count()} unverified user(s) to delete:");
        
        // Mostrar lista de usuarios que se eliminarán
        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Created At'],
            $unverifiedUsers->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            })
        );
        
        if ($dryRun) {
            $this->comment('DRY RUN: No users were actually deleted.');
            return Command::SUCCESS;
        }
        
        // Confirmar antes de eliminar (solo en modo interactivo)
        if ($this->input->isInteractive()) {
            if (!$this->confirm('Do you want to proceed with deletion?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }
        
        // Eliminar usuarios
        $deletedCount = 0;
        foreach ($unverifiedUsers as $user) {
            try {
                $user->delete();
                $deletedCount++;
                $this->line("Deleted user: {$user->email}");
            } catch (\Exception $e) {
                $this->error("Failed to delete user {$user->email}: {$e->getMessage()}");
            }
        }
        
        $this->info("Successfully deleted {$deletedCount} unverified user(s).");
        
        return Command::SUCCESS;
    }
}
