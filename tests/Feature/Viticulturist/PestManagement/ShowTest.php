<?php

namespace Tests\Feature\Viticulturist\PestManagement;

use App\Livewire\Viticulturist\PestManagement\Show;
use App\Models\Pest;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class ShowTest extends ViticulturistTestCase
{
    // ── Render ────────────────────────────────────────────────────────────────

    public function test_renders_show(): void
    {
        $pest = $this->makePest();
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertStatus(200);
    }

    public function test_shows_pest_name(): void
    {
        $pest = $this->makePest(['name' => 'Polilla del Racimo']);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Polilla del Racimo');
    }

    public function test_shows_scientific_name(): void
    {
        $pest = $this->makePest([
            'name' => 'Araña Roja',
            'scientific_name' => 'Tetranychus urticae',
        ]);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Tetranychus urticae');
    }

    public function test_shows_description(): void
    {
        $pest = $this->makePest(['description' => 'Ácaro que ataca hojas y frutas.']);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Ácaro que ataca hojas y frutas.');
    }

    public function test_shows_symptoms(): void
    {
        $pest = $this->makePest(['symptoms' => 'Hojas con punteaduras amarillentas.']);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Hojas con punteaduras amarillentas.');
    }

    public function test_shows_prevention_methods(): void
    {
        $pest = $this->makePest(['prevention_methods' => 'Mantener humedad adecuada.']);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Mantener humedad adecuada.');
    }

    // ── control_methods (IPM PAC) ─────────────────────────────────────────────

    public function test_shows_control_methods_section(): void
    {
        $pest = $this->makePest([
            'control_methods' => ['biologico', 'cultural', 'quimico'],
        ]);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Control biológico')
            ->assertSee('Control cultural')
            ->assertSee('Control químico');
    }

    public function test_control_methods_section_not_shown_when_null(): void
    {
        $pest = $this->makePest(['control_methods' => null]);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertDontSee('Métodos de Control IPM');
    }

    public function test_shows_all_four_control_method_types(): void
    {
        $pest = $this->makePest([
            'control_methods' => ['biologico', 'cultural', 'fisico', 'quimico'],
        ]);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('Control biológico')
            ->assertSee('Control cultural')
            ->assertSee('Control físico')
            ->assertSee('Control químico');
    }

    // ── Risk period badge ─────────────────────────────────────────────────────

    public function test_shows_risk_badge_when_in_risk_period(): void
    {
        $currentMonth = now()->month;
        $pest = $this->makePest(['risk_months' => [$currentMonth]]);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('En período de riesgo');
    }

    public function test_no_risk_badge_when_not_in_risk_period(): void
    {
        // Use months that definitely don't include current month
        $otherMonths = array_values(array_diff(range(1, 12), [now()->month]));
        $safeMonths = array_slice($otherMonths, 0, 2);

        $pest = $this->makePest(['risk_months' => $safeMonths]);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertDontSee('En período de riesgo');
    }

    // ── threshold ─────────────────────────────────────────────────────────────

    public function test_shows_threshold(): void
    {
        $pest = $this->makePest(['threshold' => '5% de racimos afectados']);
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Show::class, ['pest' => $pest])
            ->assertSee('5% de racimos afectados');
    }
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makePest(array $overrides = []): Pest
    {
        return Pest::create(array_merge([
            'type' => 'pest',
            'name' => 'Araña Roja',
            'description' => 'Ácaro que ataca las hojas.',
            'active' => true,
        ], $overrides));
    }
}
