<?php

namespace Tests\Unit\Models;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificationTest extends TestCase
{
    use RefreshDatabase;

    // ── certification_type_label ──────────────────────────────────────────────

    public function test_certification_type_label_returns_correct_label(): void
    {
        $model = new Certification(['certification_type' => 'ecologico']);
        $this->assertEquals('Agricultura Ecológica', $model->certification_type_label);
    }

    public function test_certification_type_label_returns_label_for_every_type(): void
    {
        $expected = [
            'ecologico' => 'Agricultura Ecológica',
            'produccion_integrada' => 'Producción Integrada',
            'globalgap' => 'GlobalG.A.P.',
            'rainforest' => 'Rainforest Alliance',
            'denominacion_origen' => 'Denominación de Origen',
            'indicacion_geografica' => 'Indicación Geográfica Protegida',
            'otro' => 'Otra certificación',
        ];

        foreach ($expected as $key => $label) {
            $model = new Certification(['certification_type' => $key]);
            $this->assertEquals($label, $model->certification_type_label, "Failed for type: {$key}");
        }
    }

    public function test_certification_type_label_returns_raw_value_for_unknown_type(): void
    {
        $model = new Certification(['certification_type' => 'biodynamico']);
        $this->assertEquals('biodynamico', $model->certification_type_label);
    }

    // ── type_color ────────────────────────────────────────────────────────────

    public function test_type_color_returns_correct_color_for_each_type(): void
    {
        $expected = [
            'ecologico' => 'green',
            'produccion_integrada' => 'teal',
            'globalgap' => 'blue',
            'rainforest' => 'emerald',
            'denominacion_origen' => 'agro',
            'indicacion_geografica' => 'violet',
            'otro' => 'zinc',
        ];

        foreach ($expected as $key => $color) {
            $model = new Certification(['certification_type' => $key]);
            $this->assertEquals($color, $model->type_color, "Failed for type: {$key}");
        }
    }

    public function test_type_color_returns_zinc_for_unknown_type(): void
    {
        $model = new Certification(['certification_type' => 'desconocido']);
        $this->assertEquals('zinc', $model->type_color);
    }

    // ── is_expired ─────────────────────────────────────────────────────────────

    public function test_is_expired_returns_true_when_expiry_date_is_past(): void
    {
        $model = new Certification(['expiry_date' => now()->subYear()]);
        $this->assertTrue($model->is_expired);
    }

    public function test_is_expired_returns_false_when_expiry_date_is_future(): void
    {
        $model = new Certification(['expiry_date' => now()->addYear()]);
        $this->assertFalse($model->is_expired);
    }

    public function test_is_expired_returns_false_when_no_expiry_date(): void
    {
        $model = new Certification(['expiry_date' => null]);
        $this->assertFalse($model->is_expired);
    }

    // ── is_expiring_soon (60-day window) ──────────────────────────────────────

    public function test_is_expiring_soon_returns_true_when_within_60_days(): void
    {
        $model = new Certification(['expiry_date' => now()->addDays(30)]);
        $this->assertTrue($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_true_on_boundary_of_60_days(): void
    {
        $model = new Certification(['expiry_date' => now()->addDays(60)]);
        $this->assertTrue($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_false_when_beyond_60_days(): void
    {
        $model = new Certification(['expiry_date' => now()->addDays(90)]);
        $this->assertFalse($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_false_when_already_expired(): void
    {
        $model = new Certification(['expiry_date' => now()->subDay()]);
        $this->assertFalse($model->is_expiring_soon);
    }

    public function test_is_expiring_soon_returns_false_when_no_expiry_date(): void
    {
        $model = new Certification(['expiry_date' => null]);
        $this->assertFalse($model->is_expiring_soon);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function test_certification_belongs_to_viticulturist(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $cert = Certification::create([
            'viticulturist_id' => $user->id,
            'certification_type' => 'ecologico',
            'certifying_body' => 'CAAE',
            'issue_date' => now()->subYear(),
            'active' => true,
        ]);

        $this->assertEquals($user->id, $cert->viticulturist->id);
    }
}
