<?php

namespace Tests\Feature\Viticulturist\PhytosanitaryAlerts;

use App\Livewire\Viticulturist\PhytosanitaryAlerts\Edit;
use App\Models\PhytosanitaryAlert;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['phytosanitaryAlert' => $alert])
            ->assertSet('title', 'Alerta Test')
            ->assertSet('alert_type', 'enfermedad')
            ->assertSet('severity', 'alta')
            ->assertSet('alert_date', '2024-06-15');
    }

    public function test_can_update_alert(): void
    {
        $viticulturist = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['phytosanitaryAlert' => $alert])
            ->set('title', 'Alerta Actualizada')
            ->set('severity', 'critica')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.phytosanitary-alerts.index'));

        $this->assertDatabaseHas('phytosanitary_alerts', [
            'id' => $alert->id,
            'title' => 'Alerta Actualizada',
            'severity' => 'critica',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['phytosanitaryAlert' => $alert])
            ->set('title', '')
            ->set('description', '')
            ->set('alert_date', '')
            ->call('save')
            ->assertHasErrors(['title', 'description', 'alert_date']);
    }

    public function test_cannot_edit_other_viticulturists_alert(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $alert = $this->makeAlert($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.phytosanitary-alerts.edit', $alert))
            ->assertStatus(403);
    }

    private function makeAlert(int $viticulturistId): PhytosanitaryAlert
    {
        return PhytosanitaryAlert::create([
            'viticulturist_id' => $viticulturistId,
            'title' => 'Alerta Test',
            'source' => 'otro',
            'alert_type' => 'enfermedad',
            'severity' => 'alta',
            'description' => 'Descripción de prueba',
            'alert_date' => '2024-06-15',
            'active' => true,
        ]);
    }
}
