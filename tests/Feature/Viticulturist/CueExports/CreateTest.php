<?php

namespace Tests\Feature\Viticulturist\CueExports;

use App\Livewire\Viticulturist\CueExports\Create;
use App\Models\CueExport;
use App\Models\Exploitation;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    private function makeExploitation(int $viticulturistId): Exploitation
    {
        return Exploitation::create([
            'viticulturist_id'         => $viticulturistId,
            'exploitation_name'        => 'Explotación Test',
            'holder_name'              => 'Test Holder',
            'holder_nif'               => '12345678A',
            'is_ecological'            => false,
            'is_integrated_production' => false,
            'is_quality_scheme'        => false,
            'active'                   => true,
        ]);
    }

    public function test_can_create_cue_export(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('exploitation_id', (string) $exploitation->id)
            ->set('campaign_year', 2024)
            ->set('period_type', 'annual')
            ->set('from_date', '2024-01-01')
            ->set('to_date', '2024-12-31')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.cue-exports.index'));

        $this->assertDatabaseHas('cue_exports', [
            'viticulturist_id' => $viticulturist->id,
            'exploitation_id'  => $exploitation->id,
            'campaign_year'    => 2024,
            'status'           => 'draft',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('exploitation_id', '')
            ->set('from_date', '')
            ->call('save')
            ->assertHasErrors(['exploitation_id', 'from_date']);
    }

    public function test_to_date_must_be_after_from_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('exploitation_id', (string) $exploitation->id)
            ->set('from_date', '2024-06-01')
            ->set('to_date', '2024-05-01')
            ->call('save')
            ->assertHasErrors(['to_date']);
    }

    public function test_exploitation_must_belong_to_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $otherExp      = $this->makeExploitation($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Create::class)
            ->set('exploitation_id', (string) $otherExp->id)
            ->set('campaign_year', 2024)
            ->set('from_date', '2024-01-01')
            ->set('to_date', '2024-12-31')
            ->call('save');
    }

    public function test_period_type_updates_dates(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        $currentYear = now()->year;

        Livewire::test(Create::class)
            ->set('campaign_year', $currentYear)
            ->set('period_type', 'annual')
            ->assertSet('from_date', "{$currentYear}-01-01")
            ->assertSet('to_date', "{$currentYear}-12-31");
    }
}
