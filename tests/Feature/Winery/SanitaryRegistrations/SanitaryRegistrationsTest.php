<?php

namespace Tests\Feature\Winery\SanitaryRegistrations;

use App\Livewire\Winery\SanitaryRegistrations\Create;
use App\Livewire\Winery\SanitaryRegistrations\Edit;
use App\Livewire\Winery\SanitaryRegistrations\Index;
use App\Models\SanitaryRegistration;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class SanitaryRegistrationsTest extends WineryTestCase
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
        $this->get(route('winery.sanitary-registrations.index'))->assertOk();
    }

    public function test_guest_cannot_access(): void
    {
        $this->app['auth']->guard()->logout();

        $this->get(route('winery.sanitary-registrations.index'))->assertRedirect();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('registration_number', '')
            ->set('registration_type', '')
            ->set('status', '')
            ->call('save')
            ->assertHasErrors(['registration_number', 'registration_type', 'status']);
    }

    public function test_winery_can_create_sanitary_registration(): void
    {
        $firstType   = array_key_first(SanitaryRegistration::REGISTRATION_TYPES);
        $firstStatus = array_key_first(SanitaryRegistration::STATUSES);

        Livewire::test(Create::class)
            ->set('registration_number', 'RGSEAA-12345')
            ->set('registration_type', $firstType)
            ->set('status', $firstStatus)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sanitary_registrations', [
            'user_id'             => $this->winery->id,
            'registration_number' => 'RGSEAA-12345',
            'registration_type'   => $firstType,
            'status'              => $firstStatus,
        ]);
    }

    public function test_winery_can_edit_sanitary_registration(): void
    {
        $firstType   = array_key_first(SanitaryRegistration::REGISTRATION_TYPES);
        $firstStatus = array_key_first(SanitaryRegistration::STATUSES);

        $registration = SanitaryRegistration::create([
            'user_id'             => $this->winery->id,
            'registration_number' => 'RGSEAA-00001',
            'registration_type'   => $firstType,
            'status'              => $firstStatus,
        ]);

        Livewire::test(Edit::class, ['sanitaryRegistration' => $registration])
            ->set('registration_number', 'RGSEAA-99999')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sanitary_registrations', [
            'id'                  => $registration->id,
            'registration_number' => 'RGSEAA-99999',
        ]);
    }

    public function test_winery_cannot_edit_other_winery_sanitary_registration(): void
    {
        $firstType   = array_key_first(SanitaryRegistration::REGISTRATION_TYPES);
        $firstStatus = array_key_first(SanitaryRegistration::STATUSES);

        $registration = SanitaryRegistration::create([
            'user_id'             => $this->winery->id,
            'registration_number' => 'RGSEAA-PROT',
            'registration_type'   => $firstType,
            'status'              => $firstStatus,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.sanitary-registrations.edit', $registration))
            ->assertForbidden();
    }

    public function test_winery_can_delete_sanitary_registration(): void
    {
        $firstType   = array_key_first(SanitaryRegistration::REGISTRATION_TYPES);
        $firstStatus = array_key_first(SanitaryRegistration::STATUSES);

        $registration = SanitaryRegistration::create([
            'user_id'             => $this->winery->id,
            'registration_number' => 'RGSEAA-DEL',
            'registration_type'   => $firstType,
            'status'              => $firstStatus,
        ]);

        Livewire::test(Index::class)
            ->call('delete', $registration->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('sanitary_registrations', ['id' => $registration->id]);
    }
}
