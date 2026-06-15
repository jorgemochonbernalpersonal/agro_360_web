<?php

namespace Tests\Feature\Viticulturist\Pac\Payments;

use App\Livewire\Viticulturist\Pac\Payments\Index;
use App\Models\PacPayment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_renders_for_viticulturist(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Index::class)->assertStatus(200);
    }

    public function test_can_create_payment(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('year', now()->year)
            ->set('payment_type', 'basic_payment')
            ->set('amount', '1200.00')
            ->set('payment_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pac_payments', [
            'viticulturist_id' => $v->id,
            'payment_type' => 'basic_payment',
            'amount' => 1200.00,
        ]);
    }

    public function test_can_delete_own_payment(): void
    {
        $v = $this->makeViticulturist();
        $payment = $this->makePayment($v->id);
        $this->actingAs($v);

        Livewire::test(Index::class)
            ->call('delete', $payment->id);

        $this->assertDatabaseMissing('pac_payments', ['id' => $payment->id]);
    }

    public function test_cannot_delete_other_viticulturist_payment(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $payment = $this->makePayment($other->id);
        $this->actingAs($v);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $payment->id);
    }

    public function test_cannot_edit_other_viticulturist_payment(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $payment = $this->makePayment($other->id);
        $this->actingAs($v);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('openEdit', $payment->id);
    }

    public function test_cannot_update_other_viticulturist_payment(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $payment = $this->makePayment($other->id);
        $this->actingAs($v);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->set('editingId', $payment->id)
            ->set('year', now()->year)
            ->set('payment_type', 'basic_payment')
            ->set('amount', '999.00')
            ->set('payment_date', now()->toDateString())
            ->call('save');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePayment(int $viticulturistId): PacPayment
    {
        return PacPayment::create([
            'viticulturist_id' => $viticulturistId,
            'year' => now()->year,
            'payment_type' => 'basic_payment',
            'amount' => 500.00,
            'payment_date' => now()->toDateString(),
        ]);
    }
}
