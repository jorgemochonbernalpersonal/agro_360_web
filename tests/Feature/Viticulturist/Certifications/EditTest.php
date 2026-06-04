<?php

namespace Tests\Feature\Viticulturist\Certifications;

use App\Livewire\Viticulturist\Certifications\Edit;
use App\Models\Certification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_can_edit_certification(): void
    {
        $v = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['certification' => $cert])
            ->set('certifying_body', 'CCPAE Actualizado')
            ->set('certification_type', 'produccion_integrada')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.certifications.index'));

        $this->assertDatabaseHas('certifications', [
            'id' => $cert->id,
            'certifying_body' => 'CCPAE Actualizado',
            'certification_type' => 'produccion_integrada',
        ]);
    }

    public function test_mount_fills_properties_from_model(): void
    {
        $v = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['certification' => $cert])
            ->assertSet('certifying_body', 'CAAE Original')
            ->assertSet('certification_type', 'ecologico')
            ->assertSet('issue_date', '2023-01-01');
    }

    public function test_cannot_edit_other_viticulturists_certification(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $cert = $this->makeCertification($v->id);
        $this->actingAs($other);

        Livewire::test(Edit::class, ['certification' => $cert])
            ->assertStatus(403);
    }

    public function test_expiry_must_be_after_issue_on_update(): void
    {
        $v = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['certification' => $cert])
            ->set('issue_date', '2024-06-01')
            ->set('expiry_date', '2024-01-01')
            ->call('save')
            ->assertHasErrors(['expiry_date']);
    }

    public function test_certificate_number_can_be_cleared(): void
    {
        $v = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['certification' => $cert])
            ->set('certificate_number', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('certifications', [
            'id' => $cert->id,
            'certificate_number' => null,
        ]);
    }

    private function makeCertification(int $viticulturistId): Certification
    {
        return Certification::create([
            'viticulturist_id' => $viticulturistId,
            'certification_type' => 'ecologico',
            'certifying_body' => 'CAAE Original',
            'issue_date' => '2023-01-01',
        ]);
    }
}
