<?php

namespace Tests\Feature\Viticulturist\Pac\Declarations;

use App\Livewire\Viticulturist\Pac\Declarations\Index;
use App\Models\PacDeclaration;
use App\Models\PacDeclarationItem;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_declarations(): void
    {
        $v = $this->makeViticulturist();
        $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->assertSee((string) now()->year);
    }

    public function test_delete_removes_draft_declaration(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('delete', $declaration->id);

        $this->assertDatabaseMissing('pac_declarations', ['id' => $declaration->id]);
    }

    public function test_cannot_delete_submitted_declaration(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id, 'submitted');
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('delete', $declaration->id);

        $this->assertDatabaseHas('pac_declarations', ['id' => $declaration->id]);
    }

    public function test_submit_changes_status(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->makeItem($declaration, $v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('submit', $declaration->id);

        $this->assertDatabaseHas('pac_declarations', [
            'id' => $declaration->id,
            'status' => 'submitted',
        ]);
    }

    public function test_submit_fails_without_items(): void
    {
        $v = $this->makeViticulturist();
        $declaration = $this->makeDeclaration($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('submit', $declaration->id);

        $this->assertDatabaseHas('pac_declarations', [
            'id' => $declaration->id,
            'status' => 'draft',
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

    private function makeDeclaration(int $viticulturistId, string $status = 'draft'): PacDeclaration
    {
        return PacDeclaration::create([
            'viticulturist_id' => $viticulturistId,
            'year' => now()->year,
            'status' => $status,
            'total_declared_area' => 0,
            'total_eligible_area' => 0,
        ]);
    }

    private function makeItem(PacDeclaration $declaration, int $viticulturistId): PacDeclarationItem
    {
        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturistId, 'area' => 2.5]);

        return PacDeclarationItem::create([
            'declaration_id' => $declaration->id,
            'plot_id' => $plot->id,
            'declared_area' => 2.500,
            'eligible_area' => 2.000,
        ]);
    }
}
