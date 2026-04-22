<?php

namespace Tests\Feature\Supervisor\Policies;

use App\Models\DoInspection;
use App\Models\DoLabel;
use App\Models\DoQualification;
use App\Models\User;
use App\Policies\DoInspectionPolicy;
use App\Policies\DoLabelPolicy;
use App\Policies\DoQualificationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoModelPoliciesTest extends TestCase
{
    use RefreshDatabase;

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    private function makeLabel(User $supervisor): DoLabel
    {
        return DoLabel::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $supervisor->id, // same for simplicity
            'vintage'            => now()->year,
            'quantity_requested' => 100,
            'status'             => 'pending',
        ]);
    }

    private function makeInspection(User $supervisor): DoInspection
    {
        return DoInspection::create([
            'supervisor_id'   => $supervisor->id,
            'subject_type'    => 'winery',
            'subject_id'      => $supervisor->id,
            'inspection_date' => now()->toDateString(),
            'status'          => 'scheduled',
        ]);
    }

    private function makeQualification(User $supervisor): DoQualification
    {
        return DoQualification::create([
            'supervisor_id'      => $supervisor->id,
            'winery_id'          => $supervisor->id,
            'vintage'            => now()->year,
            'wine_name'          => 'Prueba',
            'qualification_date' => now()->toDateString(),
        ]);
    }

    // ── DoLabelPolicy ─────────────────────────────────────────────────────────

    public function test_label_view_any_allows_supervisor(): void
    {
        $this->assertTrue((new DoLabelPolicy)->viewAny($this->makeUser('supervisor')));
    }

    public function test_label_view_any_allows_admin(): void
    {
        $this->assertTrue((new DoLabelPolicy)->viewAny($this->makeUser('admin')));
    }

    public function test_label_view_any_denies_winery(): void
    {
        $this->assertFalse((new DoLabelPolicy)->viewAny($this->makeUser('winery')));
    }

    public function test_label_view_any_denies_viticulturist(): void
    {
        $this->assertFalse((new DoLabelPolicy)->viewAny($this->makeUser('viticulturist')));
    }

    public function test_label_view_allows_own_supervisor(): void
    {
        $supervisor = $this->makeUser('supervisor');
        $label      = $this->makeLabel($supervisor);

        $this->assertTrue((new DoLabelPolicy)->view($supervisor, $label));
    }

    public function test_label_view_denies_other_supervisor(): void
    {
        $owner = $this->makeUser('supervisor');
        $other = $this->makeUser('supervisor');
        $label = $this->makeLabel($owner);

        $this->assertFalse((new DoLabelPolicy)->view($other, $label));
    }

    public function test_label_view_allows_admin_on_any(): void
    {
        $owner = $this->makeUser('supervisor');
        $admin = $this->makeUser('admin');
        $label = $this->makeLabel($owner);

        $this->assertTrue((new DoLabelPolicy)->view($admin, $label));
    }

    public function test_label_create_allows_supervisor(): void
    {
        $this->assertTrue((new DoLabelPolicy)->create($this->makeUser('supervisor')));
    }

    public function test_label_create_denies_winery(): void
    {
        $this->assertFalse((new DoLabelPolicy)->create($this->makeUser('winery')));
    }

    public function test_label_update_allows_own_supervisor(): void
    {
        $supervisor = $this->makeUser('supervisor');
        $label      = $this->makeLabel($supervisor);

        $this->assertTrue((new DoLabelPolicy)->update($supervisor, $label));
    }

    public function test_label_update_denies_other_supervisor(): void
    {
        $owner = $this->makeUser('supervisor');
        $other = $this->makeUser('supervisor');
        $label = $this->makeLabel($owner);

        $this->assertFalse((new DoLabelPolicy)->update($other, $label));
    }

    public function test_label_delete_mirrors_update(): void
    {
        $owner = $this->makeUser('supervisor');
        $other = $this->makeUser('supervisor');
        $label = $this->makeLabel($owner);

        $this->assertTrue((new DoLabelPolicy)->delete($owner, $label));
        $this->assertFalse((new DoLabelPolicy)->delete($other, $label));
    }

    // ── DoInspectionPolicy ────────────────────────────────────────────────────

    public function test_inspection_view_any_allows_supervisor(): void
    {
        $this->assertTrue((new DoInspectionPolicy)->viewAny($this->makeUser('supervisor')));
    }

    public function test_inspection_view_any_denies_winery(): void
    {
        $this->assertFalse((new DoInspectionPolicy)->viewAny($this->makeUser('winery')));
    }

    public function test_inspection_view_allows_own_supervisor(): void
    {
        $supervisor  = $this->makeUser('supervisor');
        $inspection  = $this->makeInspection($supervisor);

        $this->assertTrue((new DoInspectionPolicy)->view($supervisor, $inspection));
    }

    public function test_inspection_view_denies_other_supervisor(): void
    {
        $owner      = $this->makeUser('supervisor');
        $other      = $this->makeUser('supervisor');
        $inspection = $this->makeInspection($owner);

        $this->assertFalse((new DoInspectionPolicy)->view($other, $inspection));
    }

    public function test_inspection_update_allows_own_supervisor(): void
    {
        $supervisor  = $this->makeUser('supervisor');
        $inspection  = $this->makeInspection($supervisor);

        $this->assertTrue((new DoInspectionPolicy)->update($supervisor, $inspection));
    }

    public function test_inspection_update_denies_other_supervisor(): void
    {
        $owner      = $this->makeUser('supervisor');
        $other      = $this->makeUser('supervisor');
        $inspection = $this->makeInspection($owner);

        $this->assertFalse((new DoInspectionPolicy)->update($other, $inspection));
    }

    public function test_inspection_delete_allows_admin_on_any(): void
    {
        $owner      = $this->makeUser('supervisor');
        $admin      = $this->makeUser('admin');
        $inspection = $this->makeInspection($owner);

        $this->assertTrue((new DoInspectionPolicy)->delete($admin, $inspection));
    }

    // ── DoQualificationPolicy ─────────────────────────────────────────────────

    public function test_qualification_view_any_allows_supervisor(): void
    {
        $this->assertTrue((new DoQualificationPolicy)->viewAny($this->makeUser('supervisor')));
    }

    public function test_qualification_view_any_denies_viticulturist(): void
    {
        $this->assertFalse((new DoQualificationPolicy)->viewAny($this->makeUser('viticulturist')));
    }

    public function test_qualification_view_allows_own_supervisor(): void
    {
        $supervisor     = $this->makeUser('supervisor');
        $qualification  = $this->makeQualification($supervisor);

        $this->assertTrue((new DoQualificationPolicy)->view($supervisor, $qualification));
    }

    public function test_qualification_view_denies_other_supervisor(): void
    {
        $owner         = $this->makeUser('supervisor');
        $other         = $this->makeUser('supervisor');
        $qualification = $this->makeQualification($owner);

        $this->assertFalse((new DoQualificationPolicy)->view($other, $qualification));
    }

    public function test_qualification_update_allows_own_supervisor(): void
    {
        $supervisor    = $this->makeUser('supervisor');
        $qualification = $this->makeQualification($supervisor);

        $this->assertTrue((new DoQualificationPolicy)->update($supervisor, $qualification));
    }

    public function test_qualification_update_denies_other_supervisor(): void
    {
        $owner         = $this->makeUser('supervisor');
        $other         = $this->makeUser('supervisor');
        $qualification = $this->makeQualification($owner);

        $this->assertFalse((new DoQualificationPolicy)->update($other, $qualification));
    }

    public function test_qualification_delete_mirrors_update(): void
    {
        $owner         = $this->makeUser('supervisor');
        $other         = $this->makeUser('supervisor');
        $qualification = $this->makeQualification($owner);

        $this->assertTrue((new DoQualificationPolicy)->delete($owner, $qualification));
        $this->assertFalse((new DoQualificationPolicy)->delete($other, $qualification));
    }
}
