<?php

namespace Tests\Feature\Viticulturist\HarvestByproducts;

use App\Livewire\Viticulturist\HarvestByproducts\Index;
use App\Models\Campaign;
use App\Models\HarvestByproduct;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_byproducts(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->makeByproduct($v->id, $c->id, true, ['destination_name' => 'Destino-Visible-001']);

        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertSee('Destino-Visible-001');
    }

    public function test_archive_sets_active_false(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id);

        $this->actingAs($v);

        Livewire::test(Index::class)->call('archive', $byproduct->id);

        $this->assertDatabaseHas('harvest_byproducts', ['id' => $byproduct->id, 'active' => false]);
    }

    public function test_unarchive_restores_record(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id, false);

        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('unarchive', $byproduct->id);

        $this->assertDatabaseHas('harvest_byproducts', ['id' => $byproduct->id, 'active' => true]);
    }

    public function test_archived_tab_shows_inactive_records(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->makeByproduct($v->id, $c->id, false, ['destination_name' => 'Destino-Archivado-001']);

        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->assertSee('Destino-Archivado-001');
    }

    public function test_delete_removes_archived_record(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id, false);

        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('delete', $byproduct->id);

        $this->assertDatabaseMissing('harvest_byproducts', ['id' => $byproduct->id]);
    }

    public function test_cannot_archive_other_viticulturists_record(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $byproduct = $this->makeByproduct($v->id, $c->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('archive', $byproduct->id);
    }

    public function test_active_tab_does_not_show_archived_records(): void
    {
        $v = $this->makeViticulturist();
        $c = Campaign::getOrCreateActiveForYear($v->id);
        $this->makeByproduct($v->id, $c->id, false, ['destination_name' => 'Solo-Archivado-XYZ']);

        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertDontSee('Solo-Archivado-XYZ');
    }

    private function makeByproduct(int $viticulturistId, int $campaignId, bool $active = true, array $overrides = []): HarvestByproduct
    {
        return HarvestByproduct::create(array_merge([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaignId,
            'date' => '2024-10-01',
            'byproduct_type' => 'pomace',
            'quantity_kg' => 1500.000,
            'destination_type' => 'cooperative',
            'destination_name' => 'Cooperativa Test',
            'active' => $active,
        ], $overrides));
    }
}
