<?php

namespace Tests\Feature\Viticulturist;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use Tests\Feature\ViticulturistTestCase;

class PlotPlantingHarvestSummaryTest extends ViticulturistTestCase
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

    private function plantingOwnedBy(User $user): PlotPlanting
    {
        $plot = (new Plot(['viticulturist_id' => $user->id]));

        return (new PlotPlanting())->setRelation('plot', $plot);
    }

    public function test_owner_can_view_harvest_summary(): void
    {
        $planting = $this->plantingOwnedBy($this->owner);

        $this->assertTrue($this->owner->can('viewHarvestSummary', $planting));
    }

    public function test_other_viticulturist_cannot_view_harvest_summary(): void
    {
        $planting = $this->plantingOwnedBy($this->owner);

        $this->assertFalse($this->other->can('viewHarvestSummary', $planting));
    }

    public function test_admin_can_view_harvest_summary(): void
    {
        $planting = $this->plantingOwnedBy($this->owner);

        $this->assertTrue($this->admin->can('viewHarvestSummary', $planting));
    }
}
