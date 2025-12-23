<?php

namespace Tests\Unit\Models;

use App\Models\DigitalSignature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DigitalSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_signature_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'test-password-123',
        ]);

        $this->assertEquals($user->id, $signature->user->id);
        $this->assertInstanceOf(User::class, $signature->user);
    }

    public function test_signature_password_is_hashed_automatically(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'plain-password-123',
        ]);

        // El password debe estar hasheado, no en texto plano
        $this->assertNotEquals('plain-password-123', $signature->signature_password);
        $this->assertTrue(Hash::check('plain-password-123', $signature->signature_password));
    }

    public function test_signature_password_is_hidden_from_serialization(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'test-password',
        ]);

        $array = $signature->toArray();

        $this->assertArrayNotHasKey('signature_password', $array);
    }

    public function test_verify_password_returns_true_for_correct_password(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'correct-password',
        ]);

        $this->assertTrue($signature->verifyPassword('correct-password'));
    }

    public function test_verify_password_returns_false_for_incorrect_password(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'correct-password',
        ]);

        $this->assertFalse($signature->verifyPassword('wrong-password'));
    }

    public function test_verify_password_returns_false_for_empty_password(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'correct-password',
        ]);

        $this->assertFalse($signature->verifyPassword(''));
    }

    public function test_for_user_returns_signature_when_exists(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'test-password',
        ]);

        $found = DigitalSignature::forUser($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($signature->id, $found->id);
    }

    public function test_for_user_returns_null_when_not_exists(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $found = DigitalSignature::forUser($user->id);

        $this->assertNull($found);
    }

    public function test_create_or_update_for_user_creates_new_signature(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::createOrUpdateForUser($user->id, 'new-password');

        $this->assertNotNull($signature);
        $this->assertEquals($user->id, $signature->user_id);
        $this->assertTrue($signature->verifyPassword('new-password'));
    }

    public function test_create_or_update_for_user_updates_existing_signature(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $originalSignature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'old-password',
        ]);

        $updatedSignature = DigitalSignature::createOrUpdateForUser($user->id, 'new-password');

        $this->assertEquals($originalSignature->id, $updatedSignature->id);
        $this->assertTrue($updatedSignature->verifyPassword('new-password'));
        $this->assertFalse($updatedSignature->verifyPassword('old-password'));
    }

    public function test_create_or_update_for_user_hashes_password_automatically(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::createOrUpdateForUser($user->id, 'plain-password');

        // El password debe estar hasheado
        $this->assertNotEquals('plain-password', $signature->signature_password);
        $this->assertTrue(Hash::check('plain-password', $signature->signature_password));
    }

    public function test_multiple_users_can_have_different_signatures(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $signature1 = DigitalSignature::create([
            'user_id' => $user1->id,
            'signature_password' => 'password-user1',
        ]);

        $signature2 = DigitalSignature::create([
            'user_id' => $user2->id,
            'signature_password' => 'password-user2',
        ]);

        $this->assertNotEquals($signature1->id, $signature2->id);
        $this->assertTrue($signature1->verifyPassword('password-user1'));
        $this->assertTrue($signature2->verifyPassword('password-user2'));
        $this->assertFalse($signature1->verifyPassword('password-user2'));
    }

    public function test_timestamps_are_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $signature = DigitalSignature::create([
            'user_id' => $user->id,
            'signature_password' => 'test-password',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $signature->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $signature->updated_at);
    }
}

