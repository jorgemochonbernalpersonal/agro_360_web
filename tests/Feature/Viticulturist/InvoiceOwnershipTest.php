<?php

namespace Tests\Feature\Viticulturist;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Verifica que los componentes de facturación del viticultor rechazan
 * clientes que no pertenecen al viticultor autenticado.
 */
class InvoiceOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $viticulturist;
    private User $otherUser;
    private Client $ownClient;
    private Client $foreignClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viticulturist = User::factory()->create([
            'role'               => 'viticulturist',
            'email_verified_at'  => now(),
        ]);

        $this->otherUser = User::factory()->create([
            'role'               => 'viticulturist',
            'email_verified_at'  => now(),
        ]);

        $this->ownClient = Client::factory()->individual()->create([
            'user_id' => $this->viticulturist->id,
            'active'  => true,
        ]);

        $this->foreignClient = Client::factory()->individual()->create([
            'user_id' => $this->otherUser->id,
            'active'  => true,
        ]);
    }

    // ── Regla de ownership de client_id (idéntica en Create y Edit) ──────────

    private function makeClientOwnershipRule(): \Closure
    {
        $userId = $this->viticulturist->id;
        return function ($attribute, $value, $fail) use ($userId) {
            if ($value && !\App\Models\Client::where('id', $value)->where('user_id', $userId)->exists()) {
                $fail('El cliente seleccionado no es válido.');
            }
        };
    }

    public function test_viticulturist_invoice_create_rejects_foreign_client(): void
    {
        $this->actingAs($this->viticulturist);

        $validator = Validator::make(
            ['client_id' => $this->foreignClient->id],
            ['client_id' => ['required', $this->makeClientOwnershipRule()]]
        );

        $this->assertTrue($validator->fails());
        $this->assertNotEmpty($validator->errors()->get('client_id'));
    }

    public function test_viticulturist_invoice_create_accepts_own_client(): void
    {
        $this->actingAs($this->viticulturist);

        $validator = Validator::make(
            ['client_id' => $this->ownClient->id],
            ['client_id' => ['required', $this->makeClientOwnershipRule()]]
        );

        $this->assertFalse($validator->fails(), 'Own client should pass: ' . $validator->errors()->first('client_id'));
    }

    public function test_viticulturist_invoice_edit_rejects_foreign_client(): void
    {
        $this->actingAs($this->viticulturist);

        $validator = Validator::make(
            ['client_id' => $this->foreignClient->id],
            ['client_id' => ['required', $this->makeClientOwnershipRule()]]
        );

        $this->assertTrue($validator->fails());
        $this->assertNotEmpty($validator->errors()->get('client_id'));
    }

    public function test_viticulturist_invoice_edit_accepts_own_client(): void
    {
        $this->actingAs($this->viticulturist);

        $validator = Validator::make(
            ['client_id' => $this->ownClient->id],
            ['client_id' => ['required', $this->makeClientOwnershipRule()]]
        );

        $this->assertFalse($validator->fails(), 'Own client should pass: ' . $validator->errors()->first('client_id'));
    }

    // ── Cross-user: otro viticultor no puede usar clientes ajenos ─────────────

    public function test_other_viticulturist_cannot_use_first_viticulturists_client(): void
    {
        $this->actingAs($this->otherUser);

        $otherUserId = $this->otherUser->id;
        $rule = function ($attribute, $value, $fail) use ($otherUserId) {
            if ($value && !\App\Models\Client::where('id', $value)->where('user_id', $otherUserId)->exists()) {
                $fail('El cliente seleccionado no es válido.');
            }
        };

        $validator = Validator::make(
            ['client_id' => $this->ownClient->id], // belongs to $viticulturist
            ['client_id' => ['required', $rule]]
        );

        $this->assertTrue($validator->fails());
    }
}
