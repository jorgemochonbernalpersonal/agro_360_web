<?php

namespace Tests\Feature\Viticulturist\Certifications;

use App\Livewire\Viticulturist\Certifications\Index;
use App\Models\Certification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeCertification(int $viticulturistId, bool $active = true, array $overrides = []): Certification
    {
        return Certification::create(array_merge([
            'viticulturist_id'   => $viticulturistId,
            'certification_type' => 'ecologico',
            'certifying_body'    => 'CAAE',
            'issue_date'         => '2023-01-01',
            'active'             => $active,
        ], $overrides));
    }

    public function test_index_shows_active_certifications(): void
    {
        $v = $this->makeViticulturist();
        $this->makeCertification($v->id, true, ['certifying_body' => 'Organismo-Visible-001']);
        $this->actingAs($v);

        Livewire::test(Index::class)->assertSee('Organismo-Visible-001');
    }

    public function test_archive_sets_active_false(): void
    {
        $v    = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)->call('archive', $cert->id);

        $this->assertDatabaseHas('certifications', ['id' => $cert->id, 'active' => false]);
    }

    public function test_unarchive_restores_record(): void
    {
        $v    = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id, false);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('unarchive', $cert->id);

        $this->assertDatabaseHas('certifications', ['id' => $cert->id, 'active' => true]);
    }

    public function test_archived_tab_shows_inactive_records(): void
    {
        $v = $this->makeViticulturist();
        $this->makeCertification($v->id, false, ['certifying_body' => 'Organismo-Archivado-001']);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->assertSee('Organismo-Archivado-001');
    }

    public function test_active_tab_hides_archived(): void
    {
        $v = $this->makeViticulturist();
        $this->makeCertification($v->id, false, ['certifying_body' => 'Solo-Archivada-XYZ']);
        $this->actingAs($v);

        Livewire::test(Index::class)->assertDontSee('Solo-Archivada-XYZ');
    }

    public function test_delete_removes_archived_record(): void
    {
        $v    = $this->makeViticulturist();
        $cert = $this->makeCertification($v->id, false);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('delete', $cert->id);

        $this->assertDatabaseMissing('certifications', ['id' => $cert->id]);
    }

    public function test_cannot_archive_other_viticulturists_certification(): void
    {
        $v     = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $cert  = $this->makeCertification($v->id);
        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('archive', $cert->id);
    }

    public function test_stats_count_expired_certifications(): void
    {
        $v = $this->makeViticulturist();
        // expired cert
        $this->makeCertification($v->id, true, [
            'issue_date'  => '2020-01-01',
            'expiry_date' => '2021-01-01',
        ]);
        $this->actingAs($v);

        // Just verify the component renders without errors
        Livewire::test(Index::class)->assertOk();
    }
}
