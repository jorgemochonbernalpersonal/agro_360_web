<?php

namespace Tests\Feature\Personal;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_viticulturist_without_winery_or_supervisor_sees_only_created(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $created1 = User::factory()->create(['role' => 'viticulturist']);
        $created2 = User::factory()->create(['role' => 'viticulturist']);
        $other = User::factory()->create(['role' => 'viticulturist']);

        // Viticultores creados por el viticultor
        WineryViticulturist::create([
            'viticulturist_id' => $created1->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created2->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Otro viticultor no relacionado
        WineryViticulturist::create([
            'viticulturist_id' => $other->id,
            'parent_viticulturist_id' => $other->id,
            'assigned_by' => $other->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $visible = WineryViticulturist::visibleTo($viticulturist)->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains('viticulturist_id', $created1->id));
        $this->assertTrue($visible->contains('viticulturist_id', $created2->id));
        $this->assertFalse($visible->contains('viticulturist_id', $other->id));
    }

    public function test_viticulturist_with_supervisor_sees_supervisor_pool(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $poolViticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $poolViticulturist2 = User::factory()->create(['role' => 'viticulturist']);

        // Asignar supervisor al viticultor
        WineryViticulturist::create([
            'viticulturist_id' => $viticulturist->id,
            'supervisor_id' => $supervisor->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $supervisor->id,
        ]);

        // Viticultores del pool del supervisor
        WineryViticulturist::create([
            'viticulturist_id' => $poolViticulturist1->id,
            'supervisor_id' => $supervisor->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $supervisor->id,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $poolViticulturist2->id,
            'supervisor_id' => $supervisor->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => $supervisor->id,
        ]);

        $visible = WineryViticulturist::visibleTo($viticulturist)->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains('viticulturist_id', $poolViticulturist1->id));
        $this->assertTrue($visible->contains('viticulturist_id', $poolViticulturist2->id));
    }

    public function test_viticulturist_with_winery_sees_winery_viticulturists(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $wineryViticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $wineryViticulturist2 = User::factory()->create(['role' => 'viticulturist']);

        // Asignar winery al viticultor
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        // Otros viticultores de la winery
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $wineryViticulturist1->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $wineryViticulturist2->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $visible = WineryViticulturist::visibleTo($viticulturist)->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains('viticulturist_id', $wineryViticulturist1->id));
        $this->assertTrue($visible->contains('viticulturist_id', $wineryViticulturist2->id));
    }

    public function test_viticulturist_can_edit_only_created_viticulturists(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create(['role' => 'viticulturist']);
        $winery = User::factory()->create(['role' => 'winery']);
        $wineryViticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Asignar creator a la winery para que pueda ver los viticultores de la winery
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $creator->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        // Viticultor creado por el creator
        $createdRelation = WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Viticultor de winery (no editable)
        $wineryRelation = WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $wineryViticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $editable = WineryViticulturist::editableBy($creator)->get();

        $this->assertCount(1, $editable);
        $this->assertEquals($created->id, $editable->first()->viticulturist_id);
        $this->assertTrue($createdRelation->isVisibleTo($creator));
        $this->assertTrue($wineryRelation->isVisibleTo($creator)); // Visible pero no editable
    }

    public function test_visibility_respects_winery_filter(): void
    {
        $winery1 = User::factory()->create(['role' => 'winery']);
        $winery2 = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $winery1Viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $winery2Viticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Asignar ambas wineries al viticultor
        WineryViticulturist::create([
            'winery_id' => $winery1->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery1->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery2->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery2->id,
        ]);

        // Viticultores de cada winery
        WineryViticulturist::create([
            'winery_id' => $winery1->id,
            'viticulturist_id' => $winery1Viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery1->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery2->id,
            'viticulturist_id' => $winery2Viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery2->id,
        ]);

        // Sin filtro de winery
        $visibleAll = WineryViticulturist::visibleTo($viticulturist)->get();
        $this->assertCount(2, $visibleAll);

        // Con filtro de winery1
        $visibleWinery1 = WineryViticulturist::visibleTo($viticulturist, $winery1->id)->get();
        $this->assertCount(1, $visibleWinery1);
        $this->assertEquals($winery1Viticulturist->id, $visibleWinery1->first()->viticulturist_id);

        // Con filtro de winery2
        $visibleWinery2 = WineryViticulturist::visibleTo($viticulturist, $winery2->id)->get();
        $this->assertCount(1, $visibleWinery2);
        $this->assertEquals($winery2Viticulturist->id, $visibleWinery2->first()->viticulturist_id);
    }
}

