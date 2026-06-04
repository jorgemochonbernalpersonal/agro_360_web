<?php

namespace Tests\Feature\Viticulturist\FieldApplicators;

use App\Livewire\Viticulturist\FieldApplicators\Index;
use App\Models\FieldApplicator;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_applicators(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id, 'Mi Aplicador');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Mi Aplicador');
    }

    public function test_deactivate_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $applicator->id);

        $this->assertDatabaseHas('field_applicators', [
            'id' => $applicator->id,
            'active' => false,
        ]);
    }

    public function test_deactivated_applicator_disappears_from_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id, 'Aplicador a Dar de Baja');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $applicator->id)
            ->assertDontSee('Aplicador a Dar de Baja');
    }

    public function test_cannot_deactivate_other_viticulturists_applicator(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('deactivate', $applicator->id);
    }

    public function test_applicators_from_other_viticulturist_not_visible(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $applicator = $this->makeApplicator($viticulturist->id, 'Aplicador Ajeno');

        $this->actingAs($other);

        Livewire::test(Index::class)
            ->assertDontSee('Aplicador Ajeno');
    }

    private function makeApplicator(int $viticulturistId, string $name = 'Aplicador Test'): FieldApplicator
    {
        return FieldApplicator::create([
            'viticulturist_id' => $viticulturistId,
            'name' => $name,
            'ropo_number' => 'ROPO-'.uniqid(),
            'ropo_category' => 'qualified',
            'is_advisor' => false,
            'active' => true,
        ]);
    }
}
