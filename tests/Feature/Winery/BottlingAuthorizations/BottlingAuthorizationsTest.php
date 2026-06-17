<?php

namespace Tests\Feature\Winery\BottlingAuthorizations;

use App\Livewire\Winery\BottlingAuthorizations\Create;
use App\Livewire\Winery\BottlingAuthorizations\Edit;
use App\Livewire\Winery\BottlingAuthorizations\Index;
use App\Models\BottlingAuthorization;
use App\Models\User;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class BottlingAuthorizationsTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_winery_can_view_index(): void
    {
        $this->get(route('winery.bottling-authorizations.index'))->assertOk();
    }

    public function test_guest_cannot_access(): void
    {
        $this->app['auth']->guard()->logout();

        $this->get(route('winery.bottling-authorizations.index'))->assertRedirect();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('authorization_number', '')
            ->set('authorization_type', '')
            ->set('status', '')
            ->call('save')
            ->assertHasErrors(['authorization_number', 'authorization_type', 'status']);
    }

    public function test_winery_can_create_bottling_authorization(): void
    {
        $firstType = array_key_first(BottlingAuthorization::AUTHORIZATION_TYPES);
        $firstStatus = array_key_first(BottlingAuthorization::STATUSES);

        Livewire::test(Create::class)
            ->set('authorization_number', 'AUTH-2026-001')
            ->set('authorization_type', $firstType)
            ->set('status', $firstStatus)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bottling_authorizations', [
            'user_id' => $this->winery->id,
            'authorization_number' => 'AUTH-2026-001',
            'authorization_type' => $firstType,
            'status' => $firstStatus,
        ]);
    }

    public function test_winery_can_edit_bottling_authorization(): void
    {
        $firstType = array_key_first(BottlingAuthorization::AUTHORIZATION_TYPES);
        $firstStatus = array_key_first(BottlingAuthorization::STATUSES);

        $authorization = BottlingAuthorization::create([
            'user_id' => $this->winery->id,
            'authorization_number' => 'AUTH-OLD',
            'authorization_type' => $firstType,
            'status' => $firstStatus,
        ]);

        Livewire::test(Edit::class, ['bottlingAuthorization' => $authorization])
            ->set('authorization_number', 'AUTH-NEW')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bottling_authorizations', [
            'id' => $authorization->id,
            'authorization_number' => 'AUTH-NEW',
        ]);
    }

    public function test_create_rejects_wine_from_other_winery(): void
    {
        $otherWine = Wine::create([
            'user_id'   => $this->makeOtherWinery()->id,
            'name'      => 'Other Wine',
            'wine_type' => 'red',
            'status'    => 'in_progress',
        ]);

        $firstType = array_key_first(BottlingAuthorization::AUTHORIZATION_TYPES);
        $firstStatus = array_key_first(BottlingAuthorization::STATUSES);

        Livewire::test(Create::class)
            ->set('wine_id', (string) $otherWine->id)
            ->set('authorization_number', 'AUTH-IDOR-TEST')
            ->set('authorization_type', $firstType)
            ->set('status', $firstStatus)
            ->call('save')
            ->assertHasErrors(['wine_id']);
    }

    public function test_winery_cannot_edit_other_winery_bottling_authorization(): void
    {
        $firstType = array_key_first(BottlingAuthorization::AUTHORIZATION_TYPES);
        $firstStatus = array_key_first(BottlingAuthorization::STATUSES);

        $authorization = BottlingAuthorization::create([
            'user_id' => $this->winery->id,
            'authorization_number' => 'AUTH-PROT',
            'authorization_type' => $firstType,
            'status' => $firstStatus,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.bottling-authorizations.edit', $authorization))
            ->assertForbidden();
    }

    public function test_winery_can_delete_bottling_authorization(): void
    {
        $firstType = array_key_first(BottlingAuthorization::AUTHORIZATION_TYPES);
        $firstStatus = array_key_first(BottlingAuthorization::STATUSES);

        $authorization = BottlingAuthorization::create([
            'user_id' => $this->winery->id,
            'authorization_number' => 'AUTH-DEL',
            'authorization_type' => $firstType,
            'status' => $firstStatus,
        ]);

        Livewire::test(Index::class)
            ->call('delete', $authorization->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('bottling_authorizations', ['id' => $authorization->id]);
    }
}
