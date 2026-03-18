<?php

namespace Tests\Feature\Winery\Documents;

use App\Livewire\Winery\Documents\Create;
use App\Livewire\Winery\Documents\Edit;
use App\Livewire\Winery\Documents\Index;
use App\Models\User;
use App\Models\WineryDocument;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class DocumentsTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_winery_can_view_index(): void
    {
        $this->get(route('winery.documents.index'))->assertOk();
    }

    public function test_guest_cannot_access(): void
    {
        $this->app['auth']->guard()->logout();

        $this->get(route('winery.documents.index'))->assertRedirect();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('title', '')
            ->set('document_type', '')
            ->call('save')
            ->assertHasErrors(['title', 'document_type']);
    }

    public function test_winery_can_create_winery_document(): void
    {
        $firstType = array_key_first(WineryDocument::DOCUMENT_TYPES);

        Livewire::test(Create::class)
            ->set('title', 'Licencia de actividad')
            ->set('document_type', $firstType)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_documents', [
            'user_id'       => $this->winery->id,
            'title'         => 'Licencia de actividad',
            'document_type' => $firstType,
        ]);
    }

    public function test_winery_can_edit_winery_document(): void
    {
        $firstType = array_key_first(WineryDocument::DOCUMENT_TYPES);

        $document = WineryDocument::create([
            'user_id'       => $this->winery->id,
            'title'         => 'Título Original',
            'document_type' => $firstType,
            'active'        => true,
        ]);

        Livewire::test(Edit::class, ['wineryDocument' => $document])
            ->set('title', 'Título Actualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_documents', [
            'id'    => $document->id,
            'title' => 'Título Actualizado',
        ]);
    }

    public function test_winery_cannot_edit_other_winery_winery_document(): void
    {
        $firstType = array_key_first(WineryDocument::DOCUMENT_TYPES);

        $document = WineryDocument::create([
            'user_id'       => $this->winery->id,
            'title'         => 'Documento Protegido',
            'document_type' => $firstType,
            'active'        => true,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.documents.edit', $document))
            ->assertForbidden();
    }

    public function test_winery_can_delete_winery_document(): void
    {
        $firstType = array_key_first(WineryDocument::DOCUMENT_TYPES);

        $document = WineryDocument::create([
            'user_id'       => $this->winery->id,
            'title'         => 'Documento a Eliminar',
            'document_type' => $firstType,
            'active'        => true,
        ]);

        Livewire::test(Index::class)
            ->call('delete', $document->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('winery_documents', ['id' => $document->id]);
    }
}
