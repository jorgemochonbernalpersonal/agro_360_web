<?php

namespace App\Http\Controllers;

use App\Models\DiseaseAlertSubscription;

/**
 * Herramientas públicas gratuitas (/herramientas): calculadoras y tests sin registro
 * que sirven de puerta de entrada SEO y de captación de contactos.
 */
class ToolsController extends Controller
{
    /**
     * Registro de herramientas publicadas. Única fuente de verdad para rutas,
     * índice /herramientas y sitemap. slug => metadatos.
     */
    public const TOOLS = [
        'calculadora-caldo-fitosanitario' => [
            'view' => 'tools.spray-calculator',
            'title' => 'Calculadora de caldo fitosanitario',
            'summary' => 'Litros de caldo, número de cubas y cantidad de cada producto por cuba a partir de la dosis de la etiqueta.',
            'icon' => '🧪',
        ],
        'calculadora-rendimiento-vendimia' => [
            'view' => 'tools.harvest-yield',
            'title' => 'Estimador de vendimia',
            'summary' => 'Kilos por hectárea, kilos totales y litros de vino estimados por conteo de racimos. Compara con el límite de tu D.O.',
            'icon' => '🍇',
        ],
        'visor-sigpac' => [
            'view' => 'tools.sigpac-viewer',
            'title' => 'Visor de recinto SIGPAC',
            'summary' => 'Superficie, uso, pendiente, regadío e incidencias de tu recinto a partir de su referencia, sobre la ortofoto.',
            'icon' => '🗺️',
        ],
        'alertas-mildiu-oidio' => [
            'view' => 'tools.disease-alerts',
            'title' => 'Alertas de mildiu y oídio',
            'summary' => 'Riesgo de los próximos días en tu municipio y aviso gratis por email cuando sea alto.',
            'icon' => '🌦️',
        ],
        'test-cuaderno-digital-2027' => [
            'view' => 'tools.notebook-readiness',
            'title' => '¿Estás listo para el cuaderno digital 2027?',
            'summary' => 'Test de 8 preguntas: descubre qué te falta para cumplir con el cuaderno de explotación digital obligatorio.',
            'icon' => '✅',
        ],
    ];

    /** Herramientas anunciadas que aún no están construidas (se muestran como "Próximamente"). */
    public const UPCOMING = [
        ['title' => 'Buscador de fitosanitarios autorizados', 'summary' => 'Por cultivo y plaga, con plazo de seguridad y dosis máxima.', 'icon' => '🔎'],
        ['title' => 'Factura Verifactu gratis', 'summary' => 'Factura homologada para autónomos agrarios, con IRPF agrario.', 'icon' => '🧾'],
    ];

    public function index()
    {
        return view('tools.index', [
            'tools' => self::TOOLS,
            'upcoming' => self::UPCOMING,
        ]);
    }

    public function show(string $slug)
    {
        $tool = self::TOOLS[$slug] ?? abort(404);

        return view($tool['view'], [
            'slug' => $slug,
            'tool' => $tool,
            'related' => array_diff_key(self::TOOLS, [$slug => true]),
        ]);
    }

    public function showUnsubscribe(string $token)
    {
        return view('tools.disease-alerts-unsubscribed', [
            'subscription' => DiseaseAlertSubscription::with('municipality')->where('token', $token)->first(),
        ]);
    }

    public function unsubscribe(string $token)
    {
        $subscription = DiseaseAlertSubscription::where('token', $token)->first();

        if ($subscription && ! $subscription->unsubscribed_at) {
            $subscription->update(['unsubscribed_at' => now()]);
        }

        return redirect()->route('tools.disease-alerts.unsubscribe', $token);
    }

    /** @return array<int, string> */
    public static function slugs(): array
    {
        return array_keys(self::TOOLS);
    }
}
