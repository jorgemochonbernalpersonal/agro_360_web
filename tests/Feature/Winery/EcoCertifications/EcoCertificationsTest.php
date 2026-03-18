<?php

namespace Tests\Feature\Winery\EcoCertifications;

use App\Livewire\Winery\EcoCertifications\Create;
use App\Livewire\Winery\EcoCertifications\Edit;
use App\Livewire\Winery\EcoCertifications\Index;
use App\Models\EcoCertification;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class EcoCertificationsTest extends WineryTestCase
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
        $this->get(route('winery.eco-certifications.index'))->assertOk();
    }

    public function test_guest_cannot_access(): void
    {
        $this->app['auth']->guard()->logout();

        $this->get(route('winery.eco-certifications.index'))->assertRedirect();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('certification_type', '')
            ->set('status', '')
            ->call('save')
            ->assertHasErrors(['name', 'certification_type', 'status']);
    }

    public function test_winery_can_create_eco_certification(): void
    {
        $firstType   = array_key_first(EcoCertification::CERTIFICATION_TYPES);
        $firstStatus = array_key_first(EcoCertification::STATUSES);

        Livewire::test(Create::class)
            ->set('name', 'Certificación Ecológica 2026')
            ->set('certification_type', $firstType)
            ->set('status', $firstStatus)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('eco_certifications', [
            'user_id'            => $this->winery->id,
            'name'               => 'Certificación Ecológica 2026',
            'certification_type' => $firstType,
            'status'             => $firstStatus,
        ]);
    }

    public function test_winery_can_edit_eco_certification(): void
    {
        $firstType   = array_key_first(EcoCertification::CERTIFICATION_TYPES);
        $firstStatus = array_key_first(EcoCertification::STATUSES);

        $certification = EcoCertification::create([
            'user_id'            => $this->winery->id,
            'name'               => 'Nombre Original',
            'certification_type' => $firstType,
            'status'             => $firstStatus,
        ]);

        Livewire::test(Edit::class, ['ecoCertification' => $certification])
            ->set('name', 'Nombre Actualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('eco_certifications', [
            'id'   => $certification->id,
            'name' => 'Nombre Actualizado',
        ]);
    }

    public function test_winery_cannot_edit_other_winery_eco_certification(): void
    {
        $firstType   = array_key_first(EcoCertification::CERTIFICATION_TYPES);
        $firstStatus = array_key_first(EcoCertification::STATUSES);

        $certification = EcoCertification::create([
            'user_id'            => $this->winery->id,
            'name'               => 'Certificación Protegida',
            'certification_type' => $firstType,
            'status'             => $firstStatus,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.eco-certifications.edit', $certification))
            ->assertForbidden();
    }

    public function test_winery_can_delete_eco_certification(): void
    {
        $firstType   = array_key_first(EcoCertification::CERTIFICATION_TYPES);
        $firstStatus = array_key_first(EcoCertification::STATUSES);

        $certification = EcoCertification::create([
            'user_id'            => $this->winery->id,
            'name'               => 'Certificación a Eliminar',
            'certification_type' => $firstType,
            'status'             => $firstStatus,
        ]);

        Livewire::test(Index::class)
            ->call('delete', $certification->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('eco_certifications', ['id' => $certification->id]);
    }
}
