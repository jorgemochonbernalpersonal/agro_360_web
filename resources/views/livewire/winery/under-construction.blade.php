<div class="min-h-[calc(100vh-8rem)] flex items-center justify-center p-8">
    <div class="max-w-lg w-full text-center space-y-8 animate-fade-in">

        {{-- Icon --}}
        <div class="flex justify-center">
            <div class="relative">
                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center shadow-lg">
                    <flux:icon icon="{{ $icon }}" class="size-12 text-amber-500" />
                </div>
                {{-- Animated dots --}}
                <span class="absolute -top-1 -right-1 flex size-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full size-4 bg-amber-500"></span>
                </span>
            </div>
        </div>

        {{-- Text --}}
        <div class="space-y-3">
            <h1 class="text-2xl font-bold text-zinc-900">
                @if ($module)
                    {{ $module }}
                @else
                    Módulo en construcción
                @endif
            </h1>
            <p class="text-zinc-500 text-base leading-relaxed">
                Estamos trabajando en este módulo para ofrecerte la mejor experiencia.<br>
                Pronto estará disponible.
            </p>
            @if ($eta)
                <p class="text-sm text-amber-600 font-medium">
                    <flux:icon icon="clock" class="size-4 inline-block mr-1" />
                    Estimación: {{ $eta }}
                </p>
            @endif
        </div>

        {{-- Progress bar decorative --}}
        <div class="w-full bg-zinc-100 rounded-full h-2 overflow-hidden">
            <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-orange-400 animate-pulse" style="width: 35%"></div>
        </div>

        {{-- Feature hints --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
            @php
                $hints = match ($module) {
                    'SILICIE'           => [
                        ['icon' => 'document-chart-bar', 'label' => 'Registro SILICIE', 'desc' => 'Integración con el sistema oficial'],
                        ['icon' => 'arrows-right-left',  'label' => 'Movimientos',       'desc' => 'Entradas y salidas de vino'],
                        ['icon' => 'arrow-up-tray',      'label' => 'Exportación',        'desc' => 'Envío automático de datos'],
                    ],
                    'Documentos Bodega' => [
                        ['icon' => 'folder-open',   'label' => 'Documentos',   'desc' => 'Certificados y licencias'],
                        ['icon' => 'cloud-arrow-up','label' => 'Digitalización','desc' => 'Sube y organiza ficheros'],
                        ['icon' => 'bell',          'label' => 'Alertas',       'desc' => 'Vencimientos y renovaciones'],
                    ],
                    'Insumos de Bodega' => [
                        ['icon' => 'building-storefront', 'label' => 'Inventario',  'desc' => 'Control de stock de insumos'],
                        ['icon' => 'arrow-trending-down', 'label' => 'Consumos',    'desc' => 'Seguimiento de uso por lote'],
                        ['icon' => 'bell',                'label' => 'Alertas',     'desc' => 'Mínimo de stock configurable'],
                    ],
                    'Proveedores'       => [
                        ['icon' => 'truck',          'label' => 'Proveedores',  'desc' => 'Directorio y condiciones'],
                        ['icon' => 'shopping-cart',  'label' => 'Pedidos',      'desc' => 'Historial de compras'],
                        ['icon' => 'chart-bar',      'label' => 'Análisis',     'desc' => 'Costes y comparativas'],
                    ],
                    'Análisis de Lab.'  => [
                        ['icon' => 'beaker',              'label' => 'Análisis',         'desc' => 'Parámetros físico-químicos'],
                        ['icon' => 'chart-bar',           'label' => 'Evolución',        'desc' => 'Histórico por contenedor'],
                        ['icon' => 'clipboard-document',  'label' => 'Informes',         'desc' => 'Exportación a PDF'],
                    ],
                    'Compra de Uva'       => [
                        ['icon' => 'arrow-down-tray',      'label' => 'Liquidaciones',    'desc' => 'Liquidaciones de uva por viticultor'],
                        ['icon' => 'document-text',        'label' => 'Albaranes',         'desc' => 'Albaranes de entrega y valorados'],
                        ['icon' => 'calculator',           'label' => 'Precios',           'desc' => 'Tarifas por variedad, calidad y añada'],
                    ],
                    'Vinos'               => [
                        ['icon' => 'arrows-right-left',   'label' => 'Pipeline',         'desc' => 'Seguimiento del proceso de vinificación'],
                        ['icon' => 'beaker',              'label' => 'Elaboración',       'desc' => 'Trasiegos, clarificaciones y filtraciones'],
                        ['icon' => 'archive-box',         'label' => 'Lotes',             'desc' => 'Trazabilidad completa por lote de vino'],
                    ],
                    'Uva / Mosto externo' => [
                        ['icon' => 'archive-box',         'label' => 'Partidas externas','desc' => 'Uva, mosto y vino a granel de proveedores'],
                        ['icon' => 'scale',               'label' => 'Control de stock', 'desc' => 'Seguimiento de kg disponibles por partida'],
                        ['icon' => 'arrows-right-left',   'label' => 'Vinculación',      'desc' => 'Asociación directa con contenedores y vinos'],
                    ],
                    default             => [
                        ['icon' => 'cog-6-tooth',   'label' => 'En desarrollo', 'desc' => 'Módulo en proceso de construcción'],
                        ['icon' => 'rocket-launch', 'label' => 'Próximamente',  'desc' => 'Estará disponible muy pronto'],
                        ['icon' => 'star',          'label' => 'Prioritario',   'desc' => 'Forma parte del roadmap activo'],
                    ],
                };
            @endphp

            @foreach ($hints as $hint)
                <div class="bg-zinc-50 rounded-xl p-4 space-y-1.5">
                    <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center">
                        <flux:icon icon="{{ $hint['icon'] }}" class="size-4 text-amber-500" />
                    </div>
                    <p class="text-xs font-semibold text-zinc-700">{{ $hint['label'] }}</p>
                    <p class="text-[11px] text-zinc-400">{{ $hint['desc'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Back button --}}
        <div class="pt-2">
            <flux:button href="{{ route($backRoute) }}" wire:navigate variant="ghost" icon="arrow-left" size="sm">
                Volver al panel
            </flux:button>
        </div>
    </div>
</div>
