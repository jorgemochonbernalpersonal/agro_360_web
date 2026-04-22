<?php

namespace Tests\Feature\Supervisor\Regulation;

use App\Livewire\Supervisor\Regulation\Index;
use App\Models\Certification;
use App\Models\DoDocument;
use App\Models\PlotPlanting;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class RegulationTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makePlanting(User $viticulturist, array $attrs = []): PlotPlanting
    {
        $plot = $this->makePlot($viticulturist);

        return PlotPlanting::factory()->create(array_merge([
            'plot_id'                => $plot->id,
            'planting_authorization' => 'AUTH-' . rand(1000, 9999),
            'authorization_date'     => now()->subYear(),
            'right_type'             => 'nueva',
        ], $attrs));
    }

    private function makeCertEco(User $viticulturist, array $attrs = []): Certification
    {
        return Certification::create(array_merge([
            'viticulturist_id'   => $viticulturist->id,
            'certification_type' => 'ecologico',
            'certifying_body'    => 'CAAE',
            'active'             => true,
            'issue_date'         => now()->subYear(),
            'expiry_date'        => now()->addYear(),
        ], $attrs));
    }

    private function makeDocument(User $supervisor, array $attrs = []): DoDocument
    {
        return DoDocument::create(array_merge([
            'supervisor_id' => $supervisor->id,
            'type'          => DoDocument::TYPE_PLIEGO,
            'title'         => 'Pliego de prueba',
            'status'        => DoDocument::STATUS_DRAFT,
        ], $attrs));
    }

    // ── TAB: autorizaciones — visibilidad ─────────────────────────────────────

    public function test_autorizaciones_shows_plantings_with_authorization(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlanting($viticulturist, ['planting_authorization' => 'AUTH-VISIBLE-001']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('AUTH-VISIBLE-001');
    }

    public function test_autorizaciones_hides_plantings_without_authorization(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);
        $plot          = $this->makePlot($viticulturist);

        PlotPlanting::factory()->create([
            'plot_id'                => $plot->id,
            'planting_authorization' => null,
        ]);

        // Create one with authorization so the list renders
        $this->makePlanting($viticulturist, ['planting_authorization' => 'AUTH-OK-001']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('items', fn($items) => $items->total() === 1);
    }

    public function test_autorizaciones_does_not_show_unrelated_viticulturist_plantings(): void
    {
        $supervisor   = $this->makeSupervisor();
        $outsideVit   = User::factory()->create(['role' => 'viticulturist']);
        $plot         = $this->makePlot($outsideVit);

        PlotPlanting::factory()->create([
            'plot_id'                => $plot->id,
            'planting_authorization' => 'AUTH-AJENO-999',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('AUTH-AJENO-999');
    }

    // ── TAB: autorizaciones — filtros ─────────────────────────────────────────

    public function test_autorizaciones_filter_by_right_type(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlanting($viticulturist, ['planting_authorization' => 'AUTH-NUEVA',     'right_type' => 'nueva']);
        $this->makePlanting($viticulturist, ['planting_authorization' => 'AUTH-REPLANTA',  'right_type' => 'replantacion']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterRightType', 'nueva')
            ->assertSee('AUTH-NUEVA')
            ->assertDontSee('AUTH-REPLANTA');
    }

    public function test_autorizaciones_filter_by_viticulturist(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1       = $this->makeViticulturistForSupervisor($supervisor);
        $vit2       = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlanting($vit1, ['planting_authorization' => 'AUTH-VIT1']);
        $this->makePlanting($vit2, ['planting_authorization' => 'AUTH-VIT2']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterVit', (string) $vit1->id)
            ->assertSee('AUTH-VIT1')
            ->assertDontSee('AUTH-VIT2');
    }

    public function test_autorizaciones_search_by_authorization_number(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlanting($viticulturist, ['planting_authorization' => 'AUTH-BUSCAR-X1']);
        $this->makePlanting($viticulturist, ['planting_authorization' => 'AUTH-OTRO-Z9']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'BUSCAR-X1')
            ->assertSee('AUTH-BUSCAR-X1')
            ->assertDontSee('AUTH-OTRO-Z9');
    }

    // ── TAB: autorizaciones — stats ───────────────────────────────────────────

    public function test_autorizaciones_stats_count_by_right_type(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makePlanting($viticulturist, ['right_type' => 'nueva']);
        $this->makePlanting($viticulturist, ['right_type' => 'nueva']);
        $this->makePlanting($viticulturist, ['right_type' => 'replantacion']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('stats', fn($s) =>
                $s['total'] === 3 && $s['nueva'] === 2 && $s['replantacion'] === 1
            );
    }

    // ── TAB: certificaciones — visibilidad ────────────────────────────────────

    public function test_certificaciones_shows_ecologico_certs_for_own_viticulturists(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makeCertEco($viticulturist, ['certifying_body' => 'CAAE-Visible-2026']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->assertSee('CAAE-Visible-2026');
    }

    public function test_certificaciones_hides_non_ecologico_certs(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Certification::create([
            'viticulturist_id'   => $viticulturist->id,
            'certification_type' => 'globalgap',
            'certifying_body'    => 'GlobalGAP-NoMostrar',
            'active'             => true,
            'issue_date'         => now()->subYear(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->assertDontSee('GlobalGAP-NoMostrar');
    }

    public function test_certificaciones_does_not_show_unrelated_viticulturist_certs(): void
    {
        $supervisor  = $this->makeSupervisor();
        $outsideVit  = User::factory()->create(['role' => 'viticulturist']);

        $this->makeCertEco($outsideVit, ['certifying_body' => 'CAAE-Ajeno']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->assertDontSee('CAAE-Ajeno');
    }

    // ── TAB: certificaciones — stats ──────────────────────────────────────────

    public function test_certificaciones_stats_count_active_and_expired(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makeCertEco($viticulturist, ['expiry_date' => now()->addYear()]);   // active
        $this->makeCertEco($viticulturist, ['expiry_date' => now()->subDay()]);    // expired

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->assertViewHas('stats', fn($s) =>
                $s['active'] === 1 && $s['expired'] === 1
            );
    }

    public function test_certificaciones_stats_count_expiring(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makeCertEco($viticulturist, ['expiry_date' => now()->addDays(30)]);  // expiring
        $this->makeCertEco($viticulturist, ['expiry_date' => now()->addYear()]);    // active

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->assertViewHas('stats', fn($s) => $s['expiring'] === 1);
    }

    // ── TAB: certificaciones — filtros ────────────────────────────────────────

    public function test_certificaciones_filter_expired(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->makeCertEco($viticulturist, ['certifying_body' => 'CAAE-Caducado', 'expiry_date' => now()->subDay()]);
        $this->makeCertEco($viticulturist, ['certifying_body' => 'CAAE-Vigente',  'expiry_date' => now()->addYear()]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->set('filterStatus', 'expired')
            ->assertSee('CAAE-Caducado')
            ->assertDontSee('CAAE-Vigente');
    }

    public function test_certificaciones_filter_by_viticulturist(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1       = $this->makeViticulturistForSupervisor($supervisor);
        $vit2       = $this->makeViticulturistForSupervisor($supervisor);

        $this->makeCertEco($vit1, ['certifying_body' => 'CuerpoVit1']);
        $this->makeCertEco($vit2, ['certifying_body' => 'CuerpoVit2']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'certificaciones')
            ->set('filterVit', (string) $vit1->id)
            ->assertSee('CuerpoVit1')
            ->assertDontSee('CuerpoVit2');
    }

    // ── TAB: documentos — stats ───────────────────────────────────────────────

    public function test_document_stats_count_by_status(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->makeDocument($supervisor, ['status' => DoDocument::STATUS_DRAFT]);
        $this->makeDocument($supervisor, ['status' => DoDocument::STATUS_ACTIVE]);
        $this->makeDocument($supervisor, ['status' => DoDocument::STATUS_ACTIVE]);
        $this->makeDocument($supervisor, ['status' => DoDocument::STATUS_ARCHIVED]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'pliego')
            ->assertViewHas('stats', fn($s) =>
                $s['draft'] === 1 && $s['active'] === 2 && $s['archived'] === 1
            );
    }

    public function test_document_stats_filter_by_status(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->makeDocument($supervisor, ['title' => 'Doc Vigente',  'status' => DoDocument::STATUS_ACTIVE]);
        $this->makeDocument($supervisor, ['title' => 'Doc Borrador', 'status' => DoDocument::STATUS_DRAFT]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('switchTab', 'pliego')
            ->set('filterStatus', DoDocument::STATUS_ACTIVE)
            ->assertSee('Doc Vigente')
            ->assertDontSee('Doc Borrador');
    }

}
