<?php

namespace App\Console\Commands;

use Database\Seeders\CompleteTestUserSeeder;
use Illuminate\Console\Command;

class SeedUserDataCommand extends Command
{
    protected $signature = 'user:seed-data {user_id : The ID of the user to seed data for}';
    protected $description = 'Seed complete test data for a specific user';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        
        $this->info("🚀 Generando datos completos para usuario ID: {$userId}");
        $this->newLine();

        try {
            $seeder = new CompleteTestUserSeeder();
            $seeder->setCommand($this);
            $seeder->run($userId);
            
            $this->newLine();
            $this->info('✅ Datos generados exitosamente!');
            $this->info('💡 Ahora puedes generar datos de remote sensing:');
            $this->info('   php artisan remote-sensing:regenerate-mock');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
