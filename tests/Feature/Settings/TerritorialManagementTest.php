<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\TerritorialManagement;
use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Site;
use App\Models\SoilType;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class TerritorialManagementTest extends ViticulturistTestCase
{
    public function test_can_add_site(): void
    {
        $v = $this->makeViticulturist();
        $municipality = $this->getMunicipality();
        $this->actingAs($v);

        Livewire::test(TerritorialManagement::class)
            ->set('selectedMunicipalityId', (string) $municipality->id)
            ->set('newSiteName', 'Paraje El Pino')
            ->call('addSite')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sites', [
            'user_id' => $v->id,
            'name' => 'Paraje El Pino',
            'municipality_id' => $municipality->id,
        ]);
    }

    public function test_add_site_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(TerritorialManagement::class)
            ->set('selectedMunicipalityId', '')
            ->set('newSiteName', '')
            ->call('addSite')
            ->assertHasErrors(['selectedMunicipalityId', 'newSiteName']);
    }

    public function test_can_delete_own_site(): void
    {
        $v = $this->makeViticulturist();
        $municipality = $this->getMunicipality();
        $site = Site::create([
            'user_id' => $v->id,
            'name' => 'Paraje a Eliminar',
            'municipality_id' => $municipality->id,
        ]);
        $this->actingAs($v);

        Livewire::test(TerritorialManagement::class)
            ->call('deleteSite', $site->id);

        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    public function test_cannot_delete_other_users_site(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $municipality = $this->getMunicipality();
        $site = Site::create([
            'user_id' => $other->id,
            'name' => 'Paraje Ajeno',
            'municipality_id' => $municipality->id,
        ]);
        $this->actingAs($v);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(TerritorialManagement::class)
            ->call('deleteSite', $site->id);
    }

    public function test_can_add_catalog_item(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(TerritorialManagement::class)
            ->set('newName', 'Arcilloso Test')
            ->call('addCatalogItem', 'soil_types')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('soil_types', [
            'user_id' => $v->id,
            'name' => 'Arcilloso Test',
        ]);
    }

    public function test_can_delete_own_catalog_item(): void
    {
        $v = $this->makeViticulturist();
        $soilType = SoilType::create([
            'user_id' => $v->id,
            'name' => 'Tipo a Eliminar',
            'active' => true,
        ]);
        $this->actingAs($v);

        Livewire::test(TerritorialManagement::class)
            ->call('deleteCatalogItem', 'soil_types', $soilType->id);

        $this->assertDatabaseMissing('soil_types', ['id' => $soilType->id]);
    }

    public function test_cannot_delete_other_users_catalog_item(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $soilType = SoilType::create([
            'user_id' => $other->id,
            'name' => 'Tipo Ajeno',
            'active' => true,
        ]);
        $this->actingAs($v);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(TerritorialManagement::class)
            ->call('deleteCatalogItem', 'soil_types', $soilType->id);
    }

    private function getMunicipality(): Municipality
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TEST'], ['name' => 'Comunidad Test']);
        $province = Province::firstOrCreate(['code' => 'TEST'], ['name' => 'Provincia Test', 'autonomous_community_id' => $ac->id]);

        return Municipality::firstOrCreate(['code' => 'TEST'], ['name' => 'Municipio Test', 'province_id' => $province->id]);
    }
}
