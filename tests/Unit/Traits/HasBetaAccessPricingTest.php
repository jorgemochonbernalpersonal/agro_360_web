<?php

namespace Tests\Unit\Traits;

use App\Models\Ability;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HasBetaAccessPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function userWithBeta(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'is_beta_user' => true,
            'beta_ends_at' => now()->addDays(30),
        ], $attrs));
    }

    private function userWithoutAccess(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'is_beta_user' => false,
            'beta_ends_at' => null,
        ], $attrs));
    }

    private function linkViticulturistToWinery(User $viticulturist, User $winery): void
    {
        WineryViticulturist::create([
            'viticulturist_id' => $viticulturist->id,
            'winery_id'        => $winery->id,
        ]);
        Cache::forget("user_{$viticulturist->id}_has_winery");
    }

    private function linkWineryToSupervisor(User $winery, User $supervisor): void
    {
        DB::table('supervisor_winery')->insert([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
        ]);
    }

    private function activeSubscriptionFor(User $user): void
    {
        Subscription::create([
            'user_id'   => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount'    => Subscription::PRICE_MONTHLY,
            'status'    => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at'   => now()->addMonth(),
        ]);
        $user->load('activeSubscription');
    }

    // ── viticulturistMonthlyPrice / viticulturistYearlyPrice ─────────────────

    public function test_precio_mensual_viticultor_independiente(): void
    {
        $user = $this->userWithBeta(['role' => 'viticulturist']);

        $this->assertSame(Subscription::PRICE_MONTHLY_INDEPENDENT, $user->viticulturistMonthlyPrice());
        $this->assertSame(Subscription::PRICE_YEARLY_INDEPENDENT, $user->viticulturistYearlyPrice());
    }

    public function test_precio_mensual_viticultor_vinculado_a_bodega(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $vit    = $this->userWithBeta(['role' => 'viticulturist']);
        $this->linkViticulturistToWinery($vit, $winery);

        $this->assertSame(Subscription::PRICE_MONTHLY_VIT_LINKED, $vit->viticulturistMonthlyPrice());
        $this->assertSame(Subscription::PRICE_YEARLY_VIT_LINKED, $vit->viticulturistYearlyPrice());
    }

    public function test_precio_mensual_productor(): void
    {
        $user = $this->userWithBeta(['role' => 'producer']);

        $this->assertSame(Subscription::PRICE_MONTHLY_PRODUCER, $user->viticulturistMonthlyPrice());
        $this->assertSame(Subscription::PRICE_YEARLY_PRODUCER, $user->viticulturistYearlyPrice());
    }

    // ── effectivePlan ─────────────────────────────────────────────────────────

    public function test_plan_efectivo_sin_acceso_es_free(): void
    {
        $user = $this->userWithoutAccess(['role' => 'viticulturist']);

        $this->assertSame('free', $user->effectivePlan());
    }

    public function test_plan_efectivo_viticultor_con_beta_es_vit_pro(): void
    {
        $user = $this->userWithBeta(['role' => 'viticulturist']);

        $this->assertSame('vit_pro', $user->effectivePlan());
    }

    public function test_plan_efectivo_productor_con_beta_es_vit_pro(): void
    {
        $user = $this->userWithBeta(['role' => 'producer']);

        $this->assertSame('vit_pro', $user->effectivePlan());
    }

    public function test_plan_efectivo_bodega_con_beta_es_winery(): void
    {
        $user = $this->userWithBeta(['role' => 'winery']);

        $this->assertSame('winery', $user->effectivePlan());
    }

    public function test_plan_efectivo_supervisor_con_beta_es_do(): void
    {
        $user = $this->userWithBeta(['role' => 'supervisor']);

        $this->assertSame('do', $user->effectivePlan());
    }

    public function test_plan_efectivo_viticultor_con_suscripcion_activa_es_vit_pro(): void
    {
        $user = $this->userWithoutAccess(['role' => 'viticulturist']);
        $this->activeSubscriptionFor($user);

        $this->assertSame('vit_pro', $user->effectivePlan());
    }

    public function test_plan_efectivo_bodega_con_beta_expirada_sin_suscripcion_es_free(): void
    {
        $user = User::factory()->create([
            'role'         => 'winery',
            'is_beta_user' => true,
            'beta_ends_at' => now()->subDay(),
        ]);

        $this->assertSame('free', $user->effectivePlan());
    }

    // ── planAbilities ─────────────────────────────────────────────────────────

    public function test_plan_free_incluye_cuaderno_digital(): void
    {
        $user = $this->userWithoutAccess(['role' => 'viticulturist']);

        $this->assertContains(Ability::DIGITAL_NOTEBOOK, $user->planAbilities());
    }

    public function test_plan_free_no_incluye_teledeteccion(): void
    {
        $user = $this->userWithoutAccess(['role' => 'viticulturist']);

        $this->assertNotContains(Ability::REMOTE_SENSING, $user->planAbilities());
    }

    public function test_plan_vit_pro_incluye_abilities_free_y_capa_a(): void
    {
        $user = $this->userWithBeta(['role' => 'viticulturist']);
        $abilities = $user->planAbilities();

        $this->assertContains(Ability::DIGITAL_NOTEBOOK, $abilities);
        $this->assertContains(Ability::REMOTE_SENSING, $abilities);
        $this->assertContains(Ability::VERIFAKTU, $abilities);
        $this->assertContains(Ability::PAC, $abilities);
        $this->assertContains(Ability::FINANCIAL_STATS, $abilities);
    }

    public function test_plan_vit_pro_no_incluye_panel_viticultores(): void
    {
        $user = $this->userWithBeta(['role' => 'viticulturist']);

        $this->assertNotContains(Ability::VITICULTURIST_PANEL, $user->planAbilities());
    }

    public function test_plan_winery_incluye_todas_las_capas(): void
    {
        $user = $this->userWithBeta(['role' => 'winery']);
        $abilities = $user->planAbilities();

        $this->assertContains(Ability::DIGITAL_NOTEBOOK, $abilities);
        $this->assertContains(Ability::REMOTE_SENSING, $abilities);
        $this->assertContains(Ability::VITICULTURIST_PANEL, $abilities);
        $this->assertContains(Ability::CONSOLIDATED_REPORTS, $abilities);
        $this->assertContains(Ability::YIELD_FORECASTS, $abilities);
    }

    public function test_plan_do_incluye_oversight_y_panel_pero_no_remote_sensing(): void
    {
        $user = $this->userWithBeta(['role' => 'supervisor']);
        $abilities = $user->planAbilities();

        $this->assertContains(Ability::DO_OVERSIGHT, $abilities);
        $this->assertContains(Ability::VITICULTURIST_PANEL, $abilities);
        $this->assertContains(Ability::CONSOLIDATED_REPORTS, $abilities);
        $this->assertNotContains(Ability::REMOTE_SENSING, $abilities);
    }

    public function test_has_plan_ability_devuelve_true_para_ability_incluida(): void
    {
        $user = $this->userWithBeta(['role' => 'winery']);

        $this->assertTrue($user->hasPlanAbility(Ability::VITICULTURIST_PANEL));
    }

    public function test_has_plan_ability_devuelve_false_para_ability_no_incluida(): void
    {
        $user = $this->userWithoutAccess(['role' => 'viticulturist']);

        $this->assertFalse($user->hasPlanAbility(Ability::REMOTE_SENSING));
    }

    // ── managedViticulturistCount / wineryTier ────────────────────────────────

    public function test_bodega_sin_viticultores_tiene_count_0(): void
    {
        $winery = $this->userWithBeta(['role' => 'winery']);

        $this->assertSame(0, $winery->managedViticulturistCount());
    }

    public function test_bodega_con_viticultores_devuelve_count_correcto(): void
    {
        $winery = $this->userWithBeta(['role' => 'winery']);
        $vit1   = User::factory()->create(['role' => 'viticulturist']);
        $vit2   = User::factory()->create(['role' => 'viticulturist']);

        $this->linkViticulturistToWinery($vit1, $winery);
        $this->linkViticulturistToWinery($vit2, $winery);

        $this->assertSame(2, $winery->managedViticulturistCount());
    }

    public function test_viticultor_siempre_devuelve_count_0(): void
    {
        $user = $this->userWithBeta(['role' => 'viticulturist']);

        $this->assertSame(0, $user->managedViticulturistCount());
    }

    public function test_winery_tier_bodega_sin_viticultores_es_solo_vino(): void
    {
        $winery = $this->userWithBeta(['role' => 'winery']);

        $tier = $winery->wineryTier();
        $this->assertSame('solo_vino', $tier['label']);
        $this->assertSame(19.00, $winery->wineryMonthlyPrice());
        $this->assertSame(180.00, $winery->wineryYearlyPrice());
    }

    public function test_winery_tier_bodega_con_5_viticultores_es_red_s(): void
    {
        $winery = $this->userWithBeta(['role' => 'winery']);

        for ($i = 0; $i < 5; $i++) {
            $vit = User::factory()->create(['role' => 'viticulturist']);
            $this->linkViticulturistToWinery($vit, $winery);
        }

        $this->assertSame('red_s', $winery->wineryTier()['label']);
        $this->assertSame(29.00, $winery->wineryMonthlyPrice());
    }

    public function test_winery_tier_enterprise_devuelve_null_en_precios(): void
    {
        $winery = $this->userWithBeta(['role' => 'winery']);

        for ($i = 0; $i < 101; $i++) {
            $vit = User::factory()->create(['role' => 'viticulturist']);
            DB::table('winery_viticulturist')->insert([
                'viticulturist_id' => $vit->id,
                'winery_id'        => $winery->id,
            ]);
        }

        $this->assertNull($winery->wineryMonthlyPrice());
        $this->assertNull($winery->wineryYearlyPrice());
    }

    // ── isCoveredByDo: bodega adscrita a una DO ───────────────────────────────

    public function test_bodega_sin_supervisor_no_esta_cubierta_por_do(): void
    {
        $winery = $this->userWithoutAccess(['role' => 'winery']);

        $this->assertFalse($winery->isCoveredByDo());
    }

    public function test_bodega_adscrita_a_do_esta_cubierta(): void
    {
        $winery     = $this->userWithoutAccess(['role' => 'winery']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->linkWineryToSupervisor($winery, $supervisor);

        $this->assertTrue($winery->isCoveredByDo());
    }

    public function test_bodega_adscrita_a_do_tiene_acceso_activo_sin_beta_ni_suscripcion(): void
    {
        $winery     = $this->userWithoutAccess(['role' => 'winery']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->linkWineryToSupervisor($winery, $supervisor);

        $this->assertTrue($winery->hasActiveAccess());
        $this->assertSame('winery', $winery->effectivePlan());
    }

    public function test_bodega_adscrita_a_do_con_beta_expirada_sigue_con_acceso(): void
    {
        $winery = User::factory()->create([
            'role'         => 'winery',
            'is_beta_user' => true,
            'beta_ends_at' => now()->subDay(),
        ]);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->linkWineryToSupervisor($winery, $supervisor);

        $this->assertTrue($winery->hasActiveAccess());
        $this->assertSame('winery', $winery->effectivePlan());
    }

    public function test_viticultor_y_productor_nunca_estan_cubiertos_por_do(): void
    {
        $vit      = $this->userWithBeta(['role' => 'viticulturist']);
        $producer = $this->userWithBeta(['role' => 'producer']);

        $this->assertFalse($vit->isCoveredByDo());
        $this->assertFalse($producer->isCoveredByDo());
    }

    // ── managedWineryCount / doTier ───────────────────────────────────────────

    public function test_no_supervisor_siempre_devuelve_count_0(): void
    {
        $user = $this->userWithBeta(['role' => 'viticulturist']);

        $this->assertSame(0, $user->managedWineryCount());
    }

    public function test_supervisor_sin_bodegas_tiene_count_0(): void
    {
        $supervisor = $this->userWithBeta(['role' => 'supervisor']);

        $this->assertSame(0, $supervisor->managedWineryCount());
    }

    public function test_supervisor_con_bodegas_devuelve_count_correcto(): void
    {
        $supervisor = $this->userWithBeta(['role' => 'supervisor']);
        $winery1    = User::factory()->create(['role' => 'winery']);
        $winery2    = User::factory()->create(['role' => 'winery']);

        $this->linkWineryToSupervisor($winery1, $supervisor);
        $this->linkWineryToSupervisor($winery2, $supervisor);

        $this->assertSame(2, $supervisor->managedWineryCount());
    }

    public function test_do_tier_supervisor_con_10_bodegas_es_do_s(): void
    {
        $supervisor = $this->userWithBeta(['role' => 'supervisor']);

        for ($i = 0; $i < 10; $i++) {
            $winery = User::factory()->create(['role' => 'winery']);
            $this->linkWineryToSupervisor($winery, $supervisor);
        }

        $tier = $supervisor->doTier();
        $this->assertSame('do_s', $tier['label']);
        $this->assertSame(149.00, $supervisor->doMonthlyPrice());
        $this->assertSame(1400.00, $supervisor->doYearlyPrice());
    }

    public function test_do_tier_supervisor_con_30_bodegas_es_do_m(): void
    {
        $supervisor = $this->userWithBeta(['role' => 'supervisor']);

        for ($i = 0; $i < 30; $i++) {
            $winery = User::factory()->create(['role' => 'winery']);
            $this->linkWineryToSupervisor($winery, $supervisor);
        }

        $this->assertSame('do_m', $supervisor->doTier()['label']);
        $this->assertSame(249.00, $supervisor->doMonthlyPrice());
    }

    public function test_do_tier_enterprise_devuelve_null_en_precios(): void
    {
        $supervisor = $this->userWithBeta(['role' => 'supervisor']);

        for ($i = 0; $i < 101; $i++) {
            $winery = User::factory()->create(['role' => 'winery']);
            $this->linkWineryToSupervisor($winery, $supervisor);
        }

        $this->assertNull($supervisor->doMonthlyPrice());
        $this->assertNull($supervisor->doYearlyPrice());
    }
}
