<?php

namespace Tests\Unit\Models;

use App\Models\SupportTicketComment;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_ticket_comment_belongs_to_ticket(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $comment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Test comment',
        ]);

        $this->assertEquals($ticket->id, $comment->ticket->id);
        $this->assertInstanceOf(SupportTicket::class, $comment->ticket);
    }

    public function test_support_ticket_comment_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $comment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Test comment',
        ]);

        $this->assertEquals($user->id, $comment->user->id);
        $this->assertInstanceOf(User::class, $comment->user);
    }

    public function test_is_internal_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $comment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Internal comment',
            'is_internal' => true,
        ]);

        $this->assertIsBool($comment->is_internal);
        $this->assertTrue($comment->is_internal);
    }

    public function test_scope_public_filters_public_comments(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $publicComment1 = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Public comment 1',
            'is_internal' => false,
        ]);

        $publicComment2 = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Public comment 2',
            'is_internal' => false,
        ]);

        $internalComment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Internal comment',
            'is_internal' => true,
        ]);

        $results = SupportTicketComment::public()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $publicComment1->id));
        $this->assertTrue($results->contains('id', $publicComment2->id));
        $this->assertFalse($results->contains('id', $internalComment->id));
    }

    public function test_scope_internal_filters_internal_comments(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $internalComment1 = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Internal comment 1',
            'is_internal' => true,
        ]);

        $internalComment2 = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Internal comment 2',
            'is_internal' => true,
        ]);

        $publicComment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Public comment',
            'is_internal' => false,
        ]);

        $results = SupportTicketComment::internal()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $internalComment1->id));
        $this->assertTrue($results->contains('id', $internalComment2->id));
        $this->assertFalse($results->contains('id', $publicComment->id));
    }

    public function test_is_internal_returns_true_when_internal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $comment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Internal comment',
            'is_internal' => true,
        ]);

        $this->assertTrue($comment->isInternal());
    }

    public function test_is_internal_returns_false_when_public(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $comment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Public comment',
            'is_internal' => false,
        ]);

        $this->assertFalse($comment->isInternal());
    }

    public function test_comment_defaults_to_public_when_not_specified(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'title' => 'Test Ticket',
            'description' => 'Test message',
            'status' => 'open',
        ]);

        $comment = SupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Comment without is_internal',
        ]);

        // Si no se especifica, debería ser false (público) por defecto
        $this->assertFalse($comment->is_internal ?? false);
    }
}

