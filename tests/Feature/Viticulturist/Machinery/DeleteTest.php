<?php

namespace Tests\Feature\Viticulturist\Machinery;

use App\Livewire\Viticulturist\Machinery\Index;
use App\Models\Machinery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for Viticulturist Machinery delete functionality.
 */
class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private function makeViticulturist(): User
    {
        return User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);
    }

    private function makeMachinery(int $viticulturistId): Machinery
    {
        return Machinery::create([
            'viticulturist_id' => $viticulturistId,
            'name'             => 'Tractor Test',
            'type'             => 'tractor',
            'active'           => true,
        ]);
    }

    // ── happy path ────────────────────────────────────────────────────────────

    public function test_can_delete_machinery_without_activities(): void
    {
        $viticulturist = $this->makeViticulturist();
        $machinery     = $this->makeMachinery($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $machinery->id);

        $this->assertDatabaseMissing('machinery', ['id' => $machinery->id]);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_delete_another_viticulturists_machinery(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeViticulturist();
        $machinery     = $this->makeMachinery($other->id);

        $this->actingAs($viticulturist);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $machinery->id);
    }
}
