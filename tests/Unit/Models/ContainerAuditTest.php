<?php

namespace Tests\Unit\Models;

use App\Models\AuditLog;
use App\Models\Container;
use App\Models\ContainerRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auditoría de metadatos de contenedor (trait Auditable + AuditLog).
 *
 * Regla clave: los cambios de configuración (nombre, capacidad, sala, archivado)
 * SÍ se auditan; los cambios de stock (used_capacity, wine_volume_liters) NO,
 * porque ya quedan registrados en container_histories vía los services.
 */
class ContainerAuditTest extends TestCase
{
    use RefreshDatabase;

    private function actingWinery(): User
    {
        $user = User::factory()->create(['role' => 'winery']);
        $this->actingAs($user);

        return $user;
    }

    public function test_creating_container_writes_audit_log(): void
    {
        $user = $this->actingWinery();

        $container = Container::factory()->create([
            'user_id'  => $user->id,
            'capacity' => 1000.0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Container::class,
            'auditable_id'   => $container->id,
            'event'          => 'created',
            'user_id'        => $user->id,
        ]);
    }

    public function test_updating_metadata_is_audited(): void
    {
        $user      = $this->actingWinery();
        $container = Container::factory()->create([
            'user_id'  => $user->id,
            'name'     => 'Tanque A',
            'capacity' => 1000.0,
        ]);
        AuditLog::where('event', 'created')->delete();

        $container->update(['name' => 'Tanque B', 'capacity' => 1500.0]);

        $log = AuditLog::where('event', 'updated')
            ->where('auditable_id', $container->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->new_values);
        $this->assertArrayHasKey('capacity', $log->new_values);
        $this->assertEquals('Tanque A', $log->old_values['name']);
        $this->assertEquals('Tanque B', $log->new_values['name']);
    }

    public function test_archiving_is_audited(): void
    {
        $user      = $this->actingWinery();
        $container = Container::factory()->create(['user_id' => $user->id, 'archived' => false]);
        AuditLog::where('auditable_id', $container->id)->delete();

        $container->update(['archived' => true]);

        $log = AuditLog::where('event', 'updated')
            ->where('auditable_id', $container->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('archived', $log->new_values);
    }

    public function test_stock_changes_are_NOT_audited(): void
    {
        $user      = $this->actingWinery();
        $container = Container::factory()->create([
            'user_id'       => $user->id,
            'capacity'      => 1000.0,
            'used_capacity' => 0.0,
        ]);
        AuditLog::where('auditable_id', $container->id)->delete();

        // Movimiento de stock: NO debe generar audit log de configuración.
        $container->incrementUsedCapacity(200.0);
        $container->decrementUsedCapacity(50.0);

        $this->assertDatabaseMissing('audit_logs', [
            'auditable_type' => Container::class,
            'auditable_id'   => $container->id,
            'event'          => 'updated',
        ]);
    }

    public function test_container_room_metadata_is_audited(): void
    {
        $user = $this->actingWinery();
        $room = ContainerRoom::create([
            'user_id'     => $user->id,
            'name'        => 'Sala Barricas',
            'temperature' => 14.0,
        ]);
        AuditLog::where('event', 'created')->delete();

        $room->update(['temperature' => 16.0]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => ContainerRoom::class,
            'auditable_id'   => $room->id,
            'event'          => 'updated',
        ]);
    }
}
