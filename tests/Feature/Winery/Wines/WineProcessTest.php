<?php

namespace Tests\Feature\Winery\Wines;

use App\Livewire\Winery\Wines\Process\Create;
use App\Livewire\Winery\Wines\Process\Edit;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class WineProcessTest extends WineryTestCase
{
    private \App\Models\User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    private function makeWine(?int $userId = null): Wine
    {
        return Wine::create([
            'user_id' => $userId ?? $this->winery->id,
            'name' => 'Test Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);
    }

    private function makeProcess(Wine $wine): WineProcessDetail
    {
        return WineProcessDetail::create([
            'wine_id' => $wine->id,
            'process_type' => 'fermentation',
            'start_date' => now()->toDateString(),
            'created_by' => $wine->user_id,
        ]);
    }

    // --- Create ---

    public function test_create_renders(): void
    {
        $wine = $this->makeWine();
        $this->get(route('winery.wines.process.create', $wine))->assertOk();
    }

    public function test_other_winery_cannot_access_create(): void
    {
        $wine = $this->makeWine();
        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.wines.process.create', $wine))
            ->assertForbidden();
    }

    public function test_create_validates_required_fields(): void
    {
        $wine = $this->makeWine();
        Livewire::test(Create::class, ['wine' => $wine])
            ->set('process_type', '')
            ->set('start_date', '')
            ->call('save')
            ->assertHasErrors(['process_type', 'start_date']);
    }

    public function test_create_saves_process(): void
    {
        $wine = $this->makeWine();
        Livewire::test(Create::class, ['wine' => $wine])
            ->set('process_type', 'fermentation')
            ->set('start_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_process_details', [
            'wine_id' => $wine->id,
            'process_type' => 'fermentation',
        ]);
    }

    // --- Edit ---

    public function test_edit_renders(): void
    {
        $wine = $this->makeWine();
        $process = $this->makeProcess($wine);
        $this->get(route('winery.wines.process.edit', [$wine, $process]))->assertOk();
    }

    public function test_other_winery_cannot_access_edit(): void
    {
        $wine = $this->makeWine();
        $process = $this->makeProcess($wine);
        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.wines.process.edit', [$wine, $process]))
            ->assertForbidden();
    }

    public function test_edit_process_from_different_wine_returns_404(): void
    {
        $wine = $this->makeWine();
        $otherWine = $this->makeWine();
        $process = $this->makeProcess($otherWine);

        $this->get(route('winery.wines.process.edit', [$wine, $process]))
            ->assertNotFound();
    }

    public function test_edit_saves_changes(): void
    {
        $wine = $this->makeWine();
        $process = $this->makeProcess($wine);

        Livewire::test(Edit::class, ['wine' => $wine, 'process' => $process])
            ->set('process_type', 'aging')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_process_details', [
            'id' => $process->id,
            'process_type' => 'aging',
        ]);
    }
}
