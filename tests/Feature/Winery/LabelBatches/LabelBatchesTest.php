<?php

namespace Tests\Feature\Winery\LabelBatches;

use App\Livewire\Winery\LabelBatches\Create;
use App\Livewire\Winery\LabelBatches\Edit;
use App\Models\LabelBatch;
use App\Models\User;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class LabelBatchesTest extends WineryTestCase
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
        $this->get(route('winery.label-batches.index'))
            ->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('source', '')
            ->set('start_number', '')
            ->set('end_number', '')
            ->call('save')
            ->assertHasErrors(['name', 'source', 'start_number', 'end_number']);
    }

    public function test_create_saves_label_batch(): void
    {
        $wine = Wine::create([
            'user_id'   => $this->winery->id,
            'name'      => 'Test Wine',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Lote Etiquetas A')
            ->set('source', 'own')
            ->set('start_number', 1)
            ->set('end_number', 1000)
            ->set('wine_id', (string) $wine->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('label_batches', [
            'user_id'        => $this->winery->id,
            'name'           => 'Lote Etiquetas A',
            'total_quantity' => 1000,
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $wine = Wine::create([
            'user_id'   => $this->winery->id,
            'name'      => 'Test Wine',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        $batch = LabelBatch::create([
            'user_id'         => $this->winery->id,
            'wine_id'         => $wine->id,
            'name'            => 'Old Name',
            'source'          => 'own',
            'start_number'    => 1,
            'end_number'      => 500,
            'total_quantity'  => 500,
            'used_quantity'   => 0,
            'wasted_quantity' => 0,
        ]);

        Livewire::test(Edit::class, ['batch' => $batch])
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('label_batches', [
            'id'   => $batch->id,
            'name' => 'New Name',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $batch = LabelBatch::create([
            'user_id'         => $this->winery->id,
            'name'            => 'My Batch',
            'source'          => 'own',
            'start_number'    => 1,
            'end_number'      => 200,
            'total_quantity'  => 200,
            'used_quantity'   => 0,
            'wasted_quantity' => 0,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.label-batches.edit', $batch))
            ->assertForbidden();
    }
}
