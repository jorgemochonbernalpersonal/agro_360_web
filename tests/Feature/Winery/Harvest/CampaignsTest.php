<?php

namespace Tests\Feature\Winery\Harvest;

use App\Livewire\Winery\Harvest\Campaigns\Create;
use App\Livewire\Winery\Harvest\Campaigns\Edit;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class CampaignsTest extends WineryTestCase
{
    protected User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders_own_campaigns(): void
    {
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $this->winery->id,
            'year'             => 2024,
            'name'             => 'Vendimia 2024',
        ]);

        $this->get(route('winery.campaigns.index'))
            ->assertOk()
            ->assertSee('Vendimia 2024');
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('year', '')
            ->set('start_date', '')
            ->set('end_date', '')
            ->call('save')
            ->assertHasErrors(['name', 'year', 'start_date', 'end_date']);
    }

    public function test_create_saves_campaign(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Vendimia 2023')
            ->set('year', '2023')
            ->set('start_date', '2023-08-01')
            ->set('end_date', '2023-11-30')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('campaigns', [
            'viticulturist_id' => $this->winery->id,
            'name'             => 'Vendimia 2023',
            'year'             => 2023,
        ]);
    }

    public function test_edit_saves_campaign(): void
    {
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $this->winery->id,
            'year'             => 2022,
            'name'             => 'Vendimia 2022',
            'locked_at'        => null,
        ]);

        Livewire::test(Edit::class, ['campaign' => $campaign])
            ->set('name', 'Vendimia 2022 Actualizada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('campaigns', [
            'id'   => $campaign->id,
            'name' => 'Vendimia 2022 Actualizada',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $this->winery->id,
            'year'             => 2021,
            'locked_at'        => null,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.campaigns.edit', $campaign))
            ->assertForbidden();
    }
}
