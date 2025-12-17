<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WineryViticulturistTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_constants_are_correct(): void
    {
        $this->assertEquals('own', WineryViticulturist::SOURCE_OWN);
        $this->assertEquals('supervisor', WineryViticulturist::SOURCE_SUPERVISOR);
        $this->assertEquals('viticulturist', WineryViticulturist::SOURCE_VITICULTURIST);
    }

    public function test_scopeVisibleTo_returns_created_viticulturists(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created1 = User::factory()->create(['role' => 'viticulturist']);
        $created2 = User::factory()->create(['role' => 'viticulturist']);
        $other = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $created1->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        WineryViticulturist::create([
            'viticulturist_id' => $created2->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Otro viticultor no relacionado
        WineryViticulturist::create([
            'viticulturist_id' => $other->id,
            'parent_viticulturist_id' => $other->id,
            'assigned_by' => $other->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $visible = WineryViticulturist::visibleTo($creator)->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains('viticulturist_id', $created1->id));
        $this->assertTrue($visible->contains('viticulturist_id', $created2->id));
        $this->assertFalse($visible->contains('viticulturist_id', $other->id));
    }

    public function test_scopeVisibleTo_returns_supervisor_pool_when_has_supervisor(): void
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

    public function test_scopeVisibleTo_returns_winery_viticulturists_when_has_winery(): void
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

    public function test_scopeVisibleTo_excludes_self(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $other = User::factory()->create(['role' => 'viticulturist']);

        WineryViticulturist::create([
            'viticulturist_id' => $other->id,
            'parent_viticulturist_id' => $viticulturist->id,
            'assigned_by' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $visible = WineryViticulturist::visibleTo($viticulturist)->get();

        $this->assertCount(1, $visible);
        $this->assertFalse($visible->contains('viticulturist_id', $viticulturist->id));
    }

    public function test_scopeEditableBy_returns_only_created_viticulturists(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create(['role' => 'viticulturist']);
        $winery = User::factory()->create(['role' => 'winery']);
        $wineryViticulturist = User::factory()->create(['role' => 'viticulturist']);

        // Viticultor creado por el creator
        WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        // Viticultor de winery (no editable)
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $wineryViticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $editable = WineryViticulturist::editableBy($creator)->get();

        $this->assertCount(1, $editable);
        $this->assertEquals($created->id, $editable->first()->viticulturist_id);
    }

    public function test_isVisibleTo_returns_true_for_created_viticulturists(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->assertTrue($relation->isVisibleTo($creator));
    }

    public function test_isVisibleTo_returns_false_for_unrelated_viticulturists(): void
    {
        $creator = User::factory()->create(['role' => 'viticulturist']);
        $created = User::factory()->create(['role' => 'viticulturist']);
        $unrelated = User::factory()->create(['role' => 'viticulturist']);

        $relation = WineryViticulturist::create([
            'viticulturist_id' => $created->id,
            'parent_viticulturist_id' => $creator->id,
            'assigned_by' => $creator->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
        ]);

        $this->assertFalse($relation->isVisibleTo($unrelated));
    }

    public function test_isOwn_returns_true_for_own_source(): void
    {
        $relation = WineryViticulturist::create([
            'viticulturist_id' => User::factory()->create(['role' => 'viticulturist'])->id,
            'winery_id' => User::factory()->create(['role' => 'winery'])->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => User::factory()->create(['role' => 'winery'])->id,
        ]);

        $this->assertTrue($relation->isOwn());
        $this->assertFalse($relation->isFromSupervisor());
        $this->assertFalse($relation->isFromViticulturist());
    }

    public function test_isFromSupervisor_returns_true_for_supervisor_source(): void
    {
        $relation = WineryViticulturist::create([
            'viticulturist_id' => User::factory()->create(['role' => 'viticulturist'])->id,
            'supervisor_id' => User::factory()->create(['role' => 'supervisor'])->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'assigned_by' => User::factory()->create(['role' => 'supervisor'])->id,
        ]);

        $this->assertFalse($relation->isOwn());
        $this->assertTrue($relation->isFromSupervisor());
        $this->assertFalse($relation->isFromViticulturist());
    }

    public function test_isFromViticulturist_returns_true_for_viticulturist_source(): void
    {
        $relation = WineryViticulturist::create([
            'viticulturist_id' => User::factory()->create(['role' => 'viticulturist'])->id,
            'parent_viticulturist_id' => User::factory()->create(['role' => 'viticulturist'])->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => User::factory()->create(['role' => 'viticulturist'])->id,
        ]);

        $this->assertFalse($relation->isOwn());
        $this->assertFalse($relation->isFromSupervisor());
        $this->assertTrue($relation->isFromViticulturist());
    }
}

