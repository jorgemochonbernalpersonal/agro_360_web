<?php

namespace Tests\Unit\Models;

use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SigpacCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    // ============================================
    // Tests para buildCodeFromFields()
    // ============================================

    public function test_build_code_from_fields_with_all_fields(): void
    {
        $fields = [
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_aggregate' => '0',
            'code_zone' => '0',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ];

        $code = SigpacCode::buildCodeFromFields($fields);

        // CA(2)+Prov(2)+Mun(3)+Agr(3)+Zona(3)+Pol(3)+Parcela(5)+Recinto(3) = 24 dígitos
        // 13 + 28 + 079 + 000 + 000 + 012 + 00045 + 003
        $this->assertEquals('132807900000001200045003', $code);
        $this->assertEquals(24, strlen($code));
    }

    public function test_build_code_from_fields_pads_with_zeros(): void
    {
        $fields = [
            'code_autonomous_community' => '1',   // 1 dígito -> '01'
            'code_province' => '8',   // 1 dígito -> '08'
            'code_municipality' => '79',  // 2 dígitos -> '079'
            'code_aggregate' => '0',   // 1 dígito -> '000'
            'code_zone' => '0',   // 1 dígito -> '000'
            'code_polygon' => '2',   // 1 dígito -> '002'
            'code_plot' => '45',  // 2 dígitos -> '00045'
            'code_enclosure' => '3',   // 1 dígito -> '003'
        ];

        $code = SigpacCode::buildCodeFromFields($fields);

        // 01 + 08 + 079 + 000 + 000 + 002 + 00045 + 003
        $this->assertEquals('010807900000000200045003', $code);
        $this->assertEquals(24, strlen($code));
    }

    public function test_build_code_from_fields_supports_three_digit_aggregate(): void
    {
        $fields = [
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_aggregate' => '999',  // 3 dígitos — caso que antes fallaba
            'code_zone' => '5',
            'code_polygon' => '125',  // 3 dígitos — caso que antes fallaba
            'code_plot' => '00100',
            'code_enclosure' => '001',
        ];

        $code = SigpacCode::buildCodeFromFields($fields);

        // 13 + 28 + 079 + 999 + 005 + 125 + 00100 + 001
        $this->assertEquals('132807999900512500100001', $code);
        $this->assertEquals(24, strlen($code));
    }

    public function test_build_code_from_fields_with_missing_fields_defaults_to_zeros(): void
    {
        $fields = [
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            // code_aggregate faltante -> '000'
            'code_zone' => '0',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ];

        $code = SigpacCode::buildCodeFromFields($fields);

        $this->assertEquals('132807900000001200045003', $code);
    }

    public function test_build_code_from_fields_with_empty_aggregate_defaults_to_zero(): void
    {
        $fields = [
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_aggregate' => '',  // Vacío -> '000'
            'code_zone' => '0',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ];

        $code = SigpacCode::buildCodeFromFields($fields);

        $this->assertEquals('132807900000001200045003', $code);
    }

    // ============================================
    // Tests para parseSigpacCode()
    // ============================================

    public function test_parse_sigpac_code_throws_exception_on_invalid_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El código SIGPAC debe tener exactamente 24 dígitos');

        SigpacCode::parseSigpacCode('13280790001200045003'); // 20 dígitos
    }

    public function test_parse_sigpac_code_throws_exception_on_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El código SIGPAC debe tener exactamente 24 dígitos');

        SigpacCode::parseSigpacCode('1328079000000012000450030'); // 25 dígitos
    }

    public function test_parse_sigpac_code_throws_exception_on_non_numeric(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El código SIGPAC solo puede contener números y guiones');

        SigpacCode::parseSigpacCode('13-28-079-000-000-012-00045-ABC');
    }

    public function test_parse_sigpac_code_parses_correctly(): void
    {
        // 13-28-079-000-000-012-00045-003 → 24 dígitos sin guiones
        $parsed = SigpacCode::parseSigpacCode('13-28-079-000-000-012-00045-003');

        $this->assertEquals('132807900000001200045003', $parsed['code']);
        $this->assertEquals('13', $parsed['code_autonomous_community']);
        $this->assertEquals('28', $parsed['code_province']);
        $this->assertEquals('079', $parsed['code_municipality']);
        $this->assertEquals('000', $parsed['code_aggregate']);
        $this->assertEquals('000', $parsed['code_zone']);
        $this->assertEquals('012', $parsed['code_polygon']);
        $this->assertEquals('00045', $parsed['code_plot']);
        $this->assertEquals('003', $parsed['code_enclosure']);
    }

    public function test_parse_sigpac_code_handles_edge_cases(): void
    {
        // Código con todos los valores en 0 (24 dígitos)
        $code = '000000000000000000000000';
        $parsed = SigpacCode::parseSigpacCode($code);

        $this->assertEquals('000000000000000000000000', $parsed['code']);
        $this->assertEquals('00', $parsed['code_autonomous_community']);
        $this->assertEquals('00', $parsed['code_province']);

        // Código con valores máximos (24 dígitos)
        $code = '999999999999999999999999';
        $parsed = SigpacCode::parseSigpacCode($code);

        $this->assertEquals('999999999999999999999999', $parsed['code']);
        $this->assertEquals('999', $parsed['code_aggregate']);
        $this->assertEquals('999', $parsed['code_zone']);
        $this->assertEquals('999', $parsed['code_polygon']);
    }

    // ============================================
    // Tests para validateSigpacFormat()
    // ============================================

    public function test_validate_sigpac_format_returns_true_for_valid_code(): void
    {
        $this->assertTrue(SigpacCode::validateSigpacFormat('000000000000000000000000')); // 24 dígitos
        $this->assertTrue(SigpacCode::validateSigpacFormat('00-00-000-000-000-000-00000-000')); // con guiones
        $this->assertTrue(SigpacCode::validateSigpacFormat('13-28-079-000-000-012-00045-003'));
    }

    public function test_validate_sigpac_format_returns_false_for_invalid_length(): void
    {
        $this->assertFalse(SigpacCode::validateSigpacFormat('1328079001200045003'));     // 19 dígitos (formato viejo)
        $this->assertFalse(SigpacCode::validateSigpacFormat('1328079000000012000450030')); // 25 dígitos
    }

    public function test_validate_sigpac_format_returns_false_for_non_numeric(): void
    {
        $this->assertFalse(SigpacCode::validateSigpacFormat('13-28-079-000-000-012-00045-ABC'));
        $this->assertFalse(SigpacCode::validateSigpacFormat('13-28-079-000-000-012-00045-00X'));
    }

    // ============================================
    // Tests para getFormattedCodeAttribute()
    // ============================================

    public function test_formatted_code_returns_raw_for_invalid_length(): void
    {
        $sigpacCode = SigpacCode::create([
            'code' => '12345', // Longitud incorrecta
        ]);

        $this->assertEquals('12345', $sigpacCode->formatted_code);
    }

    public function test_formatted_code_returns_na_for_null_code(): void
    {
        $sigpacCode = new SigpacCode;
        $sigpacCode->code = null;

        $this->assertEquals('N/A', $sigpacCode->formatted_code);
    }

    public function test_formatted_code_formats_correctly(): void
    {
        $sigpacCode = SigpacCode::create([
            'code' => '132807900000001200045003',
        ]);

        $this->assertEquals('13-28-079-000-000-012-00045-003', $sigpacCode->formatted_code);
    }

    // ============================================
    // Tests para getFullCodeAttribute()
    // ============================================

    public function test_full_code_returns_code_when_present(): void
    {
        $sigpacCode = SigpacCode::create([
            'code' => '132807900000001200045003',
        ]);

        $this->assertEquals('132807900000001200045003', $sigpacCode->full_code);
    }

    public function test_full_code_constructs_from_fields_when_code_missing(): void
    {
        $sigpacCode = SigpacCode::create([
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ]);

        $this->assertNotEmpty($sigpacCode->full_code);
    }

    // ============================================
    // Tests para relaciones
    // ============================================

    public function test_sigpac_code_can_have_multiple_plots(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $sigpacCode = SigpacCode::create([
            'code' => '132807900000001200045003',
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_aggregate' => '0',
            'code_zone' => '0',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ]);

        $plot1 = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $plot2 = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $sigpacCode->plots()->attach([$plot1->id, $plot2->id]);

        $this->assertCount(2, $sigpacCode->plots);
        $this->assertTrue($sigpacCode->plots->contains($plot1));
        $this->assertTrue($sigpacCode->plots->contains($plot2));
    }

    public function test_sigpac_code_relationship_with_plots_via_multipart(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $sigpacCode = SigpacCode::create([
            'code' => '132807900000001200045003',
            'code_autonomous_community' => '13',
            'code_province' => '28',
            'code_municipality' => '079',
            'code_aggregate' => '0',
            'code_zone' => '0',
            'code_polygon' => '12',
            'code_plot' => '00045',
            'code_enclosure' => '003',
        ]);

        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $sigpacCode->plots()->attach($plot->id);

        $this->assertCount(1, $sigpacCode->plots);
        $this->assertEquals($plot->id, $sigpacCode->plots->first()->id);
    }
}
