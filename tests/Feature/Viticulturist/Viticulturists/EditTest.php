<?php

namespace Tests\Feature\Viticulturist\Viticulturists;

use App\Livewire\Viticulturist\Viticulturists\Edit;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for the Viticulturist sub-viticulturist edit component.
 *
 * Covers: field population, update, validation, and authorization.
 */
class EditTest extends TestCase
{
    use RefreshDatabase;

    // ── field population ──────────────────────────────────────────────────────

    public function test_edit_form_populates_with_current_values(): void
    {
        $parent = $this->makeViticulturist();
        $sub = $this->makeSubViticulturist($parent);

        $this->actingAs($parent);

        Livewire::test(Edit::class, ['viticulturist' => $sub])
            ->assertSet('name', $sub->name)
            ->assertSet('email', $sub->email);
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_can_update_name_and_email(): void
    {
        $parent = $this->makeViticulturist();
        $sub = $this->makeSubViticulturist($parent);

        $this->actingAs($parent);

        Livewire::test(Edit::class, ['viticulturist' => $sub])
            ->set('name', 'Nuevo Nombre')
            ->set('email', 'nuevo@email.com')
            ->call('save')
            ->assertHasNoErrors();

        $fresh = $sub->fresh();
        $this->assertSame('Nuevo Nombre', $fresh->name);
        $this->assertSame('nuevo@email.com', $fresh->email);
    }

    // ── validation ────────────────────────────────────────────────────────────

    public function test_save_requires_name(): void
    {
        $parent = $this->makeViticulturist();
        $sub = $this->makeSubViticulturist($parent);

        $this->actingAs($parent);

        Livewire::test(Edit::class, ['viticulturist' => $sub])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_email_must_be_unique(): void
    {
        $parent = $this->makeViticulturist();
        $sub = $this->makeSubViticulturist($parent);
        $existing = $this->makeViticulturist(); // has its own email

        $this->actingAs($parent);

        Livewire::test(Edit::class, ['viticulturist' => $sub])
            ->set('name', $sub->name)
            ->set('email', $existing->email)
            ->call('save')
            ->assertHasErrors(['email']);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_edit_sub_viticulturist_owned_by_another(): void
    {
        $parent = $this->makeViticulturist();
        $otherParent = $this->makeViticulturist();
        $sub = $this->makeSubViticulturist($otherParent);

        $this->actingAs($parent);

        Livewire::test(Edit::class, ['viticulturist' => $sub])
            ->assertStatus(403);
    }

    private function makeViticulturist(): User
    {
        return User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Creates a sub-viticulturist (ghost) owned by the given parent.
     */
    private function makeSubViticulturist(User $parent): User
    {
        $sub = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => false,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $sub->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'parent_viticulturist_id' => $parent->id,
            'assigned_by' => $parent->id,
        ]);

        return $sub;
    }
}
