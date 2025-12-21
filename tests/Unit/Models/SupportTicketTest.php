<?php

namespace Tests\Unit\Models;

use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_ticket_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertEquals($user->id, $ticket->user->id);
    }

    public function test_support_ticket_belongs_to_assigned_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $assignedUser = User::factory()->create(['role' => 'admin']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'assigned_to' => $assignedUser->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertEquals($assignedUser->id, $ticket->assignedTo->id);
    }

    public function test_support_ticket_has_many_comments(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $comment1 = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Comment 1',
        ]);

        $comment2 = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Comment 2',
        ]);

        $this->assertCount(2, $ticket->comments);
        $this->assertTrue($ticket->comments->contains('id', $comment1->id));
        $this->assertTrue($ticket->comments->contains('id', $comment2->id));
    }

    public function test_scope_open_filters_open_tickets(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $openTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Open Ticket',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $closedTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Closed Ticket',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
        ]);

        $results = SupportTicket::open()->get();

        $this->assertTrue($results->contains('id', $openTicket->id));
        $this->assertFalse($results->contains('id', $closedTicket->id));
    }

    public function test_scope_closed_filters_closed_tickets(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $openTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Open Ticket',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $resolvedTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Resolved Ticket',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
        ]);

        $closedTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Closed Ticket',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'closed',
            'priority' => 'medium',
        ]);

        $results = SupportTicket::closed()->get();

        $this->assertFalse($results->contains('id', $openTicket->id));
        $this->assertTrue($results->contains('id', $resolvedTicket->id));
        $this->assertTrue($results->contains('id', $closedTicket->id));
    }

    public function test_scope_for_user_filters_by_user(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $ticket1 = SupportTicket::create([
            'user_id' => $user1->id,
            'title' => 'Ticket 1',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $ticket2 = SupportTicket::create([
            'user_id' => $user2->id,
            'title' => 'Ticket 2',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $results = SupportTicket::forUser($user1->id)->get();

        $this->assertTrue($results->contains('id', $ticket1->id));
        $this->assertFalse($results->contains('id', $ticket2->id));
    }

    public function test_scope_of_type_filters_by_type(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $bugTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Bug Ticket',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $featureTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Feature Ticket',
            'description' => 'Description',
            'type' => 'feature',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $results = SupportTicket::ofType('bug')->get();

        $this->assertTrue($results->contains('id', $bugTicket->id));
        $this->assertFalse($results->contains('id', $featureTicket->id));
    }

    public function test_scope_with_priority_filters_by_priority(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $highPriorityTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'High Priority',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $lowPriorityTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Low Priority',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'low',
        ]);

        $results = SupportTicket::withPriority('high')->get();

        $this->assertTrue($results->contains('id', $highPriorityTicket->id));
        $this->assertFalse($results->contains('id', $lowPriorityTicket->id));
    }

    public function test_resolve_method_sets_status_and_resolved_at(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertNull($ticket->resolved_at);

        $ticket->resolve();

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_close_method_sets_status_and_closed_at(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertNull($ticket->closed_at);

        $ticket->close();

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_reopen_method_resets_status_and_dates(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
            'resolved_at' => now(),
            'closed_at' => now(),
        ]);

        $ticket->reopen();

        $ticket->refresh();
        $this->assertEquals('open', $ticket->status);
        $this->assertNull($ticket->resolved_at);
        $this->assertNull($ticket->closed_at);
    }

    public function test_is_open_returns_true_when_status_is_open(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertTrue($ticket->isOpen());
    }

    public function test_is_open_returns_false_when_status_is_not_open(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
        ]);

        $this->assertFalse($ticket->isOpen());
    }

    public function test_is_closed_returns_true_for_resolved_status(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
        ]);

        $this->assertTrue($ticket->isClosed());
    }

    public function test_is_closed_returns_true_for_closed_status(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'closed',
            'priority' => 'medium',
        ]);

        $this->assertTrue($ticket->isClosed());
    }

    public function test_is_closed_returns_false_for_open_status(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertFalse($ticket->isClosed());
    }

    public function test_priority_color_returns_correct_colors(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $urgentTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Urgent',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'urgent',
        ]);

        $highTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'High',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $mediumTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Medium',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $lowTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Low',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'low',
        ]);

        $this->assertEquals('red', $urgentTicket->priority_color);
        $this->assertEquals('orange', $highTicket->priority_color);
        $this->assertEquals('yellow', $mediumTicket->priority_color);
        $this->assertEquals('gray', $lowTicket->priority_color);
    }

    public function test_status_color_returns_correct_colors(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $openTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Open',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $inProgressTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'In Progress',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);

        $resolvedTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Resolved',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
        ]);

        $closedTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Closed',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'closed',
            'priority' => 'medium',
        ]);

        $this->assertEquals('blue', $openTicket->status_color);
        $this->assertEquals('yellow', $inProgressTicket->status_color);
        $this->assertEquals('green', $resolvedTicket->status_color);
        $this->assertEquals('gray', $closedTicket->status_color);
    }

    public function test_get_type_label_returns_correct_labels(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $bugTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Bug',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $featureTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Feature',
            'description' => 'Description',
            'type' => 'feature',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $improvementTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Improvement',
            'description' => 'Description',
            'type' => 'improvement',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $questionTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Question',
            'description' => 'Description',
            'type' => 'question',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertEquals('🐛 Bug', $bugTicket->getTypeLabel());
        $this->assertEquals('✨ Nueva Funcionalidad', $featureTicket->getTypeLabel());
        $this->assertEquals('🚀 Mejora', $improvementTicket->getTypeLabel());
        $this->assertEquals('❓ Pregunta', $questionTicket->getTypeLabel());
    }

    public function test_get_status_label_returns_correct_labels(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $openTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Open',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertEquals('Abierto', $openTicket->getStatusLabel());
    }

    public function test_get_priority_label_returns_correct_labels(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $urgentTicket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Urgent',
            'description' => 'Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'urgent',
        ]);

        $this->assertEquals('🔴 Urgente', $urgentTicket->getPriorityLabel());
    }

    public function test_image_url_returns_null_when_no_image(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertNull($ticket->image_url);
    }

    public function test_image_url_returns_url_when_image_exists(): void
    {
        Storage::fake('public');
        
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'open',
            'priority' => 'medium',
            'image' => 'tickets/test.jpg',
        ]);

        $url = $ticket->image_url;
        $this->assertNotNull($url);
        $this->assertStringContainsString('tickets/test.jpg', $url);
    }

    public function test_resolved_at_and_closed_at_are_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'type' => 'bug',
            'status' => 'resolved',
            'priority' => 'medium',
            'resolved_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $ticket->resolved_at);
    }
}

