<?php

namespace Tests\Feature\Winery;

use App\Models\User;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use Tests\Feature\WineryTestCase;

/**
 * Verifica que WineTransferPolicy, WineLossPolicy y WineFermentationControlPolicy
 * conceden ownership al dueño del vino asociado y lo deniegan a otros.
 *
 * Usa setRelation() para cargar la relación wine sin necesitar schema completo
 * de las tablas de proceso (sin factories).
 */
class WineProcessOwnershipTest extends WineryTestCase
{
    private User $owner;

    private User $other;

    private User $admin;

    private Wine $wine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->makeWinery();
        $this->other = $this->makeOtherWinery();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->wine  = Wine::create(['user_id' => $this->owner->id, 'name' => 'Tempranillo Test']);
    }

    // ── WineTransferPolicy ────────────────────────────────────────────────────

    public function test_wine_transfer_owner_can_update_other_cannot(): void
    {
        $transfer = (new WineTransfer(['wine_id' => $this->wine->id]))
            ->setRelation('wine', $this->wine);

        $this->assertTrue($this->owner->can('update', $transfer));
        $this->assertFalse($this->other->can('update', $transfer));
        $this->assertTrue($this->admin->can('update', $transfer));
    }

    public function test_wine_transfer_with_null_wine_is_denied(): void
    {
        $transfer = (new WineTransfer(['wine_id' => null]))
            ->setRelation('wine', null);

        $this->assertFalse($this->owner->can('update', $transfer));
        $this->assertFalse($this->other->can('update', $transfer));
        $this->assertTrue($this->admin->can('update', $transfer));
    }

    // ── WineLossPolicy ────────────────────────────────────────────────────────

    public function test_wine_loss_owner_can_update_other_cannot(): void
    {
        $loss = (new WineLoss(['wine_id' => $this->wine->id]))
            ->setRelation('wine', $this->wine);

        $this->assertTrue($this->owner->can('update', $loss));
        $this->assertFalse($this->other->can('update', $loss));
        $this->assertTrue($this->admin->can('update', $loss));
    }

    public function test_wine_loss_with_null_wine_is_denied(): void
    {
        $loss = (new WineLoss(['wine_id' => null]))
            ->setRelation('wine', null);

        $this->assertFalse($this->owner->can('update', $loss));
        $this->assertFalse($this->other->can('update', $loss));
        $this->assertTrue($this->admin->can('update', $loss));
    }

    // ── WineFermentationControlPolicy ─────────────────────────────────────────

    public function test_fermentation_control_owner_can_update_other_cannot(): void
    {
        $control = (new WineFermentationControl(['wine_id' => $this->wine->id]))
            ->setRelation('wine', $this->wine);

        $this->assertTrue($this->owner->can('update', $control));
        $this->assertFalse($this->other->can('update', $control));
        $this->assertTrue($this->admin->can('update', $control));
    }

    public function test_fermentation_control_with_null_wine_is_denied(): void
    {
        $control = (new WineFermentationControl(['wine_id' => null]))
            ->setRelation('wine', null);

        $this->assertFalse($this->owner->can('update', $control));
        $this->assertFalse($this->other->can('update', $control));
        $this->assertTrue($this->admin->can('update', $control));
    }
}
