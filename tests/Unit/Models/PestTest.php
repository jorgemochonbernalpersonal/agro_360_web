<?php

namespace Tests\Unit\Models;

use App\Models\Pest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PestTest extends TestCase
{
    use RefreshDatabase;

    // ── Casts ─────────────────────────────────────────────────────────────────

    public function test_risk_months_is_cast_to_array(): void
    {
        $pest = Pest::create([
            'type'        => 'pest',
            'name'        => 'Test Plaga',
            'risk_months' => [5, 6, 7],
            'active'      => true,
        ]);

        $this->assertIsArray($pest->risk_months);
        $this->assertEquals([5, 6, 7], $pest->risk_months);
    }

    public function test_active_is_cast_to_boolean(): void
    {
        $pest = Pest::create([
            'type'   => 'disease',
            'name'   => 'Test Enfermedad',
            'active' => true,
        ]);

        $this->assertIsBool($pest->active);
        $this->assertTrue($pest->active);
    }

    public function test_control_methods_is_cast_to_array(): void
    {
        $pest = Pest::create([
            'type'            => 'pest',
            'name'            => 'Polilla Test',
            'control_methods' => ['biologico', 'cultural', 'quimico'],
            'active'          => true,
        ]);

        $this->assertIsArray($pest->control_methods);
        $this->assertContains('biologico', $pest->control_methods);
        $this->assertContains('cultural', $pest->control_methods);
        $this->assertContains('quimico', $pest->control_methods);
    }

    public function test_control_methods_nullable(): void
    {
        $pest = Pest::create([
            'type'   => 'disease',
            'name'   => 'Sin Métodos',
            'active' => true,
        ]);

        $this->assertNull($pest->control_methods);
    }

    // ── isInRiskPeriod ────────────────────────────────────────────────────────

    public function test_is_in_risk_period_returns_true_when_month_in_array(): void
    {
        $pest = Pest::create([
            'type'        => 'pest',
            'name'        => 'Araña Roja',
            'risk_months' => [6, 7, 8, 9],
            'active'      => true,
        ]);

        $this->assertTrue($pest->isInRiskPeriod(7));
    }

    public function test_is_in_risk_period_returns_false_when_month_not_in_array(): void
    {
        $pest = Pest::create([
            'type'        => 'pest',
            'name'        => 'Araña Roja',
            'risk_months' => [6, 7, 8, 9],
            'active'      => true,
        ]);

        $this->assertFalse($pest->isInRiskPeriod(1));
    }

    public function test_is_in_risk_period_returns_false_when_no_risk_months(): void
    {
        $pest = Pest::create([
            'type'   => 'disease',
            'name'   => 'Sin Meses',
            'active' => true,
        ]);

        $this->assertFalse($pest->isInRiskPeriod(6));
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function test_scope_by_type_filters_pests(): void
    {
        Pest::create(['type' => 'pest', 'name' => 'Plaga A', 'active' => true]);
        Pest::create(['type' => 'disease', 'name' => 'Enfermedad A', 'active' => true]);

        $pests = Pest::byType('pest')->get();

        // All results must have type 'pest'; seeded records may also be present
        $this->assertTrue($pests->every(fn ($p) => $p->type === 'pest'));
        $this->assertTrue($pests->contains('name', 'Plaga A'));
        $this->assertFalse($pests->contains('name', 'Enfermedad A'));
    }

    public function test_scope_in_risk_period_filters_by_month(): void
    {
        Pest::create(['type' => 'pest', 'name' => 'Riesgo Verano', 'risk_months' => [6, 7, 8], 'active' => true]);
        Pest::create(['type' => 'pest', 'name' => 'Riesgo Invierno', 'risk_months' => [12, 1, 2], 'active' => true]);

        $results = Pest::inRiskPeriod(7)->get();

        // Scope must include Riesgo Verano (has month 7) and exclude Riesgo Invierno
        $this->assertTrue($results->contains('name', 'Riesgo Verano'));
        $this->assertFalse($results->contains('name', 'Riesgo Invierno'));
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function test_full_name_includes_scientific_name(): void
    {
        $pest = Pest::create([
            'type'            => 'pest',
            'name'            => 'Polilla del Racimo',
            'scientific_name' => 'Lobesia botrana',
            'active'          => true,
        ]);

        $this->assertEquals('Polilla del Racimo (Lobesia botrana)', $pest->full_name);
    }

    public function test_full_name_without_scientific_name(): void
    {
        $pest = Pest::create([
            'type'   => 'pest',
            'name'   => 'Sin Nombre Científico',
            'active' => true,
        ]);

        $this->assertEquals('Sin Nombre Científico', $pest->full_name);
    }

    public function test_icon_is_bug_for_pest(): void
    {
        $pest = Pest::create(['type' => 'pest', 'name' => 'Bug', 'active' => true]);

        $this->assertEquals('🐛', $pest->icon);
    }

    public function test_icon_is_virus_for_disease(): void
    {
        $disease = Pest::create(['type' => 'disease', 'name' => 'Virus', 'active' => true]);

        $this->assertEquals('🦠', $disease->icon);
    }

    // ── control_method_labels accessor ────────────────────────────────────────

    public function test_control_method_labels_returns_readable_labels(): void
    {
        $pest = Pest::create([
            'type'            => 'pest',
            'name'            => 'Con Métodos',
            'control_methods' => ['biologico', 'quimico'],
            'active'          => true,
        ]);

        $labels = $pest->control_method_labels;

        $this->assertContains('Control biológico', $labels);
        $this->assertContains('Control químico', $labels);
    }

    public function test_control_method_labels_returns_empty_array_when_null(): void
    {
        $pest = Pest::create(['type' => 'disease', 'name' => 'Sin Labels', 'active' => true]);

        $this->assertEquals([], $pest->control_method_labels);
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    public function test_control_method_types_constant_has_expected_values(): void
    {
        $this->assertEquals(['biologico', 'cultural', 'fisico', 'quimico'], Pest::CONTROL_METHOD_TYPES);
    }
}
