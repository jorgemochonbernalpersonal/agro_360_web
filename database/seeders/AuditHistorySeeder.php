<?php

namespace Database\Seeders;

use App\Models\AgriculturalActivity;
use App\Models\AgriculturalActivityAuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AuditHistorySeeder extends Seeder
{
    /**
     * Seed audit history for existing activities
     */
    public function run(): void
    {
        $this->command->info('🔍 Generando historial de auditoría para actividades...');

        // Obtener todas las actividades
        $activities = AgriculturalActivity::with(['viticulturist'])->get();
        
        if ($activities->isEmpty()) {
            $this->command->warn('⚠️  No hay actividades para generar historial.');
            return;
        }

        $this->command->info("📊 Encontradas {$activities->count()} actividades");

        $totalLogs = 0;

        foreach ($activities as $activity) {
            $logsCreated = $this->generateAuditHistory($activity);
            $totalLogs += $logsCreated;
        }

        $this->command->info("✅ Generados {$totalLogs} registros de auditoría");
    }

    /**
     * Generate realistic audit history for an activity
     */
    protected function generateAuditHistory(AgriculturalActivity $activity): int
    {
        $logsCreated = 0;
        $userId = $activity->viticulturist_id;
        
        // 1. Log de creación
        $createdAt = $activity->created_at ?? now()->subDays(rand(10, 30));
        
        AgriculturalActivityAuditLog::create([
            'activity_id' => $activity->id,
            'user_id' => $userId,
            'action' => 'created',
            'changes' => [
                'new' => [
                    'activity_type' => $activity->activity_type,
                    'activity_date' => $activity->activity_date->format('Y-m-d'),
                    'plot_id' => $activity->plot_id,
                    'notes' => $activity->notes,
                ]
            ],
            'ip_address' => $this->randomIp(),
            'user_agent' => $this->randomUserAgent(),
            'created_at' => $createdAt,
        ]);
        $logsCreated++;

        // 2. Generar 1-3 modificaciones aleatorias
        $numUpdates = rand(1, 3);
        
        for ($i = 0; $i < $numUpdates; $i++) {
            $updateDate = $createdAt->copy()->addDays(rand(1, 5));
            
            // Cambios aleatorios
            $changes = $this->generateRandomChanges($activity);
            
            if (!empty($changes)) {
                AgriculturalActivityAuditLog::create([
                    'activity_id' => $activity->id,
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

        // 3. Si está bloqueada, añadir log de bloqueo
        if ($activity->is_locked && $activity->locked_at) {
            AgriculturalActivityAuditLog::create([
                'activity_id' => $activity->id,
                'user_id' => $activity->locked_by ?? $userId,
                'action' => 'locked',
                'changes' => [
                    'old' => ['is_locked' => false],
                    'new' => ['is_locked' => true],
                ],
                'ip_address' => '127.0.0.1', // Sistema automático
                'user_agent' => 'Agro365 Auto-Lock System',
                'created_at' => $activity->locked_at,
            ]);
            $logsCreated++;
        }

        return $logsCreated;
    }

    /**
     * Generate random changes for an activity
     */
    protected function generateRandomChanges(AgriculturalActivity $activity): array
    {
        $possibleChanges = [
            'notes' => [
                'old' => $activity->notes,
                'new' => $activity->notes . ' (Actualizado)',
            ],
            'weather_conditions' => [
                'old' => $activity->weather_conditions,
                'new' => ['Soleado', 'Nublado', 'Lluvioso', 'Ventoso'][rand(0, 3)],
            ],
            'temperature' => [
                'old' => $activity->temperature,
                'new' => rand(15, 30),
            ],
            'phenological_stage' => [
                'old' => $activity->phenological_stage,
                'new' => ['Brotación', 'Floración', 'Cuajado', 'Envero', 'Maduración'][rand(0, 4)],
            ],
        ];

        // Seleccionar 1-2 cambios aleatorios
        $numChanges = rand(1, 2);
        $selectedKeys = array_rand($possibleChanges, $numChanges);
        
        if (!is_array($selectedKeys)) {
            $selectedKeys = [$selectedKeys];
        }

        $changes = [
            'old' => [],
            'new' => [],
        ];

        foreach ($selectedKeys as $key) {
            $changes['old'][$key] = $possibleChanges[$key]['old'];
            $changes['new'][$key] = $possibleChanges[$key]['new'];
        }

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
            '84.88.12.' . rand(1, 255), // IP pública española
            '213.97.45.' . rand(1, 255), // IP pública española
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
            'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
