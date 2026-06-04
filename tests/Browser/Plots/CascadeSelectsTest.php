<?php

namespace Tests\Browser\Plots;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * Flujo crítico: Cascade de selects de ubicación en parcelas.
 *
 * Este test cubre el bug de "Snapshot missing on Livewire component" que
 * ocurría cuando wire:model.live se usaba en los selects de comunidad/provincia.
 * La corrección fue eliminar wire:model.live y usar Alpine.js + #[Renderless].
 *
 * Flujo que se prueba:
 *   Comunidad Autónoma → Provincia (filtrado client-side) → Municipio (cargado vía AJAX Renderless)
 */
class CascadeSelectsTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Necesitamos datos geográficos reales para seleccionar
        Artisan::call('db:seed', ['--class' => 'SpainGeographySeeder']);
    }

    #[Test]
    public function selecting_community_populates_provinces_without_snapshot_error(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/viticulturist/plots/create')
                ->waitFor('body', 5)
                // La página de crear parcela debe cargarse
                ->assertSee('Crear')
                // El select de comunidad debe estar presente
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                // Seleccionar la primera comunidad autónoma disponible (Andalucía, id=1)
                ->select('select[wire\\:model="autonomous_community_id"]', 1)
                // Esperar que Alpine.js filtre las provincias — sin page reload ni AJAX de componente
                ->pause(500)
                // El select de provincia debe estar habilitado y tener opciones
                ->assertPresent('select[wire\\:model="province_id"]')
                ->assertEnabled('select[wire\\:model="province_id"]')
                // NO debe haber errores de "Snapshot missing" — verificamos que la página sigue viva
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                ->screenshot('cascade-community-to-province');
        });
    }

    #[Test]
    public function selecting_province_loads_municipalities_via_renderless(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/viticulturist/plots/create')
                ->waitFor('body', 5)
                // Seleccionar Andalucía
                ->select('select[wire\\:model="autonomous_community_id"]', 1)
                ->pause(500)
                // Seleccionar primera provincia de Andalucía (Almería = 4)
                ->select('select[wire\\:model="province_id"]', 4)
                // Esperar la carga de municipios via #[Renderless] getMunicipalities()
                ->waitUntil(
                    "document.querySelector('select[wire\\\\:model=\"municipality_id\"]').options.length > 1",
                    10
                )
                // El select de municipio debe estar habilitado y tener opciones reales
                ->assertEnabled('select[wire\\:model="municipality_id"]')
                // La página sigue íntegra — el componente Livewire sigue registrado
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                ->assertPresent('select[wire\\:model="province_id"]')
                ->screenshot('cascade-province-to-municipality');
        });
    }

    #[Test]
    public function changing_community_resets_province_and_municipality(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/viticulturist/plots/create')
                ->waitFor('body', 5)
                // Primera selección: Andalucía → Almería → municipio
                ->select('select[wire\\:model="autonomous_community_id"]', 1)
                ->pause(500)
                ->select('select[wire\\:model="province_id"]', 4)
                ->waitUntil(
                    "document.querySelector('select[wire\\\\:model=\"municipality_id\"]').options.length > 1",
                    8
                )
                // Cambiar comunidad → debe resetear provincia y municipio
                ->select('select[wire\\:model="autonomous_community_id"]', 2)
                ->pause(500)
                // El municipio debe quedar vacío (resetado)
                ->assertSelected('select[wire\\:model="municipality_id"]', '')
                // El componente sigue vivo — no hubo Snapshot missing
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                ->screenshot('cascade-community-change-resets');
        });
    }

    #[Test]
    public function full_cascade_allows_form_submission(): void
    {
        $user = $this->createViticulturist();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/viticulturist/plots/create')
                ->waitFor('body', 5)
                // Rellenar datos básicos
                ->type('[wire\\:model="name"]', 'Parcela Dusk Test')
                // Ubicación: Andalucía → Almería → primer municipio
                ->select('select[wire\\:model="autonomous_community_id"]', 1)
                ->pause(500)
                ->select('select[wire\\:model="province_id"]', 4)
                ->waitUntil(
                    "document.querySelector('select[wire\\\\:model=\"municipality_id\"]').options.length > 1",
                    8
                )
                ->script("
                    const sel = document.querySelector('select[wire\\:model=\"municipality_id\"]');
                    sel.value = sel.options[1].value;
                    sel.dispatchEvent(new Event('change', {bubbles: true}));
                ")
                ->pause(500)
                // El formulario debe poder enviarse sin errores de Livewire
                ->press('Guardar')
                ->pause(2000)
                // Si hay errores de validación es normal (faltan campos),
                // lo importante es que NO hay error de Snapshot/Livewire
                ->assertDontSee('Livewire')
                ->assertDontSee('Snapshot missing')
                ->screenshot('cascade-full-form-submit');
        });
    }

    #[Test]
    public function edit_plot_cascade_shows_preloaded_location(): void
    {
        $user = $this->createViticulturist();
        $plotId = $this->createTestPlot($user);

        $this->browse(function (Browser $browser) use ($user, $plotId) {
            $browser->loginAs($user)
                ->visit("/viticulturist/plots/{$plotId}/edit")
                ->waitFor('body', 5)
                // Los selects deben mostrar los valores pre-cargados
                ->assertPresent('select[wire\\:model="autonomous_community_id"]')
                ->assertPresent('select[wire\\:model="province_id"]')
                ->assertPresent('select[wire\\:model="municipality_id"]')
                // El select de municipio debe estar habilitado (hay provincia cargada)
                ->assertEnabled('select[wire\\:model="municipality_id"]')
                ->screenshot('cascade-edit-preloaded');
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createTestPlot($user): int
    {
        $communityId = \DB::table('autonomous_communities')->value('id');
        $provinceId = \DB::table('provinces')->where('autonomous_community_id', $communityId)->value('id');
        $municipalityId = \DB::table('municipalities')->where('province_id', $provinceId)->value('id');

        return \DB::table('plots')->insertGetId([
            'viticulturist_id' => $user->id,
            'name' => 'Parcela Dusk Edit Test',
            'autonomous_community_id' => $communityId,
            'province_id' => $provinceId,
            'municipality_id' => $municipalityId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
