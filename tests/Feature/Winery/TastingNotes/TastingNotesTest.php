<?php

namespace Tests\Feature\Winery\TastingNotes;

use App\Livewire\Winery\TastingNotes\Create;
use App\Models\User;
use App\Models\Wine;
use App\Models\WineTastingNote;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class TastingNotesTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.tasting-notes.index'))
            ->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('evaluation_date', '')
            ->call('save')
            ->assertHasErrors(['wine_id', 'evaluation_date']);
    }

    public function test_create_saves_tasting_note(): void
    {
        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Test Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $wine->id)
            ->set('evaluation_date', today()->toDateString())
            ->set('evaluator_name', 'Expert')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wine_tasting_notes', [
            'user_id' => $this->winery->id,
            'wine_id' => $wine->id,
            'evaluator_name' => 'Expert',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $wine = Wine::create([
            'user_id' => $this->winery->id,
            'name' => 'Test Wine',
            'wine_type' => 'red',
            'status' => 'in_progress',
        ]);

        $tastingNote = WineTastingNote::create([
            'user_id' => $this->winery->id,
            'wine_id' => $wine->id,
            'evaluation_date' => today(),
            'evaluator_name' => 'Test',
            'created_by' => $this->winery->id,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.tasting-notes.edit', $tastingNote))
            ->assertForbidden();
    }
}
