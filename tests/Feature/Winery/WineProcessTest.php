<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\Wines\Process\Create;
use App\Livewire\Winery\Wines\Process\Edit;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Tests for the Winery Wine Process (vinification operations) module.
 *
 * Covers: Create (happy path, validation, authorization),
 *         Edit (happy path, cross-wine guard, authorization).
 */
class WineProcessTest extends WineryTestCase
{
    private User $winery;

    private Wine $wine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);

        $this->wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Vino Test',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
    }

    // ── Create — happy path ───────────────────────────────────────────────────

    public function test_can_create_process_detail(): void
    {
        Livewire::test(Create::class, ['wine' => $this->wine])
            ->set('process_type', 'fermentation')
            ->set('start_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_process_details', [
            'wine_id' => $this->wine->id,
            'process_type' => 'fermentation',
        ]);
    }

    public function test_can_create_process_with_optional_container(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Create::class, ['wine' => $this->wine])
            ->set('process_type', 'aging')
            ->set('start_date', now()->toDateString())
            ->set('container_id', (string) $container->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_process_details', [
            'wine_id' => $this->wine->id,
            'process_type' => 'aging',
            'container_id' => $container->id,
        ]);
    }

    // ── Create — validation ───────────────────────────────────────────────────

    public function test_create_requires_process_type(): void
    {
        Livewire::test(Create::class, ['wine' => $this->wine])
            ->set('process_type', '')
            ->set('start_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['process_type']);
    }

    public function test_create_requires_start_date(): void
    {
        Livewire::test(Create::class, ['wine' => $this->wine])
            ->set('process_type', 'pressing')
            ->set('start_date', '')
            ->call('save')
            ->assertHasErrors(['start_date']);
    }

    public function test_create_rejects_invalid_process_type(): void
    {
        Livewire::test(Create::class, ['wine' => $this->wine])
            ->set('process_type', 'invalid_operation')
            ->set('start_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['process_type']);
    }

    public function test_create_end_date_must_be_after_start_date(): void
    {
        Livewire::test(Create::class, ['wine' => $this->wine])
            ->set('process_type', 'fermentation')
            ->set('start_date', '2024-09-15')
            ->set('end_date', '2024-09-10') // before start_date
            ->call('save')
            ->assertHasErrors(['end_date']);
    }

    // ── Create — authorization ────────────────────────────────────────────────

    public function test_other_winery_cannot_create_process_for_wine(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.wines.process.create', $this->wine))
            ->assertForbidden();
    }

    // ── Edit — happy path ─────────────────────────────────────────────────────

    public function test_can_edit_process_detail(): void
    {
        $process = $this->makeProcess(['process_type' => 'pressing']);

        Livewire::test(Edit::class, ['wine' => $this->wine, 'process' => $process])
            ->set('process_type', 'settling')
            ->set('start_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_process_details', [
            'id' => $process->id,
            'process_type' => 'settling',
        ]);
    }

    public function test_edit_can_set_end_date(): void
    {
        $process = $this->makeProcess();

        Livewire::test(Edit::class, ['wine' => $this->wine, 'process' => $process])
            ->set('process_type', 'fermentation')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2024-09-20')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_process_details', [
            'id' => $process->id,
        ]);
    }

    // ── Edit — authorization ──────────────────────────────────────────────────

    public function test_other_winery_cannot_edit_process(): void
    {
        $process = $this->makeProcess();

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.wines.process.edit', [$this->wine, $process]))
            ->assertForbidden();
    }

    public function test_process_of_wrong_wine_returns_404(): void
    {
        $otherWine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Otro Vino',
            'wine_type' => 'white',
            'status' => 'in_progress',
        ]);

        $process = $this->makeProcess(); // belongs to $this->wine

        // Mounting Edit with mismatched wine/process should abort 404
        Livewire::test(Edit::class, ['wine' => $otherWine, 'process' => $process])
            ->assertStatus(404);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeProcess(array $attrs = []): WineProcessDetail
    {
        return WineProcessDetail::create(array_merge([
            'wine_id' => $this->wine->id,
            'process_type' => 'fermentation',
            'start_date' => now()->toDateString(),
            'created_by' => $this->winery->id,
        ], $attrs));
    }

    private function makeContainer(): Container
    {
        $type = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);

        return Container::create([
            'user_id' => $this->winery->id,
            'name' => 'Cuba Process',
            'type_id' => $type->id,
            'capacity' => 5000,
            'unit' => 'litros',
            'used_capacity' => 0,
            'archived' => false,
        ]);
    }
}
