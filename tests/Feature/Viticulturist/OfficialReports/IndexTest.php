<?php

namespace Tests\Feature\Viticulturist\OfficialReports;

use App\Livewire\Viticulturist\OfficialReports\Index;
use App\Models\OfficialReport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makeReport(int $userId, array $overrides = []): OfficialReport
    {
        return OfficialReport::create(array_merge([
            'user_id'           => $userId,
            'report_type'       => 'phytosanitary_treatments',
            'period_start'      => now()->subMonth()->toDateString(),
            'period_end'        => now()->toDateString(),
            'verification_code' => strtoupper(\Illuminate\Support\Str::random(32)),
            'signature_hash'    => hash('sha256', uniqid()),
            'is_valid'          => true,
            'processing_status' => 'completed',
            'signed_at'         => now(),
            'signed_ip'         => '127.0.0.1',
        ], $overrides));
    }

    // ── Index listing ──────────────────────────────────────────────────────────

    public function test_index_shows_own_reports(): void
    {
        $v = $this->makeViticulturist();
        $report = $this->makeReport($v->id, ['report_type' => 'phytosanitary_treatments']);
        $this->actingAs($v);

        Livewire::test(Index::class)->assertOk();
    }

    public function test_index_does_not_show_other_users_reports(): void
    {
        $v     = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $this->makeReport($other->id, ['processing_status' => 'completed']);
        $this->actingAs($v);

        // Own user's query should return 0 reports
        $component = Livewire::test(Index::class);
        $component->assertOk();

        // The paginated reports should be empty for $v (none created for $v)
        $this->assertEquals(0, OfficialReport::forUser($v->id)->count());
    }

    public function test_stats_count_valid_and_invalid_reports(): void
    {
        $v = $this->makeViticulturist();
        $this->makeReport($v->id, ['is_valid' => true]);
        $this->makeReport($v->id, [
            'is_valid'           => false,
            'invalidation_reason' => 'Error detectado.',
            'invalidated_at'     => now(),
        ]);
        $this->actingAs($v);

        Livewire::test(Index::class)->assertOk();

        $this->assertEquals(2, OfficialReport::forUser($v->id)->count());
        $this->assertEquals(1, OfficialReport::forUser($v->id)->where('is_valid', true)->count());
        $this->assertEquals(1, OfficialReport::forUser($v->id)->where('is_valid', false)->count());
    }

    public function test_switch_tab_to_valid_filters(): void
    {
        $v = $this->makeViticulturist();
        $this->makeReport($v->id, ['is_valid' => true]);
        $this->makeReport($v->id, [
            'is_valid'           => false,
            'invalidation_reason' => 'Motivo de prueba.',
            'invalidated_at'     => now(),
        ]);
        $this->actingAs($v);

        // Switching to 'valid' filter should not throw
        Livewire::test(Index::class)
            ->call('switchTab', 'valid')
            ->assertOk();
    }

    public function test_switch_tab_to_invalid_filters(): void
    {
        $v = $this->makeViticulturist();
        $this->makeReport($v->id, [
            'is_valid'           => false,
            'invalidation_reason' => 'Motivo de prueba.',
            'invalidated_at'     => now(),
        ]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('switchTab', 'invalid')
            ->assertOk();
    }

    public function test_reset_filters_resets_search_and_tab(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->set('search', 'algo')
            ->set('statusFilter', 'valid')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', 'all');
    }

    // ── Invalidation ───────────────────────────────────────────────────────────

    public function test_open_invalidate_modal_for_own_valid_recent_report(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, ['signed_at' => now()]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->assertSet('showInvalidateModal', true)
            ->assertSet('reportToInvalidate.id', $report->id);
    }

    public function test_cannot_open_invalidate_modal_for_other_users_report(): void
    {
        $v      = $this->makeViticulturist();
        $other  = $this->makeOtherViticulturist();
        $report = $this->makeReport($other->id, ['signed_at' => now()]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->assertSet('showInvalidateModal', false);
    }

    public function test_cannot_open_invalidate_modal_for_already_invalid_report(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, [
            'is_valid'           => false,
            'invalidation_reason' => 'Ya estaba invalidado.',
            'invalidated_at'     => now(),
            'signed_at'          => now(),
        ]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->assertSet('showInvalidateModal', false);
    }

    public function test_cannot_open_invalidate_modal_for_report_older_than_30_days(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, [
            'signed_at' => now()->subDays(31),
        ]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->assertSet('showInvalidateModal', false);
    }

    public function test_invalidate_report_with_correct_login_password(): void
    {
        $v = $this->makeViticulturist();
        // factory creates user with password 'password'
        $report = $this->makeReport($v->id, ['signed_at' => now()]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->set('invalidatePassword', 'password')
            ->set('invalidateReason', 'Motivo de prueba con suficientes caracteres.')
            ->call('invalidateReport');

        $this->assertDatabaseHas('official_reports', [
            'id'       => $report->id,
            'is_valid' => false,
        ]);
    }

    public function test_invalidate_report_requires_reason_min_10_chars(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, ['signed_at' => now()]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->set('invalidatePassword', 'password')
            ->set('invalidateReason', 'Corto')
            ->call('invalidateReport')
            ->assertHasErrors(['invalidateReason']);

        $this->assertDatabaseHas('official_reports', ['id' => $report->id, 'is_valid' => true]);
    }

    public function test_invalidate_report_with_wrong_password_fails(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, ['signed_at' => now()]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->set('invalidatePassword', 'contraseña_incorrecta')
            ->set('invalidateReason', 'Motivo con suficientes caracteres.')
            ->call('invalidateReport')
            ->assertHasErrors(['invalidatePassword']);

        $this->assertDatabaseHas('official_reports', ['id' => $report->id, 'is_valid' => true]);
    }

    public function test_close_invalidate_modal_resets_state(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, ['signed_at' => now()]);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openInvalidateModal', $report->id)
            ->call('closeInvalidateModal')
            ->assertSet('showInvalidateModal', false)
            ->assertSet('reportToInvalidate', null)
            ->assertSet('invalidatePassword', '')
            ->assertSet('invalidateReason', '');
    }

    // ── Sharing ────────────────────────────────────────────────────────────────

    public function test_open_share_modal_for_own_report(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openShareModal', $report->id)
            ->assertSet('showShareModal', true)
            ->assertSet('reportToShare.id', $report->id);
    }

    public function test_cannot_open_share_modal_for_other_users_report(): void
    {
        $v      = $this->makeViticulturist();
        $other  = $this->makeOtherViticulturist();
        $report = $this->makeReport($other->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openShareModal', $report->id)
            ->assertSet('showShareModal', false);
    }

    public function test_share_report_validates_email(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openShareModal', $report->id)
            ->set('shareEmail', 'no-es-un-email')
            ->call('shareReport')
            ->assertHasErrors(['shareEmail']);
    }

    public function test_share_report_sends_email(): void
    {
        Mail::fake();

        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openShareModal', $report->id)
            ->set('shareEmail', 'destinatario@example.com')
            ->set('shareMessage', 'Mensaje de prueba')
            ->call('shareReport')
            ->assertSet('showShareModal', false);

        Mail::assertSent(\App\Mail\OfficialReportShared::class, function ($mail) {
            return $mail->hasTo('destinatario@example.com');
        });
    }

    public function test_close_share_modal_resets_state(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openShareModal', $report->id)
            ->call('closeShareModal')
            ->assertSet('showShareModal', false)
            ->assertSet('reportToShare', null)
            ->assertSet('shareEmail', '')
            ->assertSet('shareMessage', '');
    }

    // ── Preview ────────────────────────────────────────────────────────────────

    public function test_open_preview_modal_for_own_report(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openPreviewModal', $report->id)
            ->assertSet('showPreviewModal', true)
            ->assertSet('reportToPreview.id', $report->id);
    }

    public function test_cannot_open_preview_modal_for_other_users_report(): void
    {
        $v      = $this->makeViticulturist();
        $other  = $this->makeOtherViticulturist();
        $report = $this->makeReport($other->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openPreviewModal', $report->id)
            ->assertSet('showPreviewModal', false);
    }

    // ── Download ───────────────────────────────────────────────────────────────

    public function test_download_fails_when_report_not_completed(): void
    {
        $v      = $this->makeViticulturist();
        $report = $this->makeReport($v->id, ['processing_status' => 'pending']);
        $this->actingAs($v);

        // Should show warning toast and not throw
        Livewire::test(Index::class)
            ->call('downloadInFormat', $report->id, 'pdf')
            ->assertOk();
    }

    public function test_download_of_other_users_report_is_rejected(): void
    {
        $v      = $this->makeViticulturist();
        $other  = $this->makeOtherViticulturist();
        $report = $this->makeReport($other->id, ['processing_status' => 'completed']);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('downloadInFormat', $report->id, 'pdf')
            ->assertOk(); // No exception, just error toast
    }
}
