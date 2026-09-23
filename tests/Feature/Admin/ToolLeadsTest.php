<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Leads\Index as LeadsIndex;
use App\Livewire\Tools\LeadCapture;
use App\Models\ToolLead;
use App\Models\User;
use App\Notifications\NewToolLeadNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ToolLeadsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_admin_can_access_leads_page(): void
    {
        $this->makeLead(['email' => 'visible@example.com']);

        $this->actingAs($this->admin)
            ->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee('visible@example.com');
    }

    public function test_non_admin_cannot_access_leads_page(): void
    {
        $winery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);

        $this->actingAs($winery)
            ->get(route('admin.leads.index'))
            ->assertForbidden();
    }

    public function test_filters_by_status_tool_and_search(): void
    {
        $this->makeLead(['email' => 'pendiente@example.com', 'tool' => 'calculadora-caldo-fitosanitario']);
        $this->makeLead(['email' => 'hecho@example.com', 'contacted_at' => now()]);

        $this->actingAs($this->admin);

        Livewire::test(LeadsIndex::class)
            ->assertViewHas('stats', fn ($s) => $s['total'] === 2 && $s['pending'] === 1)
            ->set('filterStatus', 'pending')
            ->assertSee('pendiente@example.com')
            ->assertDontSee('hecho@example.com')
            ->set('filterStatus', '')
            ->set('filterTool', 'test-cuaderno-digital-2027')
            ->assertSee('hecho@example.com')
            ->assertDontSee('pendiente@example.com')
            ->set('filterTool', '')
            ->set('search', 'pendiente')
            ->assertSee('pendiente@example.com')
            ->assertDontSee('hecho@example.com');
    }

    public function test_admin_toggles_contacted(): void
    {
        $lead = $this->makeLead();
        $this->actingAs($this->admin);

        Livewire::test(LeadsIndex::class)->call('toggleContacted', $lead->id);
        $this->assertNotNull($lead->fresh()->contacted_at);

        Livewire::test(LeadsIndex::class)->call('toggleContacted', $lead->id);
        $this->assertNull($lead->fresh()->contacted_at);
    }

    public function test_readonly_admin_cannot_toggle_contacted(): void
    {
        $readonly = User::factory()->create(['role' => 'admin', 'is_readonly_admin' => true, 'email_verified_at' => now()]);
        $lead = $this->makeLead();
        $this->actingAs($readonly);

        Livewire::test(LeadsIndex::class)
            ->call('toggleContacted', $lead->id)
            ->assertForbidden();

        $this->assertNull($lead->fresh()->contacted_at);
    }

    public function test_export_downloads_csv(): void
    {
        $this->makeLead(['email' => 'csv@example.com', 'context' => ['score' => 63]]);
        $this->actingAs($this->admin);

        Livewire::test(LeadsIndex::class)
            ->call('export')
            ->assertFileDownloaded('contactos-herramientas-'.now()->format('Y-m-d').'.csv');
    }

    public function test_new_lead_notifies_support_email(): void
    {
        Notification::fake();
        config(['support.email' => 'soporte@agro365.test']);

        Livewire::test(LeadCapture::class, ['tool' => 'test-cuaderno-digital-2027', 'context' => ['score' => 50]])
            ->set('email', 'nuevo@example.com')
            ->set('consent', true)
            ->call('save')
            ->assertSet('sent', true);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewToolLeadNotification::class,
            function (NewToolLeadNotification $notification, array $channels, AnonymousNotifiable $notifiable) {
                $mail = $notification->toMail($notifiable);

                return $notifiable->routes['mail'] === 'soporte@agro365.test'
                    && str_contains($mail->subject, 'nuevo@example.com');
            }
        );
    }

    private function makeLead(array $attributes = []): ToolLead
    {
        return ToolLead::create(array_merge([
            'email' => 'lead'.uniqid().'@example.com',
            'tool' => 'test-cuaderno-digital-2027',
            'consented_at' => now(),
        ], $attributes));
    }
}
