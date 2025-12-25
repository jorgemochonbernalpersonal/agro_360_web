<?php

namespace Database\Seeders;

use App\Models\Plot;
use App\Models\PlotAuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PlotAuditHistorySeeder extends Seeder
{
    /**
     * Seed audit history for existing plots
     */
    public function run(): void
    {
        $this->command->info('🔍 Generando historial de auditoría para parcelas...');

        // Obtener todas las parcelas
        $plots = Plot::with(['viticulturist'])->get();
        
        if ($plots->isEmpty()) {
            $this->command->warn('⚠️  No hay parcelas para generar historial.');
            return;
        }

        $this->command->info("📊 Encontradas {$plots->count()} parcelas");

        $totalLogs = 0;

        foreach ($plots as $plot) {
            $logsCreated = $this->generateAuditHistory($plot);
            $totalLogs += $logsCreated;
        }

        $this->command->info("✅ Generados {$totalLogs} registros de auditoría para parcelas");
    }

    /**
     * Generate realistic audit history for a plot
     */
    protected function generateAuditHistory(Plot $plot): int
    {
        $logsCreated = 0;
        $userId = $plot->viticulturist_id;
        
        // 1. Log de creación
        $createdAt = $plot->created_at ?? now()->subDays(rand(30, 90));
        
        PlotAuditLog::create([
            'plot_id' => $plot->id,
            'user_id' => $userId,
            'action' => 'created',
            'changes' => [
                'new' => [
                    'name' => $plot->name,
                    'surface_area' => $plot->surface_area,
                    'location' => $plot->location,
                ]
            ],
            'ip_address' => $this->randomIp(),
            'user_agent' => $this->randomUserAgent(),
            'created_at' => $createdAt,
        ]);
        $logsCreated++;

        // 2. Generar 0-2 modificaciones aleatorias
        $numUpdates = rand(0, 2);
        
        for ($i = 0; $i < $numUpdates; $i++) {
            $updateDate = $createdAt->copy()->addDays(rand(5, 20));
            
            // Cambios aleatorios
            $changes = $this->generateRandomChanges($plot);
            
            if (!empty($changes)) {
                PlotAuditLog::create([
                    'plot_id' => $plot->id,
                    'user_id' => $userId,
                    'action' => 'updated',
                    'changes' => $changes,
                    'ip_address' => $this->randomIp(),
                    'user_agent' => $this->randomUserAgent(),
                    'created_at' => $updateDate,
                ]);
                $logsCreated++;
            }
        }

        return $logsCreated;
    }

    /**
     * Generate random changes for a plot
     */
    protected function generateRandomChanges(Plot $plot): array
    {
        $possibleChanges = [
            'surface_area' => [
                'old' => $plot->surface_area,
                'new' => round($plot->surface_area + (rand(-10, 10) / 100), 3), // ±0.1 ha
            ],
            'location' => [
                'old' => $plot->location,
                'new' => $plot->location . ' (Actualizado)',
            ],
            'cadastral_reference' => [
                'old' => $plot->cadastral_reference,
                'new' => $plot->cadastral_reference ? substr($plot->cadastral_reference, 0, -2) . rand(10, 99) : null,
            ],
        ];

        // Seleccionar 1 cambio aleatorio
        $selectedKey = array_rand($possibleChanges);

        $changes = [
            'old' => [$selectedKey => $possibleChanges[$selectedKey]['old']],
            'new' => [$selectedKey => $possibleChanges[$selectedKey]['new']],
        ];

        return $changes;
    }

    /**
     * Generate random IP address
     */
    protected function randomIp(): string
    {
        $ips = [
            '192.168.1.' . rand(1, 255),
            '10.0.0.' . rand(1, 255),
            '172.16.0.' . rand(1, 255),
            '84.88.12.' . rand(1, 255),
            '213.97.45.' . rand(1, 255),
        ];

        return $ips[array_rand($ips)];
    }

    /**
     * Generate random user agent
     */
    protected function randomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
