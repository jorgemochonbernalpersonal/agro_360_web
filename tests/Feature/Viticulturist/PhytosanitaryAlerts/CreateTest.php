<?php

namespace Tests\Feature\Viticulturist\PhytosanitaryAlerts;

use App\Livewire\Viticulturist\PhytosanitaryAlerts\Create;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_alert(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('title', 'Alerta Mildiu')
            ->set('source', 'otro')
            ->set('alert_type', 'enfermedad')
            ->set('severity', 'alta')
            ->set('description', 'Detección de mildiu en parcelas del norte')
            ->set('alert_date', '2024-06-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.phytosanitary-alerts.index'));

        $this->assertDatabaseHas('phytosanitary_alerts', [
            'viticulturist_id' => $viticulturist->id,
            'title' => 'Alerta Mildiu',
            'alert_type' => 'enfermedad',
            'severity' => 'alta',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('title', '')
            ->set('description', '')
            ->set('alert_date', '')
            ->call('save')
            ->assertHasErrors(['title', 'description', 'alert_date']);
    }

    public function test_expiry_date_must_be_after_alert_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('alert_date', '2024-06-15')
            ->set('expiry_date', '2024-06-10')
            ->set('title', 'Test')
            ->set('description', 'Test description')
            ->call('save')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_mount_sets_today_as_alert_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('alert_date', now()->format('Y-m-d'));
    }
}
