<?php

namespace Tests\Feature\Winery\CellarOperations;

use App\Livewire\Winery\CellarOperations\Create;
use App\Livewire\Winery\CellarOperations\Edit;
use App\Models\CellarOperation;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CellarOperationsTest extends WineryTestCase
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
        $this->get(route('winery.cellar-operations.index'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('operation_type', '')
            ->call('save')
            ->assertHasErrors(['operation_type']);
    }

    public function test_create_saves_operation(): void
    {
        $firstType = array_key_first(CellarOperation::OPERATION_TYPES);

        Livewire::test(Create::class)
            ->set('operation_type', $firstType)
            ->set('operation_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cellar_operations', [
            'user_id' => $this->winery->id,
            'operation_type' => $firstType,
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $operation = CellarOperation::create([
            'user_id' => $this->winery->id,
            'operation_type' => array_key_first(CellarOperation::OPERATION_TYPES),
            'operation_date' => now()->toDateString(),
            'status' => array_key_first(CellarOperation::STATUSES),
        ]);

        $newType = array_key_last(CellarOperation::OPERATION_TYPES);

        Livewire::test(Edit::class, ['operation' => $operation])
            ->set('operation_type', $newType)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cellar_operations', [
            'id' => $operation->id,
            'operation_type' => $newType,
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $operation = CellarOperation::create([
            'user_id' => $this->winery->id,
            'operation_type' => array_key_first(CellarOperation::OPERATION_TYPES),
            'operation_date' => now()->toDateString(),
            'status' => array_key_first(CellarOperation::STATUSES),
        ]);

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.cellar-operations.edit', $operation))
            ->assertForbidden();
    }
}
