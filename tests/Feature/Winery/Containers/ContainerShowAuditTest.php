<?php

namespace Tests\Feature\Winery\Containers;

use App\Livewire\Winery\Cellar\Containers\Show;
use App\Models\Container;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verifica que la pestaña "Historial de cambios" del detalle de contenedor
 * renderiza y refleja la auditoría de metadatos.
 */
class ContainerShowAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_renders_change_history_section(): void
    {
        $user = User::factory()->create(['role' => 'winery']);
        $container = Container::factory()->create([
            'user_id' => $user->id,
            'name' => 'Tanque A',
            'capacity' => 1000.0,
        ]);

        $this->actingAs($user);
        // Locale fijo: los textos de la vista pasan por __() y el locale por
        // defecto del entorno de test (en) traduciría las etiquetas.
        $this->app->setLocale('es');

        // Genera un cambio de metadatos → debe quedar auditado y mostrarse.
        $container->update(['name' => 'Tanque B', 'capacity' => 1200.0]);

        Livewire::test(Show::class, ['container' => $container])
            ->assertOk()
            ->assertSee('Historial de cambios')
            ->assertSee('Modificado')
            ->assertSee('Nombre');
    }
}
