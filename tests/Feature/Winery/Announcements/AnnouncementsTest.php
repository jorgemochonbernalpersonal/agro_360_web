<?php

namespace Tests\Feature\Winery\Announcements;

use App\Livewire\Winery\Announcements\Index;
use App\Models\User;
use App\Models\WineryAnnouncement;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class AnnouncementsTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.announcements.index'))->assertOk();
    }

    public function test_publish_validates_required_fields(): void
    {
        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('title', '')
            ->set('body', '')
            ->set('type', '')
            ->set('target', '')
            ->call('publish')
            ->assertHasErrors(['title', 'body', 'type', 'target']);
    }

    public function test_publish_creates_announcement(): void
    {
        Livewire::test(Index::class)
            ->call('openCreate')
            ->set('title', 'Aviso de prueba')
            ->set('body', 'Cuerpo del aviso de prueba para la bodega.')
            ->set('type', 'info')
            ->set('target', 'all')
            ->call('publish')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_announcements', [
            'winery_id' => $this->winery->id,
            'title' => 'Aviso de prueba',
            'type' => 'info',
            'target' => 'all',
        ]);
    }

    public function test_cannot_expire_other_winery_announcement(): void
    {
        $other = $this->makeOtherWinery();
        $announcement = WineryAnnouncement::create([
            'winery_id' => $other->id,
            'title' => 'Aviso Ajeno',
            'body' => 'Contenido ajeno.',
            'type' => 'info',
            'target' => 'all',
            'published_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('expire', $announcement->id);
    }

    public function test_cannot_delete_other_winery_announcement(): void
    {
        $other = $this->makeOtherWinery();
        $announcement = WineryAnnouncement::create([
            'winery_id' => $other->id,
            'title' => 'Aviso Protegido',
            'body' => 'Contenido protegido.',
            'type' => 'info',
            'target' => 'all',
            'published_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $announcement->id);
    }
}
