<?php

namespace Tests\Feature\Supervisor\Oversight;

use App\Livewire\Supervisor\Oversight\Certifications\Index;
use App\Models\Certification;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class CertificationsIndexTest extends SupervisorTestCase
{
    private function makeSupervisorWithViticulturist(): array
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        return [$supervisor, $viticulturist];
    }

    private function makeCert(User $viticulturist, array $attrs = []): Certification
    {
        return Certification::create(array_merge([
            'viticulturist_id'   => $viticulturist->id,
            'certification_type' => 'ecologico',
            'certifying_body'    => 'Organismo Certificador',
            'active'             => true,
            'issue_date'         => now()->subYear(),
            'expiry_date'        => now()->addYear(),
        ], $attrs));
    }

    // ── carga básica ──────────────────────────────────────────────────────

    public function test_index_loads_for_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    // ── visibilidad ───────────────────────────────────────────────────────

    public function test_shows_certifications_of_own_viticulturists(): void
    {
        [$supervisor, $viticulturist] = $this->makeSupervisorWithViticulturist();

        $this->makeCert($viticulturist, ['certifying_body' => 'CuerpoEco2026']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('CuerpoEco2026');
    }

    public function test_does_not_show_certifications_of_unrelated_viticulturists(): void
    {
        $supervisor  = $this->makeSupervisor();
        $outsideVit  = User::factory()->create(['role' => 'viticulturist', 'name' => 'VitAjeno Test']);

        $this->makeCert($outsideVit, ['certifying_body' => 'OrganismoAjeno']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('OrganismoAjeno');
    }

    // ── contadores ────────────────────────────────────────────────────────

    public function test_counts_expiring_soon_certifications(): void
    {
        [$supervisor, $viticulturist] = $this->makeSupervisorWithViticulturist();

        $this->makeCert($viticulturist, ['expiry_date' => now()->addDays(30)]);
        $this->makeCert($viticulturist, ['expiry_date' => now()->addYear()]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalExpiring', 1);
    }

    public function test_counts_expired_certifications(): void
    {
        [$supervisor, $viticulturist] = $this->makeSupervisorWithViticulturist();

        $this->makeCert($viticulturist, ['expiry_date' => now()->subDay()]);
        $this->makeCert($viticulturist, ['expiry_date' => now()->addYear()]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('totalExpired', 1);
    }

    // ── filtros ───────────────────────────────────────────────────────────

    public function test_filter_by_type(): void
    {
        [$supervisor, $viticulturist] = $this->makeSupervisorWithViticulturist();

        $this->makeCert($viticulturist, ['certification_type' => 'ecologico',  'certifying_body' => 'CuerpoEco']);
        $this->makeCert($viticulturist, ['certification_type' => 'globalgap',  'certifying_body' => 'CuerpoGAP']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterType', 'ecologico')
            ->assertSee('CuerpoEco')
            ->assertDontSee('CuerpoGAP');
    }

    public function test_filter_status_expiring(): void
    {
        [$supervisor, $viticulturist] = $this->makeSupervisorWithViticulturist();

        $this->makeCert($viticulturist, ['expiry_date' => now()->addDays(15), 'certifying_body' => 'CuerpoExpirando']);
        $this->makeCert($viticulturist, ['expiry_date' => now()->addYear(),    'certifying_body' => 'CuerpoVigente']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterStatus', 'expiring')
            ->assertSee('CuerpoExpirando')
            ->assertDontSee('CuerpoVigente');
    }

    public function test_filter_status_expired(): void
    {
        [$supervisor, $viticulturist] = $this->makeSupervisorWithViticulturist();

        $this->makeCert($viticulturist, ['expiry_date' => now()->subDay(),  'certifying_body' => 'CuerpoCaducado']);
        $this->makeCert($viticulturist, ['expiry_date' => now()->addYear(), 'certifying_body' => 'CuerpoVigente2']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterStatus', 'expired')
            ->assertSee('CuerpoCaducado')
            ->assertDontSee('CuerpoVigente2');
    }

    public function test_filter_by_viticulturist(): void
    {
        [$supervisor, $vit1] = $this->makeSupervisorWithViticulturist();
        $vit2                = $this->makeViticulturistForSupervisor($supervisor);

        $this->makeCert($vit1, ['certifying_body' => 'OrganismoVit1']);
        $this->makeCert($vit2, ['certifying_body' => 'OrganismoVit2']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterVit', (string) $vit1->id)
            ->assertSee('OrganismoVit1')
            ->assertDontSee('OrganismoVit2');
    }

    public function test_clear_filters_resets_all(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterType', 'ecologico')
            ->set('filterStatus', 'expired')
            ->call('clearFilters')
            ->assertSet('filterType', '')
            ->assertSet('filterStatus', '');
    }
}
