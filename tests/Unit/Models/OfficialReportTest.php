<?php

namespace Tests\Unit\Models;

use App\Models\OfficialReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_report_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $this->assertEquals($user->id, $report->user->id);
        $this->assertInstanceOf(User::class, $report->user);
    }

    public function test_official_report_belongs_to_invalidator(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $invalidator = User::factory()->create(['role' => 'admin']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'is_valid' => false,
            'invalidated_at' => now(),
            'invalidated_by' => $invalidator->id,
        ]);

        $this->assertEquals($invalidator->id, $report->invalidator->id);
    }

    public function test_generate_verification_code_creates_unique_code(): void
    {
        $code1 = OfficialReport::generateVerificationCode();
        $code2 = OfficialReport::generateVerificationCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertEquals(32, strlen($code1));
        $this->assertEquals(32, strlen($code2));
        $this->assertTrue(ctype_alnum($code1));
        $this->assertTrue(ctype_alnum($code2));
    }

    public function test_generate_temporary_hash_creates_unique_hash(): void
    {
        $hash1 = OfficialReport::generateTemporaryHash();
        $hash2 = OfficialReport::generateTemporaryHash();

        $this->assertNotEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1)); // SHA-256 produces 64 character hex string
        $this->assertEquals(64, strlen($hash2));
    }

    public function test_generate_signature_hash_creates_consistent_hash(): void
    {
        $data = [
            'report_type' => 'phytosanitary_treatments',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
            'user_id' => 1,
        ];

        $result1 = OfficialReport::generateSignatureHash($data);
        $result2 = OfficialReport::generateSignatureHash($data);

        // Debe generar el mismo hash para los mismos datos
        $this->assertEquals($result1['hash'], $result2['hash']);
        $this->assertArrayHasKey('hash', $result1);
        $this->assertArrayHasKey('nonce', $result1);
        $this->assertArrayHasKey('version', $result1);
    }

    public function test_verify_signature_hash_validates_correctly(): void
    {
        $data = [
            'report_type' => 'phytosanitary_treatments',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
        ];

        $signature = OfficialReport::generateSignatureHash($data);

        $this->assertTrue(OfficialReport::verifySignatureHash($data, $signature['hash']));
    }

    public function test_verify_signature_hash_rejects_modified_data(): void
    {
        $originalData = [
            'report_type' => 'phytosanitary_treatments',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
        ];

        $signature = OfficialReport::generateSignatureHash($originalData);

        $modifiedData = [
            'report_type' => 'phytosanitary_treatments',
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-30', // Modified
        ];

        $this->assertFalse(OfficialReport::verifySignatureHash($modifiedData, $signature['hash']));
    }

    public function test_get_verification_url_attribute(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $url = $report->verification_url;

        $this->assertStringContainsString('/verify-report/', $url);
        $this->assertStringContainsString($report->verification_code, $url);
    }

    public function test_is_valid_returns_true_when_valid(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'is_valid' => true,
            'invalidated_at' => null,
        ]);

        $this->assertTrue($report->isValid());
    }

    public function test_is_valid_returns_false_when_invalidated(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'is_valid' => false,
            'invalidated_at' => now(),
        ]);

        $this->assertFalse($report->isValid());
    }

    public function test_can_be_invalidated_returns_true_for_recent_reports(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now()->subDays(10), // 10 days ago
            'is_valid' => true,
        ]);

        $this->assertTrue($report->canBeInvalidated());
    }

    public function test_can_be_invalidated_returns_false_for_old_reports(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now()->subDays(35), // 35 days ago (more than 30)
            'is_valid' => true,
        ]);

        $this->assertFalse($report->canBeInvalidated());
    }

    public function test_get_report_type_name_attribute(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $this->assertEquals('Tratamientos Fitosanitarios', $report->report_type_name);
    }

    public function test_get_report_icon_attribute(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $this->assertEquals('🧪', $report->report_icon);
    }

    public function test_date_fields_are_cast_to_date(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $this->assertInstanceOf(Carbon::class, $report->period_start);
        $this->assertInstanceOf(Carbon::class, $report->period_end);
    }

    public function test_datetime_fields_are_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signed_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $report->signed_at);
    }

    public function test_boolean_fields_are_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'is_valid' => true,
        ]);

        $this->assertIsBool($report->is_valid);
        $this->assertTrue($report->is_valid);
    }

    public function test_array_fields_are_cast_to_array(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $report = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'signature_metadata' => ['key' => 'value'],
            'report_metadata' => ['data' => 'test'],
        ]);

        $this->assertIsArray($report->signature_metadata);
        $this->assertIsArray($report->report_metadata);
        $this->assertEquals('value', $report->signature_metadata['key']);
    }

    public function test_scope_for_user_filters_by_user(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $report1 = OfficialReport::create([
            'user_id' => $user1->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $report2 = OfficialReport::create([
            'user_id' => $user2->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
        ]);

        $results = OfficialReport::forUser($user1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($report1->id, $results->first()->id);
    }

    public function test_scope_valid_filters_valid_reports(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $validReport = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'is_valid' => true,
            'invalidated_at' => null,
        ]);

        $invalidReport = OfficialReport::create([
            'user_id' => $user->id,
            'report_type' => 'phytosanitary_treatments',
            'period_start' => now()->startOfYear(),
            'period_end' => now()->endOfYear(),
            'verification_code' => OfficialReport::generateVerificationCode(),
            'signature_hash' => OfficialReport::generateTemporaryHash(),
            'is_valid' => false,
            'invalidated_at' => now(),
        ]);

        $results = OfficialReport::valid()->get();

        $this->assertTrue($results->contains('id', $validReport->id));
        $this->assertFalse($results->contains('id', $invalidReport->id));
    }
}

