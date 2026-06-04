<?php

namespace Tests\Feature\Viticulturist\Certifications;

use App\Livewire\Viticulturist\Certifications\Create;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_certification(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('certification_type', 'globalgap')
            ->set('certifying_body', 'Bureau Veritas')
            ->set('issue_date', '2024-03-01')
            ->set('expiry_date', '2027-03-01')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.certifications.index'));

        $this->assertDatabaseHas('certifications', [
            'viticulturist_id' => $v->id,
            'certification_type' => 'globalgap',
            'certifying_body' => 'Bureau Veritas',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('certifying_body', '')
            ->set('issue_date', '')
            ->call('save')
            ->assertHasErrors(['certifying_body', 'issue_date']);
    }

    public function test_certification_type_must_be_valid_enum(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('certification_type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['certification_type']);
    }

    public function test_expiry_must_be_after_issue_date(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('certifying_body', 'Test Body')
            ->set('issue_date', '2024-06-01')
            ->set('expiry_date', '2024-01-01')
            ->call('save')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_mount_defaults_issue_date_to_today(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('issue_date', now()->format('Y-m-d'));
    }

    public function test_optional_fields_save_as_null(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('certification_type', 'ecologico')
            ->set('certifying_body', 'CCPAE')
            ->set('issue_date', '2024-01-01')
            ->set('certificate_number', '')
            ->set('expiry_date', '')
            ->set('audit_date', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('certifications', [
            'viticulturist_id' => $v->id,
            'certificate_number' => null,
            'expiry_date' => null,
        ]);
    }
}
