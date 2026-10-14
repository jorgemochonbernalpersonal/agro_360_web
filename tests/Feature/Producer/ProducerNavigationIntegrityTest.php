<?php

namespace Tests\Feature\Producer;

use Illuminate\Support\Facades\Route;
use Tests\Feature\ProducerTestCase;

/**
 * Barrido de integridad de la navegación del producer.
 *
 * El producer reutiliza casi todas las vistas de winery y viticulturist bajo el
 * prefijo producer.*, así que cualquier route('winery.X') o route('viticulturist.X')
 * cableado en una vista compartida lo saca de su área: cambia la URL, el sidebar
 * se desincroniza y, si el módulo es de plan superior, se lleva un 403.
 *
 * Este test recorre todas las páginas del producer sin parámetros y verifica:
 *   1. que ninguna responde 4xx/5xx,
 *   2. que ninguna enlaza a /winery/... ni /viticulturist/...
 *
 * Usa roleRoute()/roleRouteIs() como solución en las vistas compartidas.
 */
class ProducerNavigationIntegrityTest extends ProducerTestCase
{
    public function test_every_producer_page_responds_and_stays_inside_the_producer_area(): void
    {
        $producer = $this->makeProducer();
        $producer->update(['compra_uva_externa' => true]);
        $this->actingAs($producer);

        $routes = $this->parameterlessProducerRoutes();
        $this->assertGreaterThan(150, count($routes), 'El barrido no encontró las rutas del producer.');

        $failures = [];
        $leaks = [];

        foreach ($routes as $name) {
            $response = $this->get(route($name));
            $status = $response->getStatusCode();

            if ($status >= 400) {
                $failures[] = "{$status} {$name}";

                continue;
            }

            if ($status !== 200) {
                continue; // redirecciones intencionadas a rutas compartidas
            }

            preg_match_all(
                '~(?:href|action)="https?://[^/]+/(winery|viticulturist)/([^"?#]*)~i',
                $response->getContent(),
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $leaks[] = "{$name} -> /{$match[1]}/{$match[2]}";
            }
        }

        $this->assertSame([], $failures, "Páginas del producer que no responden:\n".implode("\n", $failures));
        $this->assertSame(
            [],
            array_values(array_unique($leaks)),
            "Enlaces que sacan al producer de su área (usa roleRoute()):\n".implode("\n", array_unique($leaks))
        );
    }

    /**
     * @return list<string> Nombres de las rutas GET del producer sin parámetros
     */
    private function parameterlessProducerRoutes(): array
    {
        $names = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! str_starts_with($name, 'producer.')) {
                continue;
            }

            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            if (str_contains($route->uri(), '{')) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }
}
