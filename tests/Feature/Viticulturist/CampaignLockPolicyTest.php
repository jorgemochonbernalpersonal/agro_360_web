<?php

namespace Tests\Feature\Viticulturist;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que CampaignPolicy bloquea update/delete en campañas cerradas (locked_at).
 */
class CampaignLockPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $other;

    private Campaign $unlocked;

    private Campaign $locked;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);
        $this->other = User::factory()->create(['role' => 'viticulturist', 'email_verified_at' => now()]);

        $this->unlocked = Campaign::factory()->create([
            'viticulturist_id' => $this->owner->id,
            'year' => now()->year,
            'locked_at' => null,
        ]);

        $this->locked = Campaign::factory()->create([
            'viticulturist_id' => $this->owner->id,
            'year' => now()->year - 1,
            'locked_at' => now()->subDays(30),
        ]);
    }

    public function test_owner_can_update_unlocked_campaign(): void
    {
        $this->assertTrue($this->owner->can('update', $this->unlocked));
    }

    public function test_owner_cannot_update_locked_campaign(): void
    {
        $this->assertFalse($this->owner->can('update', $this->locked));
    }

    public function test_owner_can_delete_unlocked_campaign(): void
    {
        $this->assertTrue($this->owner->can('delete', $this->unlocked));
    }

    public function test_owner_cannot_delete_locked_campaign(): void
    {
        $this->assertFalse($this->owner->can('delete', $this->locked));
    }

    public function test_other_viticulturist_cannot_update_any_campaign(): void
    {
        $this->assertFalse($this->other->can('update', $this->unlocked));
        $this->assertFalse($this->other->can('update', $this->locked));
    }

    public function test_other_viticulturist_cannot_delete_any_campaign(): void
    {
        $this->assertFalse($this->other->can('delete', $this->unlocked));
        $this->assertFalse($this->other->can('delete', $this->locked));
    }
}
