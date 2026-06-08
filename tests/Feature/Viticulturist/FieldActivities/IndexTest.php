<?php

namespace Tests\Feature\Viticulturist\FieldActivities;

use App\Livewire\Winery\FieldActivities\Index;
use App\Models\AgriculturalActivity;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_viticulturist_sees_own_activities(): void
    {
        $v = $this->makeViticulturist();
        AgriculturalActivity::factory()
            ->forViticulturist($v)
            ->create(['activity_type' => 'irrigation', 'activity_date' => now()]);

        Livewire::actingAs($v)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 1)
            ->assertViewHas('isViticulturistOnly', true);
    }

    public function test_viticulturist_does_not_see_other_viticulturists_activities(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();

        AgriculturalActivity::factory()
            ->forViticulturist($other)
            ->create(['activity_date' => now()]);

        Livewire::actingAs($v)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 0);
    }

    public function test_stats_count_activity_types(): void
    {
        $v = $this->makeViticulturist();
        AgriculturalActivity::factory()->forViticulturist($v)->create(['activity_type' => 'harvest',      'activity_date' => now()]);
        AgriculturalActivity::factory()->forViticulturist($v)->create(['activity_type' => 'phytosanitary', 'activity_date' => now()]);
        AgriculturalActivity::factory()->forViticulturist($v)->create(['activity_type' => 'irrigation',    'activity_date' => now()]);

        Livewire::actingAs($v)
            ->test(Index::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 3 && $s['harvest'] === 1 && $s['phyto'] === 1);
    }

    public function test_filter_by_activity_type(): void
    {
        $v = $this->makeViticulturist();
        AgriculturalActivity::factory()->forViticulturist($v)->create(['activity_type' => 'harvest',     'activity_date' => now()]);
        AgriculturalActivity::factory()->forViticulturist($v)->create(['activity_type' => 'irrigation',  'activity_date' => now()]);

        Livewire::actingAs($v)
            ->test(Index::class)
            ->set('activityTypeFilter', 'harvest')
            ->assertViewHas('stats', fn ($s) => $s['total'] === 1);
    }
}
