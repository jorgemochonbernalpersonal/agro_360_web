<?php

namespace Tests\Feature\Winery\Containers;

use App\Livewire\Winery\Cellar\Containers\Analytics;
use App\Models\Container;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Humo: tras dar soporte real al prop `title` en x-agro.card, las cards de
 * analytics (que usaban title= invisible) deben mostrar su encabezado.
 */
class ContainerAnalyticsRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_renders_card_titles(): void
    {
        $user = User::factory()->create(['role' => 'winery']);
        Container::factory()->create(['user_id' => $user->id, 'capacity' => 1000.0]);

        $this->actingAs($user);
        $this->app->setLocale('es');

        Livewire::test(Analytics::class)
            ->assertOk()
            ->assertSee('Distribución por ocupación')
            ->assertSee('Por tipo de contenedor');
    }
}
