# Plan de Tests — Módulos Viticultor

**Estado actual:** Nivel 4 completado — 166 tests passing
**Base:** `tests/Feature/ViticulturistTestCase.php` (makeViticulturist / makeOtherViticulturist)

---

## Completado ✅

| Módulo | Create | Edit | Index | Tests |
|--------|--------|------|-------|-------|
| Exploitations | ✅ 5 | ✅ 5 | ✅ 4 | 14 |
| FieldEquipment | ✅ 6 | ✅ 5 | ✅ 5 | 16 |
| FieldApplicators | ✅ 7 | ✅ 5 | ✅ 5 | 17 |
| AdvisoryMemberships | ✅ 5 | ✅ 5 | ✅ 4 | 14 |
| CommercialAuthorizations | ✅ 5 | ✅ 5 | ✅ 5 | 15 |
| EnergyUsages | ✅ 6 | ✅ 5 | ✅ 5 | 16 |
| CueExports | ✅ 5 | ✅ 6 | ✅ 6 | 17 |
| PlotEnvironments | ✅ 5 | ✅ 5 | ✅ 4 | 14 |
| ResidueAnalyses | ✅ 4 | ✅ 5 | ✅ 4 | 13 |
| ResidueManagements | ✅ 6 | ✅ 5 | ✅ 4 | 15 |
| MarketedHarvests | ✅ 5 | ✅ 5 | ✅ 4 | 14 |
| **Total** | | | | **166** |

---

## Nivel 2 — Sin geo seeders, FK simples

**Dependencias:** `Campaign::create()` directo (no necesita seeders geográficos).
**Patrón helper:** `makeCampaign(int $viticulturistId, int $year = 2024): Campaign`

### AdvisoryMemberships (~14 tests)

**Fixtures:**
```php
private function makeMembership(int $viticulturistId): AdvisoryMembership
{
    return AdvisoryMembership::create([
        'viticulturist_id' => $viticulturistId,
        'advisor_name'     => 'Asesor Test',
        'license_number'   => 'LIC-001',
        'specialty'        => 'phytosanitary',
        'active'           => true,
    ]);
}
private function makeCampaign(int $viticulturistId, int $year = 2024): Campaign
{
    return Campaign::create([
        'viticulturist_id' => $viticulturistId,
        'year'             => $year,
        'name'             => "Campaña $year",
    ]);
}
```

**CreateTest (~5 tests):**
- `test_can_create_membership` — campos requeridos → redirect index
- `test_validates_required_fields` — advisor_name='', license_number='' → hasErrors
- `test_invalid_specialty_rejected` — specialty='invalid' → hasErrors
- `test_all_valid_specialties_accepted` — iterar SPECIALTIES keys
- `test_optional_fields_saved` — company_name, phone, email

**EditTest (~5 tests):**
- `test_mount_fills_fields` — assertSet advisor_name, license_number, specialty
- `test_can_update_membership` — set nuevos valores → assertDatabaseHas
- `test_validates_required_fields_on_update` — advisor_name='', license_number='' → hasErrors
- `test_invalid_email_rejected` — email='not-an-email' → hasErrors
- `test_cannot_edit_other_viticulturists_membership` — HTTP route → assertStatus(403)

**IndexTest (~4 tests):**
- `test_index_shows_active_memberships` — assertSee advisor_name
- `test_deactivate_sets_active_to_false` — call deactivate → assertDatabaseHas active=false
- `test_deactivated_disappears_from_list` — call deactivate → assertDontSee
- `test_cannot_deactivate_other_viticulturists_membership` — expectException ModelNotFoundException

---

### EnergyUsages (~16 tests)

**Nota importante:** `mount()` llama `Campaign::getOrCreateActiveForYear()` — necesita que exista una Campaign para el viticulturist o se creará automáticamente (OK para tests, no rompe).
`recalculate()` es lógica pura en el componente — testeable con `updatedQuantity` / `updatedEnergyType`.

**Fixtures:**
```php
private function makeEnergyUsage(int $viticulturistId, int $campaignId): EnergyUsage
{
    return EnergyUsage::create([
        'viticulturist_id' => $viticulturistId,
        'campaign_id'      => $campaignId,
        'date'             => '2024-06-15',
        'energy_type'      => 'diesel',
        'unit'             => 'liters',
        'quantity'         => 100,
        'active'           => true,
    ]);
}
```

**CreateTest (~6 tests):**
- `test_can_create_energy_usage` — campos requeridos → redirect index
- `test_validates_required_fields` — campaign_id='', quantity='' → hasErrors
- `test_quantity_must_be_positive` — quantity=0 → hasErrors
- `test_co2_recalculates_on_quantity_change` — set quantity → assertSet co2_kg_equivalent (diesel: 2.65 kg/L)
- `test_unit_changes_with_energy_type` — updatedEnergyType('electricity') → assertSet unit='kwh'
- `test_total_cost_recalculates` — set quantity=10, cost_per_unit=1.5 → assertSet total_cost='15'

**EditTest (~5 tests):**
- `test_mount_fills_fields` — assertSet energy_type, quantity, unit
- `test_can_update_energy_usage` — set nuevos valores → assertDatabaseHas
- `test_validates_required_fields_on_update`
- `test_recalculate_still_works_on_edit` — updatedCostPerUnit → assertSet total_cost
- `test_cannot_edit_other_viticulturists_usage` — HTTP route → assertStatus(403)

**IndexTest (~5 tests):**
- `test_index_shows_active_usages` — assertSee (por campo visible en tabla)
- `test_archive_sets_active_to_false` — call archive → assertDatabaseHas active=false
- `test_unarchive_restores_record` — crear inactivo, call unarchive → active=true
- `test_archived_tab_shows_inactive` — switchTab('archived') → assertSee
- `test_cannot_archive_other_viticulturists_usage` — expectException ModelNotFoundException

---

### CommercialAuthorizations (~15 tests)

**Nota importante (BUG-1 cubierto):** `performCreate/Update` verifica que `exploitation_id` pertenece al viticulturist → test de seguridad clave.

**Fixtures:**
```php
private function makeExploitation(int $viticulturistId): Exploitation
{
    return Exploitation::create([
        'viticulturist_id' => $viticulturistId,
        'exploitation_name'=> 'Explotación Test',
        'holder_name'      => 'Test Holder',
        'holder_nif'       => '12345678A',
        'is_ecological'    => false, 'is_integrated_production' => false,
        'is_quality_scheme'=> false, 'active' => true,
    ]);
}
private function makeAuthorization(int $viticulturistId): CommercialAuthorization
{
    return CommercialAuthorization::create([
        'viticulturist_id'   => $viticulturistId,
        'authorization_type' => 'do_registration',
        'issue_date'         => '2024-01-01',
        'active'             => true,
    ]);
}
```

**CreateTest (~5 tests):**
- `test_can_create_authorization` — tipo + fecha → redirect index
- `test_validates_required_fields` — authorization_type='', issue_date='' → hasErrors
- `test_expiry_must_be_after_issue` — expiry_date < issue_date → hasErrors
- `test_exploitation_scoped_to_viticulturist` — exploitation_id de otro viticulturist → throws ModelNotFoundException
- `test_all_authorization_types_accepted` — iterar AUTHORIZATION_TYPES keys

**EditTest (~5 tests):**
- `test_mount_fills_fields`
- `test_can_update_authorization`
- `test_validates_required_fields_on_update`
- `test_exploitation_ownership_enforced_on_update` — BUG-1: usar exploitation de otro viticulturist → throws
- `test_cannot_edit_other_viticulturists_authorization` — HTTP route → assertStatus(403)

**IndexTest (~5 tests):**
- `test_index_shows_active_authorizations` — assertSee
- `test_deactivate_sets_active_to_false`
- `test_deactivated_disappears_from_list`
- `test_filter_by_type` — filterType='do_registration' → solo aparece ese tipo
- `test_cannot_deactivate_other_viticulturists_authorization` — expectException ModelNotFoundException

---

### CueExports (~16 tests)

**Nota importante:**
- `mount()` en Edit: si `status !== 'draft'` → redirect. Usar `Livewire::withQueryParams` NO aplica aquí (es model binding, no queryString). Pasar `['cueExport' => $model]` directamente.
- Los flujos de estado (`markAsGenerated` / `markAsSent` / `delete`) tienen guards — testear cada caso válido e inválido.

**Fixtures:**
```php
private function makeExploitation(int $viticulturistId): Exploitation { ... }
private function makeCueExport(int $viticulturistId, int $exploitationId, string $status = 'draft'): CueExport
{
    return CueExport::create([
        'viticulturist_id' => $viticulturistId,
        'exploitation_id'  => $exploitationId,
        'campaign_year'    => 2024,
        'period_type'      => 'annual',
        'from_date'        => '2024-01-01',
        'to_date'          => '2024-12-31',
        'status'           => $status,
    ]);
}
```

**CreateTest (~5 tests):**
- `test_can_create_cue_export` — exploitation + year → redirect index
- `test_validates_required_fields` — exploitation_id='', from_date='' → hasErrors
- `test_to_date_must_be_after_from_date` — to_date < from_date → hasErrors
- `test_exploitation_must_belong_to_viticulturist` — exploitation de otro → throws ModelNotFoundException
- `test_period_type_updates_dates` — updatedPeriodType('annual') → from_date = inicio del año

**EditTest (~6 tests):**
- `test_mount_fills_fields_for_draft` — assertSet exploitation_id, campaign_year
- `test_can_update_draft_cue_export` — set nuevos valores → assertDatabaseHas
- `test_non_draft_redirects_on_mount` — status='generated' → mount hace redirect (assertRedirect no funciona en Livewire::test — verificar que el componente redirige)
- `test_validates_required_fields_on_update`
- `test_to_date_must_be_after_from_date_on_update`
- `test_cannot_edit_other_viticulturists_export` — HTTP route → assertStatus(403)

**IndexTest (~5 tests):**
- `test_index_shows_exports` — assertSee (exploitation_name o año)
- `test_mark_as_generated_transitions_draft` — call markAsGenerated → status='generated'
- `test_cannot_mark_as_generated_if_already_generated` — toastError (no lanza excepción, solo toast)
- `test_mark_as_sent_transitions_generated` — call markAsSent → status='sent'
- `test_delete_removes_draft` — call delete → assertDatabaseMissing
- `test_cannot_delete_non_draft` — status='generated', call delete → assertDatabaseHas (no eliminado)

> **Nota:** Para tests de toast: Livewire 3 usa eventos de dispatch. Verificar con `->assertDispatched()` o simplemente verificar el estado DB sin el mensaje.

---

## Nivel 3 — Requieren Plot + PlotPlanting (+ geo seeders)

**Dependencias adicionales en setUp():**
```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(\Database\Seeders\AutonomousCommunitySeeder::class);
    $this->seed(\Database\Seeders\ProvinceSeeder::class);
    $this->seed(\Database\Seeders\MunicipalitySeeder::class);
}
```

**Helper común (en `PlotEnvironmentTestCase` o repetido):**
```php
private function makePlot(int $viticulturistId): Plot
{
    return Plot::factory()->create(['viticulturist_id' => $viticulturistId]);
}
private function makePlanting(int $plotId): PlotPlanting
{
    $grape = Grape::first() ?? Grape::factory()->create();
    return PlotPlanting::create([
        'plot_id'          => $plotId,
        'grape_variety_id' => $grape->id,
        'year_planted'     => 2010,
        'active'           => true,
    ]);
}
```

### PlotEnvironments (~14 tests)

**Nota importante:** `performCreate()` usa `updateOrCreate` — crear dos veces la misma combo campaign+plot da update, no error. `EditTest` cubre `Rule::unique` con ignore (BUG-3).

**CreateTest (~5 tests):**
- `test_can_create_plot_environment` — campaign + plot → redirect index
- `test_validates_required_fields` — campaign_id='', plot_id='' → hasErrors
- `test_water_intake_distance_must_be_positive` — water_intake_distance_m=-1 → hasErrors
- `test_slope_pct_must_be_between_0_and_100` — slope_pct=150 → hasErrors
- `test_update_or_create_upserts_on_same_campaign_plot` — crear dos veces → solo 1 registro en DB

**EditTest (~5 tests):**
- `test_mount_fills_fields`
- `test_can_update_plot_environment`
- `test_duplicate_campaign_plot_rejected_on_change` — cambiar a combo ya existente → hasErrors campaign_id (Rule::unique BUG-3)
- `test_same_combo_on_own_record_is_ok` — no cambiar nada → no errors (ignore propio ID)
- `test_cannot_edit_other_viticulturists_environment` — HTTP route → assertStatus(403)

**IndexTest (~4 tests):**
- `test_index_shows_plot_environments`
- `test_delete_removes_record` — call delete → assertDatabaseMissing
- `test_cannot_delete_other_viticulturists_environment` — expectException ModelNotFoundException
- `test_filter_by_campaign` — dos registros en campaigns distintas → solo aparece el filtrado

---

### ResidueAnalyses (~14 tests)

**Nota:** `plot_planting_id` nullable. Tests básicos no necesitan PlotPlanting (sólo campaign).
Tests opcionales con PlotPlanting sí necesitan geo seeders.

**CreateTest (~5 tests):**
- `test_can_create_analysis` — campaign + laboratory_name + analysis_date → redirect
- `test_validates_required_fields` — campaign_id='', laboratory_name='', analysis_date='' → hasErrors
- `test_compliant_flag_defaults_to_true` — no setar → assertDatabaseHas overall_compliant=1
- `test_non_compliant_can_be_saved` — overall_compliant=false → assertDatabaseHas
- `test_with_plot_planting` — con planting válido → saved correctamente *(requiere geo seeder)*

**EditTest (~5 tests):**
- `test_mount_fills_fields`
- `test_can_update_analysis`
- `test_validates_required_fields_on_update`
- `test_overall_compliant_can_be_toggled`
- `test_cannot_edit_other_viticulturists_analysis` — HTTP route → assertStatus(403)

**IndexTest (~4 tests):**
- `test_index_shows_active_analyses`
- `test_deactivate_archives_analysis` — call deactivate → active=false
- `test_deactivated_disappears` — call deactivate → assertDontSee
- `test_cannot_deactivate_other_viticulturists_analysis` — expectException ModelNotFoundException

---

### ResidueManagements (~15 tests)

**Nota importante:** Validación condicional — cuando `practice_type='burning'`, `justification` es `required|min:20`. Tests clave.
`quantity_unit` valida `exists:units,symbol` — necesita sembrar la tabla `units` o mockear (verificar si hay seeder).

**Verificar antes de escribir:**
```bash
php artisan db:seed --class=UnitsSeeder --env=testing  # ¿existe?
```

**CreateTest (~6 tests):**
- `test_can_create_residue_management` — campaign + practice_type + material_type + date → redirect
- `test_validates_required_fields` — practice_type='', material_type='', date='' → hasErrors
- `test_burning_requires_justification` — practice_type='burning', justification='' → hasErrors justification
- `test_burning_justification_min_length` — justification='corto' (< 20 chars) → hasErrors
- `test_non_burning_justification_not_required` — practice_type='composting', justification='' → no errors
- `test_justification_not_saved_for_non_burning` — practice_type='composting', justification='X' → DB justification=null

**EditTest (~5 tests):**
- `test_mount_fills_fields`
- `test_can_update_residue_management`
- `test_burning_justification_required_on_update`
- `test_switch_from_burning_to_other_clears_justification_in_db`
- `test_cannot_edit_other_viticulturists_management` — HTTP route → assertStatus(403)

**IndexTest (~4 tests):**
- `test_index_shows_active_managements`
- `test_deactivate_archives_management`
- `test_filter_by_practice_type` — solo aparecen del tipo filtrado
- `test_cannot_deactivate_other_viticulturists_management`

---

## Nivel 4 — MarketedHarvests (depende de Harvest → Activity)

**Dependencias:** `Activity` + `Harvest`. Hay `HarvestFactory` y `AgriculturalActivityFactory`.

**Fixtures:**
```php
private function makeHarvest(int $viticulturistId): Harvest
{
    // Necesita: Campaign + Plot + PlotPlanting + Activity (type='harvest') + Harvest
    $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturistId]);
    $plot     = Plot::factory()->create(['viticulturist_id' => $viticulturistId]);
    // ... PlotPlanting + Activity + Harvest
    // Revisar HarvestFactory y AgriculturalActivityFactory antes de escribir
}
```

**Nota:** MarketedHarvests/Index tiene `generateInvoice()` que hace redirect → no testear lógica de Invoice, solo que el redirect ocurre.

**CreateTest (~5 tests):**
- `test_can_create_marketed_harvest` — harvest + delivery_date + quantity_kg + destination_type → redirect
- `test_validates_required_fields`
- `test_harvest_must_belong_to_viticulturist` — harvest de otro → throws ModelNotFoundException
- `test_total_value_recalculates` — updatedQuantityKg → assertSet total_value
- `test_all_destination_types_accepted`

**EditTest (~5 tests):**
- `test_mount_fills_fields`
- `test_can_update_marketed_harvest`
- `test_validates_required_fields_on_update`
- `test_harvest_ownership_enforced_on_update`
- `test_cannot_edit_other_viticulturists_harvest` — HTTP route → assertStatus(403)

**IndexTest (~4 tests):**
- `test_index_shows_entries`
- `test_delete_removes_entry` — call delete → assertDatabaseMissing
- `test_generate_invoice_redirects` — call generateInvoice → assertRedirect
- `test_cannot_delete_other_viticulturists_entry` — expectException ModelNotFoundException

---

## Resumen General

| Nivel | Módulos | Tests estimados | Dependencias |
|-------|---------|-----------------|--------------|
| 1 ✅ | Exploitations, FieldEquipment, FieldApplicators | 47 | Solo User + WineryViticulturist |
| 2 | AdvisoryMemberships, EnergyUsages, CommercialAuthorizations, CueExports | ~61 | + Campaign, Exploitation |
| 3 | PlotEnvironments, ResidueAnalyses, ResidueManagements | ~43 | + Plot, PlotPlanting (geo seeders) |
| 4 | MarketedHarvests | ~14 | + Activity, Harvest (factories) |
| **Total** | **11 módulos** | **~165** | |

---

## Notas Técnicas Clave

1. **abort(403) en mount()** swallowed por Livewire::test → testar via HTTP route `assertStatus(403)`; el `$other` debe ser `makeViticulturist()` (con WineryViticulturist) para pasar middleware
2. **DB booleans**: siempre setear explícitamente + `tap(Model::create([...]))->refresh()` para defaults
3. **CueExports Edit con status≠draft**: mount() llama `$this->redirect()` — el snapshot queda incompleto; verificar comportamiento real con `Livewire::withQueryParams` o directamente pasar status='draft' en fixture
4. **ResidueManagements con `quantity_unit`**: `exists:units,symbol` — verificar si hay seeder de units o si hay que crear Unit manualmente en el test
5. **EnergyUsage mount()**: llama `Campaign::getOrCreateActiveForYear()` — crea campaña automáticamente si no existe (OK, no bloquea test)
6. **MarketedHarvests**: depende de cadena Activity→Harvest; revisar factories antes de escribir helpers
7. **Toast assertions**: Livewire 3 dispara eventos JS — no hay `assertSee('mensaje')` para toasts; verificar estado DB en su lugar

---

## Orden de implementación sugerido

```
Nivel 2a: AdvisoryMemberships     (más simple, solo Campaign nullable)
Nivel 2b: CommercialAuthorizations (cubre BUG-1 en tests)
Nivel 2c: EnergyUsages            (recalculate logic interesting)
Nivel 2d: CueExports              (status workflow)
Nivel 3a: PlotEnvironments        (BUG-3 cubierto, geo seeders setUp)
Nivel 3b: ResidueAnalyses         (plot_planting opcional)
Nivel 3c: ResidueManagements      (burning conditional validation)
Nivel 4:  MarketedHarvests        (Harvest factory research first)
```
