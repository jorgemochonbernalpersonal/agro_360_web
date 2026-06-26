<?php

namespace Tests\Feature\Winery\FermentationControls;

use App\Livewire\Winery\FermentationControls\Create;
use App\Livewire\Winery\FermentationControls\Edit;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CreateEditTest extends WineryTestCase
{
    private \App\Models\User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    // --- Create ---

    public function test_create_renders(): void
    {
        $this->get(route('winery.fermentation-controls.create'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('wine_id', '')
            ->set('container_id', '')
            ->set('control_date', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'container_id', 'control_date']);
    }

    public function test_create_saves_control(): void
    {
        $wine = $this->makeWine();
        $container = $this->makeContainer();

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('container_id', (string) $container->id)
            ->set('control_date', now()->format('Y-m-d\TH:i'))
            ->set('temperature', '18.5')
            ->set('ph', '3.4')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_fermentation_controls', [
            'wine_id' => $wine->id,
            'container_id' => $container->id,
        ]);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = $this->makeWine($this->makeOtherWinery()->id);
        $container = $this->makeContainer();

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('container_id', (string) $container->id)
            ->set('control_date', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_create_rejects_container_from_other_winery(): void
    {
        $wine = $this->makeWine();
        $otherContainer = $this->makeContainer($this->makeOtherWinery()->id);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('container_id', (string) $otherContainer->id)
            ->set('control_date', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['container_id']);
    }

    // --- Edit ---

    public function test_edit_renders(): void
    {
        $control = $this->makeControl($this->makeWine());
        $this->get(route('winery.fermentation-controls.edit', $control))->assertOk();
    }

    public function test_other_winery_cannot_access_edit(): void
    {
        $control = $this->makeControl($this->makeWine());

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.fermentation-controls.edit', $control))
            ->assertForbidden();
    }

    public function test_edit_saves_changes(): void
    {
        $control = $this->makeControl($this->makeWine());

        Livewire::test(Edit::class, ['control' => $control])
            ->set('temperature', '22.0')
            ->set('ph', '3.6')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_fermentation_controls', [
            'id' => $control->id,
            'temperature' => '22.0',
        ]);
    }

    private function makeWine(?int $userId = null): Wine
    {
        return Wine::create([
            'user_id' => $userId ?? $this->winery->id,
            'name' => 'Vino Test',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
    }

    private function makeContainer(?int $userId = null): Container
    {
        return Container::create([
            'user_id' => $userId ?? $this->winery->id,
            'name' => 'Depósito Test',
            'capacity' => 1000,
        ]);
    }

    private function makeControl(Wine $wine): WineFermentationControl
    {
        return WineFermentationControl::create([
            'wine_id' => $wine->id,
            'container_id' => $this->makeContainer($wine->user_id)->id,
            'control_date' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
