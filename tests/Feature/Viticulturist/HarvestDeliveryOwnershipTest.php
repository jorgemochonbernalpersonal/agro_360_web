<?php

namespace Tests\Feature\Viticulturist;

use App\Models\HarvestDelivery;
use App\Models\User;
use Tests\Feature\ViticulturistTestCase;

class HarvestDeliveryOwnershipTest extends ViticulturistTestCase
{
    private User $owner;

    private User $other;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->makeViticulturist();
        $this->other = $this->makeOtherViticulturist();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_owner_can_update_and_delete(): void
    {
        $delivery = new HarvestDelivery(['viticulturist_id' => $this->owner->id]);

        $this->assertTrue($this->owner->can('update', $delivery));
        $this->assertTrue($this->owner->can('delete', $delivery));
    }

    public function test_other_viticulturist_cannot_update_or_delete(): void
    {
        $delivery = new HarvestDelivery(['viticulturist_id' => $this->owner->id]);

        $this->assertFalse($this->other->can('update', $delivery));
        $this->assertFalse($this->other->can('delete', $delivery));
    }

    public function test_admin_can_update_and_delete(): void
    {
        $delivery = new HarvestDelivery(['viticulturist_id' => $this->owner->id]);

        $this->assertTrue($this->admin->can('update', $delivery));
        $this->assertTrue($this->admin->can('delete', $delivery));
    }
}
