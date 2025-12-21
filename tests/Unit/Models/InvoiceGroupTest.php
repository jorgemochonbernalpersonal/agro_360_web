<?php

namespace Tests\Unit\Models;

use App\Models\InvoiceGroup;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_group_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $group = InvoiceGroup::create([
            'user_id' => $user->id,
            'name' => 'Grupo de Facturas 2025',
            'description' => 'Facturas del año 2025',
        ]);

        $this->assertEquals($user->id, $group->user->id);
    }

    public function test_invoice_group_has_many_invoices(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $group = InvoiceGroup::create([
            'user_id' => $user->id,
            'name' => 'Grupo de Facturas 2025',
            'description' => 'Facturas del año 2025',
        ]);

        $client = \App\Models\Client::factory()->create(['user_id' => $user->id]);

        $invoice1 = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_group_id' => $group->id,
        ]);

        $invoice2 = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_group_id' => $group->id,
        ]);

        $this->assertCount(2, $group->invoices);
        $this->assertTrue($group->invoices->contains('id', $invoice1->id));
        $this->assertTrue($group->invoices->contains('id', $invoice2->id));
    }

    public function test_scope_for_user_filters_by_user(): void
    {
        $user1 = User::factory()->create(['role' => 'viticulturist']);
        $user2 = User::factory()->create(['role' => 'viticulturist']);

        $group1 = InvoiceGroup::create([
            'user_id' => $user1->id,
            'name' => 'Grupo Usuario 1',
            'description' => 'Descripción',
        ]);

        $group2 = InvoiceGroup::create([
            'user_id' => $user2->id,
            'name' => 'Grupo Usuario 2',
            'description' => 'Descripción',
        ]);

        $results = InvoiceGroup::forUser($user1->id)->get();

        $this->assertTrue($results->contains('id', $group1->id));
        $this->assertFalse($results->contains('id', $group2->id));
    }

    public function test_invoice_group_can_have_null_description(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $group = InvoiceGroup::create([
            'user_id' => $user->id,
            'name' => 'Grupo sin descripción',
        ]);

        $this->assertNull($group->description);
    }

    public function test_invoice_group_can_have_empty_description(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $group = InvoiceGroup::create([
            'user_id' => $user->id,
            'name' => 'Grupo con descripción vacía',
            'description' => '',
        ]);

        $this->assertEquals('', $group->description);
    }
}

