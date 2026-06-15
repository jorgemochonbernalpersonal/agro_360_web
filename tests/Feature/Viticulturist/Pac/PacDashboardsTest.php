<?php

namespace Tests\Feature\Viticulturist\Pac;

use App\Livewire\Viticulturist\Pac\Dashboard;
use App\Livewire\Viticulturist\Pac\EcoSchemes\Index as EcoSchemesIndex;
use App\Livewire\Viticulturist\Pac\Surfaces\Index as SurfacesIndex;
use App\Livewire\Viticulturist\PacComplianceDashboard;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class PacDashboardsTest extends ViticulturistTestCase
{
    public function test_pac_dashboard_renders(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Dashboard::class)->assertStatus(200);
    }

    public function test_surfaces_index_renders(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(SurfacesIndex::class)->assertStatus(200);
    }

    public function test_eco_schemes_index_renders(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(EcoSchemesIndex::class)->assertStatus(200);
    }

    public function test_compliance_dashboard_renders(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(PacComplianceDashboard::class)->assertStatus(200);
    }
}
