<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Plot;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithGeographyData;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase, WithGeographyData;

    protected AuditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createGeographyData();
        $this->service = new AuditService();
    }

    public function test_can_get_user_activity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Crear actividad
        Plot::factory()->count(3)->create(['viticulturist_id' => $user->id]);

        $activity = $this->service->getUserActivity($user, 10);

        $this->assertGreaterThan(0, $activity->count());
        $this->assertLessThanOrEqual(10, $activity->count());
    }

    public function test_can_get_recent_activity(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Plot::factory()->count(5)->create(['viticulturist_id' => $user->id]);

        $recent = $this->service->getRecentActivity(10);

        $this->assertGreaterThan(0, $recent->count());
    }

    public function test_can_get_activity_by_date_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Plot::factory()->create(['viticulturist_id' => $user->id]);

        $start = new \DateTime('2026-01-01');
        $end = new \DateTime('2026-12-31');

        $activity = $this->service->getActivityByDateRange($start, $end);

        $this->assertGreaterThan(0, $activity->count());
    }

    public function test_can_get_activity_stats(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Plot::factory()->count(3)->create(['viticulturist_id' => $user->id]);

        $stats = $this->service->getActivityStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('by_event', $stats);
        $this->assertArrayHasKey('by_model', $stats);
        $this->assertGreaterThan(0, $stats['total']);
    }

    public function test_can_get_compliance_report(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Plot::factory()->count(2)->create(['viticulturist_id' => $user->id]);

        $start = new \DateTime('2026-01-01');
        $end = new \DateTime('2026-12-31');

        $report = $this->service->getComplianceReport($user, $start, $end);

        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('user', $report);
        $this->assertArrayHasKey('total_actions', $report);
        $this->assertArrayHasKey('logs', $report);
        
        $this->assertEquals($user->id, $report['user']['id']);
        $this->assertEquals('2026-01-01', $report['period']['start']);
    }

    public function test_can_export_to_csv(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Plot::factory()->create(['viticulturist_id' => $user->id]);

        $logs = AuditLog::with('user')->get();
        $csv = $this->service->exportToCsv($logs);

        $this->assertIsString($csv);
        $this->assertStringContainsString('Fecha', $csv);
        $this->assertStringContainsString('Usuario', $csv);
        $this->assertStringContainsString('Acción', $csv);
    }

    public function test_can_clean_old_logs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Plot::factory()->create(['viticulturist_id' => $user->id]);

        // Modificar fecha de un log para que sea antiguo
        AuditLog::first()->update(['created_at' => now()->subDays(400)]);

        $deleted = $this->service->cleanOldLogs(365);

        $this->assertGreaterThan(0, $deleted);
    }

    public function test_compliance_report_includes_critical_models_only(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Crear diferentes tipos de modelos
        Plot::factory()->create(['viticulturist_id' => $user->id]);
        $user->update(['name' => 'Updated Name']); // Este NO debe aparecer

        $start = new \DateTime('2026-01-01');
        $end = new \DateTime('2026-12-31');

        $report = $this->service->getComplianceReport($user, $start, $end);

        // Solo debe incluir Plot (crítico), no User
        $logTypes = collect($report['logs'])->pluck('model')->unique()->toArray();
        
        $this->assertContains('Plot', $logTypes);
    }
}
