<?php

namespace Tests\Feature\Supervisor;

use App\Models\DoInspection;
use App\Models\DoLabel;
use App\Models\DoQualification;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class DashboardTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_dashboard(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.dashboard'))
            ->assertOk();
    }

    public function test_winery_cannot_access_dashboard(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.dashboard'))
            ->assertForbidden();
    }

    public function test_viticulturist_cannot_access_dashboard(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($viticulturist)
            ->get(route('supervisor.dashboard'))
            ->assertForbidden();
    }

    // ── counts ────────────────────────────────────────────────────────────────

    public function test_dashboard_shows_winery_count(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('wineryCount', 1);
    }

    public function test_dashboard_shows_viticulturist_count(): void
    {
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('viticulturistCount', 1);
    }

    public function test_dashboard_counts_only_own_wineries(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        SupervisorWinery::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'assigned_by' => $otherSupervisor->id,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('wineryCount', 0);
    }

    // ── pending qualifications ────────────────────────────────────────────────

    public function test_dashboard_shows_pending_qualifications_count(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        DoQualification::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'wine_name' => 'Vino Test',
            'result' => DoQualification::RESULT_PENDING,
        ]);

        // A non-pending qualification should not be counted
        DoQualification::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'wine_name' => 'Vino Calificado',
            'result' => DoQualification::RESULT_QUALIFIED,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingQualifications', 1);
    }

    public function test_dashboard_pending_qualifications_scoped_to_own_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        DoQualification::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'wine_name' => 'Vino Otro',
            'result' => DoQualification::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingQualifications', 0);
    }

    // ── issued labels ─────────────────────────────────────────────────────────

    public function test_dashboard_shows_issued_labels_this_year(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        DoLabel::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'quantity_requested' => 500,
            'quantity_issued' => 400,
            'quantity_stock' => 0,
            'status' => DoLabel::STATUS_ISSUED,
            'issued_at' => now(),
        ]);

        // A label from last year should not be counted
        DoLabel::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year - 1,
            'quantity_requested' => 200,
            'quantity_issued' => 200,
            'quantity_stock' => 0,
            'status' => DoLabel::STATUS_ISSUED,
            'issued_at' => now()->subYear(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('issuedLabelsThisYear', 400);
    }

    // ── pending notebook requests ─────────────────────────────────────────────

    public function test_dashboard_shows_pending_notebook_requests_count(): void
    {
        $supervisor = $this->makeSupervisor();
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingNotebookCount', 1)
            ->assertViewHas('pendingNotebookRequests', fn ($r) => $r->count() === 1);
    }

    public function test_approved_notebook_requests_not_counted_as_pending(): void
    {
        $supervisor = $this->makeSupervisor();
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingNotebookCount', 0)
            ->assertViewHas('pendingNotebookRequests', fn ($r) => $r->isEmpty());
    }

    // ── pendingRequests ───────────────────────────────────────────────────────

    public function test_dashboard_counts_pending_and_in_review_requests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_PENDING]);
        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_IN_REVIEW]);
        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_APPROVED]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingRequests', 2);
    }

    public function test_dashboard_pending_requests_isolated_from_other_supervisor(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        SupervisorRequest::create(['supervisor_id' => $otherSupervisor->id, 'winery_id' => $otherWinery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_PENDING]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingRequests', 0);
    }

    // ── overdueRequests ───────────────────────────────────────────────────────

    public function test_dashboard_counts_overdue_open_requests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_PENDING,   'due_date' => now()->subDays(3)]);
        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_IN_REVIEW, 'due_date' => now()->subDay()]);
        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_APPROVED,  'due_date' => now()->subDay()]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('overdueRequests', 2);
    }

    public function test_dashboard_overdue_excludes_future_due_dates(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        SupervisorRequest::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'type' => SupervisorRequest::TYPE_NONCONFORMITY, 'status' => SupervisorRequest::STATUS_PENDING, 'due_date' => now()->addDays(5)]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('overdueRequests', 0);
    }

    // ── pendingLabels ─────────────────────────────────────────────────────────

    public function test_dashboard_counts_pending_and_approved_labels(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => now()->year, 'quantity_requested' => 100, 'status' => DoLabel::STATUS_PENDING]);
        DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => now()->year, 'quantity_requested' => 200, 'status' => DoLabel::STATUS_APPROVED]);
        DoLabel::create(['supervisor_id' => $supervisor->id, 'winery_id' => $winery->id, 'vintage' => now()->year, 'quantity_requested' => 300, 'status' => DoLabel::STATUS_ISSUED, 'quantity_issued' => 300, 'issued_at' => now()]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingLabels', 2);
    }

    public function test_dashboard_pending_labels_isolated_from_other_supervisor(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        DoLabel::create(['supervisor_id' => $otherSupervisor->id, 'winery_id' => $otherWinery->id, 'vintage' => now()->year, 'quantity_requested' => 100, 'status' => DoLabel::STATUS_PENDING]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('pendingLabels', 0);
    }

    // ── nonCompliantInspections ───────────────────────────────────────────────

    public function test_dashboard_counts_non_compliant_inspections(): void
    {
        $supervisor = $this->makeSupervisor();

        DoInspection::create(['supervisor_id' => $supervisor->id, 'subject_type' => 'winery', 'subject_id' => $supervisor->id, 'inspection_date' => now()->toDateString(), 'status' => DoInspection::STATUS_COMPLETED, 'result' => DoInspection::RESULT_NON_COMPLIANT]);
        DoInspection::create(['supervisor_id' => $supervisor->id, 'subject_type' => 'winery', 'subject_id' => $supervisor->id, 'inspection_date' => now()->toDateString(), 'status' => DoInspection::STATUS_COMPLETED, 'result' => DoInspection::RESULT_COMPLIANT]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('nonCompliantInspections', 1);
    }

    public function test_dashboard_cancelled_non_compliant_inspections_excluded(): void
    {
        $supervisor = $this->makeSupervisor();

        DoInspection::create(['supervisor_id' => $supervisor->id, 'subject_type' => 'winery', 'subject_id' => $supervisor->id, 'inspection_date' => now()->toDateString(), 'status' => DoInspection::STATUS_CANCELLED, 'result' => DoInspection::RESULT_NON_COMPLIANT]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('nonCompliantInspections', 0);
    }

    public function test_dashboard_non_compliant_inspections_isolated_from_other_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();

        DoInspection::create(['supervisor_id' => $otherSupervisor->id, 'subject_type' => 'winery', 'subject_id' => $otherSupervisor->id, 'inspection_date' => now()->toDateString(), 'status' => DoInspection::STATUS_COMPLETED, 'result' => DoInspection::RESULT_NON_COMPLIANT]);

        $this->actingAs($supervisor);

        Livewire::test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertViewHas('nonCompliantInspections', 0);
    }
}
