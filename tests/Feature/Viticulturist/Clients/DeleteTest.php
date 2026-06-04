<?php

namespace Tests\Feature\Viticulturist\Clients;

use App\Livewire\Viticulturist\Clients\Index;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for Viticulturist Client delete functionality.
 */
class DeleteTest extends TestCase
{
    use RefreshDatabase;

    // ── happy path ────────────────────────────────────────────────────────────

    public function test_can_delete_client_without_invoices(): void
    {
        $viticulturist = $this->makeViticulturist();
        $client = $this->makeClient($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $client->id);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    // ── blocked when has invoices ──────────────────────────────────────────────

    public function test_cannot_delete_client_with_invoices(): void
    {
        $viticulturist = $this->makeViticulturist();
        $client = $this->makeClient($viticulturist->id);

        // Create a minimal invoice linked to this client
        Invoice::create([
            'user_id' => $viticulturist->id,
            'client_id' => $client->id,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'subtotal' => 0,
            'tax_base' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $client->id);

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_delete_another_users_client(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $client = $this->makeClient($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $client->id);
    }

    private function makeViticulturist(): User
    {
        return User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);
    }

    private function makeClient(int $userId): Client
    {
        return Client::create([
            'user_id' => $userId,
            'client_type' => 'individual',
            'first_name' => 'Test',
            'last_name' => 'Cliente',
            'email' => 'test@cliente.com',
            'active' => true,
        ]);
    }
}
