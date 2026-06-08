<?php

namespace Tests\Feature\Viticulturist\HarvestDeclarations;

use App\Livewire\Viticulturist\HarvestDeclarations\Index;
use App\Models\Campaign;
use App\Models\HarvestDeclaration;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_declarations(): void
    {
        $v = $this->makeViticulturist();
        $campaign = Campaign::getOrCreateActiveForYear($v->id);
        $this->makeDeclarationForCampaign($v->id, $campaign->id, 'Junta-Visible-2026');
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertSee('Junta-Visible-2026');
    }

    public function test_delete_removes_draft_declaration(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('delete', $declaration->id);

        $this->assertDatabaseMissing('harvest_declarations', ['id' => $declaration->id]);
    }

    public function test_cannot_delete_submitted_declaration(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id, 'Test', 'submitted');
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('delete', $declaration->id);

        $this->assertDatabaseHas('harvest_declarations', ['id' => $declaration->id]);
    }

    public function test_mark_submitted_changes_status(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('markSubmitted', $declaration->id);

        $this->assertDatabaseHas('harvest_declarations', [
            'id' => $declaration->id,
            'status' => 'submitted',
        ]);
    }

    public function test_mark_accepted_changes_status(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id, 'Test', 'submitted');
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('markAccepted', $declaration->id);

        $this->assertDatabaseHas('harvest_declarations', [
            'id' => $declaration->id,
            'status' => 'accepted',
        ]);
    }

    public function test_cannot_delete_other_viticulturists_declaration(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $declaration->id);
    }

    private function makeDeclaration(int $viticulturistId, string $authority = 'Junta de Andalucía', string $status = 'draft'): HarvestDeclaration
    {
        $campaign = Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year' => 2024,
            'name' => 'Campaña 2024',
            'active' => true,
        ]);

        return HarvestDeclaration::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaign->id,
            'declaration_year' => 2024,
            'declaration_date' => '2024-11-15',
            'authority' => $authority,
            'status' => $status,
            'active' => true,
        ]);
    }

    private function makeDeclarationForCampaign(int $viticulturistId, int $campaignId, string $authority = 'Junta de Andalucía'): HarvestDeclaration
    {
        return HarvestDeclaration::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaignId,
            'declaration_year' => now()->year,
            'declaration_date' => now()->format('Y-m-d'),
            'authority' => $authority,
            'status' => 'draft',
            'active' => true,
        ]);
    }
}
