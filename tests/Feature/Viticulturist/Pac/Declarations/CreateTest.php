<?php

namespace Tests\Feature\Viticulturist\Pac\Declarations;

use App\Livewire\Viticulturist\Pac\Declarations\Create;
use App\Models\PacDeclaration;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_pac_declaration(): void
    {
        $v = $this->makeViticulturist();
        $plot = Plot::factory()->forViticulturist($v)->create(['area' => 2.500]);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set("items.{$plot->id}.selected", true)
            ->set("items.{$plot->id}.declared_area", '2.500')
            ->set("items.{$plot->id}.eligible_area", '2.000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.pac.declarations.index'));

        $this->assertDatabaseHas('pac_declarations', [
            'viticulturist_id' => $v->id,
            'year' => now()->year,
            'status' => 'draft',
        ]);
    }

    public function test_save_error_when_no_plot_selected(): void
    {
        $v = $this->makeViticulturist();
        Plot::factory()->forViticulturist($v)->create(['area' => 2.5]);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['items']);
    }

    public function test_mount_redirects_if_declaration_exists_for_current_year(): void
    {
        $v = $this->makeViticulturist();
        PacDeclaration::create([
            'viticulturist_id' => $v->id,
            'year' => now()->year,
            'status' => 'draft',
            'total_declared_area' => 0,
            'total_eligible_area' => 0,
        ]);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertRedirect(route('viticulturist.pac.declarations.index'));
    }

    public function test_can_submit_declaration_directly(): void
    {
        $v = $this->makeViticulturist();
        $plot = Plot::factory()->forViticulturist($v)->create(['area' => 3.0]);
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set("items.{$plot->id}.selected", true)
            ->set("items.{$plot->id}.declared_area", '3.000')
            ->set("items.{$plot->id}.eligible_area", '2.800')
            ->call('save', 'submitted')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pac_declarations', [
            'viticulturist_id' => $v->id,
            'status' => 'submitted',
        ]);
    }
}
