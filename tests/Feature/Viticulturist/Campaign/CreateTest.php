<?php

namespace Tests\Feature\Viticulturist\Campaign;

use App\Livewire\Viticulturist\Campaign\Create;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_viticulturist_can_create_campaign_via_livewire(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Campaña Test E2E')
            ->set('year', now()->year + 1)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.campaign.index'));

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Campaña Test E2E',
            'year' => now()->year + 1,
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_validation_fails_with_invalid_data(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('year', 1990) // menor que 2000
            ->call('save')
            ->assertHasErrors(['name', 'year']);

        $this->assertSame(0, Campaign::count());
    }

    public function test_cannot_create_duplicate_year_campaign_for_same_viticulturist(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        Campaign::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => 2028,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('name', 'Otra Campaña 2028')
            ->set('year', 2028)
            ->call('save')
            ->assertHasErrors(['year']);

        $this->assertEquals(1, Campaign::forViticulturist($viticulturist->id)->forYear(2028)->count());
    }
}


