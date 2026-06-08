<?php

namespace Tests\Feature\Viticulturist\PhytosanitaryAlerts;

use App\Livewire\Viticulturist\PhytosanitaryAlerts\Index;
use App\Models\PhytosanitaryAlert;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_alerts(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeAlert($viticulturist->id, true, 'Alerta-Visible-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Alerta-Visible-001');
    }

    public function test_archive_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('archive', $alert->id);

        $this->assertDatabaseHas('phytosanitary_alerts', [
            'id' => $alert->id,
            'active' => false,
        ]);
    }

    public function test_unarchive_restores_alert(): void
    {
        $viticulturist = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id, false);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('unarchive', $alert->id);

        $this->assertDatabaseHas('phytosanitary_alerts', [
            'id' => $alert->id,
            'active' => true,
        ]);
    }

    public function test_delete_removes_alert(): void
    {
        $viticulturist = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $alert->id);

        $this->assertDatabaseMissing('phytosanitary_alerts', ['id' => $alert->id]);
    }

    public function test_archived_tab_shows_inactive_alerts(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeAlert($viticulturist->id, false, 'Alerta-Archivada-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->assertSee('Alerta-Archivada-001');
    }

    public function test_cannot_archive_other_viticulturists_alert(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('archive', $alert->id);
    }

    private function makeAlert(int $viticulturistId, bool $active = true, string $title = 'Alerta Test'): PhytosanitaryAlert
    {
        return PhytosanitaryAlert::create([
            'viticulturist_id' => $viticulturistId,
            'title' => $title,
            'source' => 'otro',
            'alert_type' => 'enfermedad',
            'severity' => 'media',
            'description' => 'Descripción de prueba',
            'alert_date' => '2024-06-15',
            'active' => $active,
        ]);
    }
}
