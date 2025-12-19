<?php

namespace Tests\Feature\Viticulturist\Campaign;

use App\Livewire\Viticulturist\Campaign\Edit;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function test_viticulturist_can_update_own_campaign_via_livewire(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $campaign = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Campaña Original',
            'year' => 2025,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['campaign' => $campaign])
            ->set('name', 'Campaña Actualizada')
            ->set('year', 2026)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.campaign.index'));

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Campaña Actualizada',
            'year' => 2026,
        ]);
    }

    public function test_cannot_update_campaign_to_duplicate_year_for_same_viticulturist(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $campaign2025 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2025,
        ]);

        $campaign2026 = Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2026,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['campaign' => $campaign2025])
            ->set('year', 2026)
            ->call('save')
            ->assertHasErrors(['year']);

        $this->assertEquals(2025, $campaign2025->fresh()->year);
        $this->assertEquals(2026, $campaign2026->fresh()->year);
    }
}


