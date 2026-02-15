<?php

namespace Tests\Unit\Traits;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Plot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithGeographyData;

class AuditableTest extends TestCase
{
    use RefreshDatabase, WithGeographyData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createGeographyData();
    }

    public function test_creates_audit_log_on_model_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Plot::class,
            'auditable_id' => $plot->id,
            'event' => 'created',
            'user_id' => $user->id,
        ]);
    }

    public function test_creates_audit_log_on_model_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id, 'name' => 'Original Name']);
        
        // Limpiar el log de creación para el test
        AuditLog::where('event', 'created')->delete();

        $plot->update(['name' => 'Updated Name']);

        $auditLog = AuditLog::where('event', 'updated')->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('updated', $auditLog->event);
        $this->assertEquals($user->id, $auditLog->user_id);
        $this->assertArrayHasKey('name', $auditLog->new_values);
        $this->assertEquals('Updated Name', $auditLog->new_values['name']);
    }

    public function test_creates_audit_log_on_model_deletion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $plotId = $plot->id;

        // Limpiar logs previos
        AuditLog::where('auditable_id', $plotId)->delete();

        $plot->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Plot::class,
            'auditable_id' => $plotId,
            'event' => 'deleted',
            'user_id' => $user->id,
        ]);
    }

    public function test_audit_log_contains_ip_and_user_agent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);

        $auditLog = AuditLog::where('auditable_id', $plot->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($auditLog->ip_address);
        $this->assertNotNull($auditLog->user_agent);
    }

    public function test_can_get_audit_history_for_model(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id, 'name' => 'Original']);
        
        // Hacer varios cambios
        $plot->update(['name' => 'First Update']);
        $plot->update(['name' => 'Second Update']);

        $history = $plot->auditHistory();

        $this->assertGreaterThanOrEqual(3, $history->count()); // created + 2 updates
    }

    public function test_get_latest_audit_returns_most_recent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $plot->update(['name' => 'Updated']);

        $latest = $plot->latestAudit();

        $this->assertEquals('updated', $latest->event);
    }

    public function test_audit_log_get_changes_shows_diff(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id, 'area' => 5.5]);
        
        AuditLog::where('event', 'created')->delete();
        
        $plot->update(['area' => 6.0]);

        $auditLog = AuditLog::where('event', 'updated')->first();
        $changes = $auditLog->getChanges();

        $this->assertArrayHasKey('area', $changes);
        $this->assertEquals(5.5, $changes['area']['old']);
        $this->assertEquals(6.0, $changes['area']['new']);
    }

    public function test_audit_log_description_is_human_readable(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);

        $auditLog = AuditLog::where('auditable_id', $plot->id)->first();
        $description = $auditLog->getDescription();

        $this->assertStringContainsString('John Doe', $description);
        $this->assertStringContainsString('creó', $description);
        $this->assertStringContainsString('Plot', $description);
    }

    public function test_can_query_audit_logs_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);
        Plot::factory()->create(['viticulturist_id' => $user1->id]);

        $this->actingAs($user2);
        Plot::factory()->create(['viticulturist_id' => $user2->id]);

        $user1Logs = AuditLog::byUser($user1->id)->get();
        $user2Logs = AuditLog::byUser($user2->id)->get();

        $this->assertGreaterThan(0, $user1Logs->count());
        $this->assertGreaterThan(0, $user2Logs->count());
    }

    public function test_can_query_audit_logs_by_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plot = Plot::factory()->create(['viticulturist_id' => $user->id]);
        $plot->update(['name' => 'Updated']);

        $createdLogs = AuditLog::event('created')->get();
        $updatedLogs = AuditLog::event('updated')->get();

        $this->assertGreaterThan(0, $createdLogs->count());
        $this->assertGreaterThan(0, $updatedLogs->count());
    }
}
