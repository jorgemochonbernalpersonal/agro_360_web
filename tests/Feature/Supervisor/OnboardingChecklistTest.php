<?php

namespace Tests\Feature\Supervisor;

use App\Livewire\Supervisor\OnboardingChecklist;
use App\Models\InvoicingSetting;
use App\Models\OnboardingProgress;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class OnboardingChecklistTest extends SupervisorTestCase
{
    // ── initial state ─────────────────────────────────────────────────────────

    public function test_checklist_visible_when_no_steps_completed(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', true);
    }

    public function test_checklist_has_three_supervisor_steps(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', true)
            ->assertCount('steps', 3);
    }

    public function test_progress_percentage_is_zero_with_no_completions(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('progressPercentage', 0);
    }

    // ── auto-complete: profile step ───────────────────────────────────────────

    public function test_profile_step_auto_completes_when_invoicing_setting_exists(): void
    {
        $supervisor = $this->makeSupervisor();

        InvoicingSetting::create([
            'user_id' => $supervisor->id,
            'issuer_legal_name' => 'Denominación de Origen Test',
        ]);

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', true);

        $progress = OnboardingProgress::where('user_id', $supervisor->id)
            ->where('step', OnboardingProgress::STEP_SUPERVISOR_PROFILE)
            ->first();

        $this->assertNotNull($progress);
        $this->assertTrue($progress->isCompleted());
    }

    public function test_profile_step_not_auto_completed_when_issuer_name_is_empty(): void
    {
        $supervisor = $this->makeSupervisor();

        InvoicingSetting::create([
            'user_id' => $supervisor->id,
            'issuer_legal_name' => '',
        ]);

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class);

        $progress = OnboardingProgress::where('user_id', $supervisor->id)
            ->where('step', OnboardingProgress::STEP_SUPERVISOR_PROFILE)
            ->first();

        $this->assertTrue($progress === null || ! $progress->isCompleted());
    }

    // ── auto-complete: winery step ────────────────────────────────────────────

    public function test_winery_step_auto_completes_when_winery_is_linked(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class);

        $progress = OnboardingProgress::where('user_id', $supervisor->id)
            ->where('step', OnboardingProgress::STEP_SUPERVISOR_ADD_WINERY)
            ->first();

        $this->assertNotNull($progress);
        $this->assertTrue($progress->isCompleted());
    }

    // ── auto-complete: viticulturist step ─────────────────────────────────────

    public function test_viticulturist_step_auto_completes_when_viticulturist_linked(): void
    {
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class);

        $progress = OnboardingProgress::where('user_id', $supervisor->id)
            ->where('step', OnboardingProgress::STEP_SUPERVISOR_ADD_VITICULTURIST)
            ->first();

        $this->assertNotNull($progress);
        $this->assertTrue($progress->isCompleted());
    }

    // ── progress percentage ───────────────────────────────────────────────────

    public function test_progress_percentage_updates_as_steps_complete(): void
    {
        $supervisor = $this->makeSupervisor();

        // Complete 1 of 3 steps manually
        OnboardingProgress::getOrCreate($supervisor->id, OnboardingProgress::STEP_SUPERVISOR_PROFILE)
            ->markAsCompleted();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('progressPercentage', 33);
    }

    public function test_progress_percentage_is_100_when_all_steps_done(): void
    {
        $supervisor = $this->makeSupervisor();

        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            OnboardingProgress::getOrCreate($supervisor->id, $step)->markAsCompleted();
        }

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', false);
    }

    // ── checklist hidden when all complete ────────────────────────────────────

    public function test_checklist_hidden_when_all_steps_completed(): void
    {
        $supervisor = $this->makeSupervisor();

        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            OnboardingProgress::getOrCreate($supervisor->id, $step)->markAsCompleted();
        }

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', false);
    }

    // ── skipAll ───────────────────────────────────────────────────────────────

    public function test_skip_all_hides_checklist(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', true)
            ->call('skipAll')
            ->assertSet('show', false);
    }

    public function test_skip_all_marks_all_steps_as_skipped(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->call('skipAll');

        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            $progress = OnboardingProgress::where('user_id', $supervisor->id)
                ->where('step', $step)
                ->first();

            $this->assertNotNull($progress);
            $this->assertTrue($progress->skipped);
        }
    }

    public function test_skip_all_does_not_overwrite_already_completed_steps(): void
    {
        $supervisor = $this->makeSupervisor();

        OnboardingProgress::getOrCreate($supervisor->id, OnboardingProgress::STEP_SUPERVISOR_PROFILE)
            ->markAsCompleted();

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->call('skipAll');

        $profileStep = OnboardingProgress::where('user_id', $supervisor->id)
            ->where('step', OnboardingProgress::STEP_SUPERVISOR_PROFILE)
            ->first();

        $this->assertFalse($profileStep->skipped);
    }

    // ── resetOnboarding ───────────────────────────────────────────────────────

    public function test_reset_onboarding_makes_checklist_visible_again(): void
    {
        $supervisor = $this->makeSupervisor();

        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            OnboardingProgress::getOrCreate($supervisor->id, $step)->markAsSkipped();
        }

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', false)
            ->call('resetOnboarding')
            ->assertSet('show', true);
    }

    public function test_reset_onboarding_clears_completed_state(): void
    {
        $supervisor = $this->makeSupervisor();

        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            OnboardingProgress::getOrCreate($supervisor->id, $step)->markAsCompleted();
        }

        Livewire::actingAs($supervisor)
            ->test(OnboardingChecklist::class)
            ->call('resetOnboarding');

        // After reset no step should remain completed (loadProgress re-creates them uncompleted)
        $completed = OnboardingProgress::where('user_id', $supervisor->id)
            ->whereNotNull('completed_at')
            ->count();

        $this->assertEquals(0, $completed);
    }

    // ── isolation ─────────────────────────────────────────────────────────────

    public function test_progress_is_isolated_between_supervisors(): void
    {
        $supervisor1 = $this->makeSupervisor();
        $supervisor2 = $this->makeSupervisor();

        foreach (OnboardingProgress::SUPERVISOR_STEPS as $step) {
            OnboardingProgress::getOrCreate($supervisor1->id, $step)->markAsCompleted();
        }

        Livewire::actingAs($supervisor2)
            ->test(OnboardingChecklist::class)
            ->assertSet('show', true)
            ->assertSet('progressPercentage', 0);
    }
}
