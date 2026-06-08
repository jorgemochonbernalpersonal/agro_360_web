<?php

namespace Tests\Feature\Viticulturist\Pac\Declarations;

use App\Livewire\Viticulturist\Pac\Declarations\Edit;
use App\Models\PacDeclaration;
use App\Models\PacDeclarationItem;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_existing_items(): void
    {
        $v = $this->makeViticulturist();
        [$declaration, $plot] = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        $component = Livewire::test(Edit::class, ['declaration' => $declaration]);

        $items = $component->get('items');
        $this->assertTrue($items[$plot->id]['selected']);
        $this->assertEquals('2.500', $items[$plot->id]['declared_area']);
    }

    public function test_can_update_declaration(): void
    {
        $v = $this->makeViticulturist();
        [$declaration, $plot] = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['declaration' => $declaration])
            ->set("items.{$plot->id}.selected", true)
            ->set("items.{$plot->id}.declared_area", '1.800')
            ->set("items.{$plot->id}.eligible_area", '1.500')
            ->set('reference_number', 'REF-2026-001')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pac_declarations', [
            'id' => $declaration->id,
            'reference_number' => 'REF-2026-001',
        ]);
    }

    public function test_cannot_edit_other_viticulturists_declaration(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        [$declaration] = $this->makeDeclaration($v->id);

        $this->actingAs($other)
            ->get(route('viticulturist.pac.declarations.edit', $declaration))
            ->assertStatus(403);
    }

    public function test_non_draft_redirects_to_show(): void
    {
        $v = $this->makeViticulturist();
        [$declaration, $plot] = $this->makeDeclaration($v->id, 'submitted');
        $this->actingAs($v);

        Livewire::test(Edit::class, ['declaration' => $declaration])
            ->assertRedirect(route('viticulturist.pac.declarations.show', $declaration));
    }

    private function makeDeclaration(int $viticulturistId, string $status = 'draft'): array
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturistId, 'area' => 2.5]);

        $declaration = PacDeclaration::create([
            'viticulturist_id' => $viticulturistId,
            'year' => now()->year,
            'status' => $status,
            'total_declared_area' => 2.5,
            'total_eligible_area' => 2.0,
        ]);

        PacDeclarationItem::create([
            'declaration_id' => $declaration->id,
            'plot_id' => $plot->id,
            'declared_area' => 2.500,
            'eligible_area' => 2.000,
        ]);

        return [$declaration, $plot];
    }
}
