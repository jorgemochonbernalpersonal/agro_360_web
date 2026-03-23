<?php

namespace Tests\Feature\Supervisor\Qualification;

use App\Livewire\Supervisor\Qualification\Index as QualificationIndex;
use App\Models\DoQualification;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

/**
 * Tests for the Supervisor Qualification edit modal.
 *
 * Covers: open/close, field population, update, scores, validation,
 * and cross-supervisor authorization.
 */
class EditTest extends SupervisorTestCase
{
    // ── open / close ──────────────────────────────────────────────────────────

    public function test_open_edit_populates_fields(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2023,
            'wine_name'          => 'Reserva Especial',
            'color'              => 'tinto',
            'alcohol_percentage' => '14.5',
            'qualification_date' => '2024-03-15',
            'tasting_notes'      => 'Notas de frutos rojos',
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id)
            ->assertSet('showEdit', true)
            ->assertSet('editId', $q->id)
            ->assertSet('editWineName', 'Reserva Especial')
            ->assertSet('editVintage', '2023')
            ->assertSet('editColor', 'tinto')
            ->assertSet('editQualificationDate', '2024-03-15')
            ->assertSet('editTastingNotes', 'Notas de frutos rojos');
    }

    public function test_close_edit_hides_modal(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2023,
            'wine_name'          => 'Vino Test',
            'qualification_date' => now()->toDateString(),
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id)
            ->assertSet('showEdit', true)
            ->call('closeEdit')
            ->assertSet('showEdit', false)
            ->assertSet('editId', null);
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_can_update_basic_fields(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2022,
            'wine_name'          => 'Original',
            'qualification_date' => '2023-01-01',
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', 'Actualizado')
            ->set('editVintage', '2023')
            ->set('editColor', 'blanco')
            ->set('editQualificationDate', '2024-06-01')
            ->call('updateQualification')
            ->assertSet('showEdit', false)
            ->assertHasNoErrors();

        $fresh = $q->fresh();
        $this->assertSame('Actualizado', $fresh->wine_name);
        $this->assertSame(2023, $fresh->vintage);
        $this->assertSame('blanco', $fresh->color);
        $this->assertSame('2024-06-01', $fresh->qualification_date->format('Y-m-d'));
    }

    public function test_can_update_analytical_scores(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2023,
            'wine_name'          => 'Vino Analítico',
            'qualification_date' => now()->toDateString(),
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', 'Vino Analítico')
            ->set('editVintage', '2023')
            ->set('editQualificationDate', now()->toDateString())
            ->set('editBrix', '22.5')
            ->set('editAcidity', '6.2')
            ->set('editPh', '3.4')
            ->set('editVisualScore', '8')
            ->set('editAromaScore', '9')
            ->set('editTasteScore', '7')
            ->set('editOverallScore', '85')
            ->call('updateQualification')
            ->assertHasNoErrors();

        $fresh = $q->fresh();
        $this->assertEquals(22.5, (float) $fresh->brix_degree);
        $this->assertEquals(6.2,  (float) $fresh->acidity_level);
        $this->assertEquals(3.4,  (float) $fresh->ph_level);
        $this->assertEquals(8.0,  (float) $fresh->visual_score);
        $this->assertEquals(9.0,  (float) $fresh->aroma_score);
        $this->assertEquals(7.0,  (float) $fresh->taste_score);
        $this->assertEquals(85.0, (float) $fresh->overall_score);
    }

    // ── validation ────────────────────────────────────────────────────────────

    public function test_update_requires_wine_name(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2023,
            'wine_name'          => 'Vino',
            'qualification_date' => now()->toDateString(),
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', '')
            ->call('updateQualification')
            ->assertHasErrors(['editWineName']);
    }

    public function test_update_rejects_invalid_color(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $q = DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2023,
            'wine_name'          => 'Vino',
            'qualification_date' => now()->toDateString(),
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id)
            ->set('editWineName', 'Vino')
            ->set('editVintage', '2023')
            ->set('editQualificationDate', now()->toDateString())
            ->set('editColor', 'invalido')
            ->call('updateQualification')
            ->assertHasErrors(['editColor']);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_edit_another_supervisors_qualification(): void
    {
        [$supervisor, $winery]  = $this->makeSupervisorWithWinery();
        $otherSupervisor        = $this->makeSupervisor();

        $q = DoQualification::create([
            'supervisor_id'      => $otherSupervisor->id,
            'winery_id'          => $winery->id,
            'vintage'            => 2023,
            'wine_name'          => 'Ajeno',
            'qualification_date' => now()->toDateString(),
            'result'             => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(QualificationIndex::class)
            ->call('openEdit', $q->id);
    }
}
