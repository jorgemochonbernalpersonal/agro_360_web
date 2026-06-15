<?php

namespace Tests\Feature\Viticulturist\RemoteSensing;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use Tests\Feature\ViticulturistTestCase;

class RemoteSensingTest extends ViticulturistTestCase
{
    // ── Dashboard / ExecutiveDashboard ────────────────────────────────────────

    public function test_dashboard_renders_for_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();

        $this->actingAs($viticulturist)
            ->get(route('remote-sensing.advanced'))
            ->assertOk();
    }

    public function test_executive_dashboard_renders_for_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();

        $this->actingAs($viticulturist)
            ->get(route('remote-sensing.dashboard'))
            ->assertOk();
    }

    // ── PlotAnalysis ownership ────────────────────────────────────────────────

    public function test_cannot_access_other_viticulturist_plot_analysis(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $plot = $this->makePlot($other);

        $this->actingAs($viticulturist)
            ->get(route('remote-sensing.plot', $plot))
            ->assertStatus(403);
    }

    // ── Route-level IDOR on PDF/Excel exports ────────────────────────────────

    public function test_cannot_export_pdf_for_other_viticulturist_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $plot = $this->makePlot($other);

        $this->actingAs($viticulturist)
            ->get(route('remote-sensing.export.pdf', $plot))
            ->assertStatus(403);
    }

    public function test_cannot_export_excel_for_other_viticulturist_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $plot = $this->makePlot($other);

        $this->actingAs($viticulturist)
            ->get(route('remote-sensing.export.excel', $plot))
            ->assertStatus(403);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePlot($viticulturist): Plot
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);
    }
}
