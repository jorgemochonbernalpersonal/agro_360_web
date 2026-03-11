<div class="min-h-[calc(100vh-8rem)] flex items-center justify-center p-8">
    <div class="max-w-lg w-full text-center space-y-8 animate-fade-in">

        {{-- Icon --}}
        <div class="flex justify-center">
            <div class="relative">
                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center shadow-lg">
                    <flux:icon icon="{{ $icon }}" class="size-12 text-amber-500" />
                </div>
                <span class="absolute -top-1 -right-1 flex size-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full size-4 bg-amber-500"></span>
                </span>
            </div>
        </div>

        {{-- Text --}}
        <div class="space-y-3">
            <h1 class="text-2xl font-bold text-zinc-900">
                @if ($module) {{ $module }} @else Módulo en construcción @endif
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
                    'Comunicación con Bodega' => [
                        ['icon' => 'chat-bubble-left-right', 'label' => 'Mensajería',        'desc' => 'Canal directo con tu bodega cooperativa o privada'],
                        ['icon' => 'bell',                   'label' => 'Alertas',            'desc' => 'Avisos de recepciones, calidades y liquidaciones'],
                        ['icon' => 'document-text',          'label' => 'Documentos',         'desc' => 'Albaranes y contratos compartidos con la bodega'],
                    ],
                    'Notificaciones'          => [
                        ['icon' => 'bell',                   'label' => 'Centro de avisos',   'desc' => 'Vencimientos, alertas de plagas y recordatorios'],
                        ['icon' => 'clock',                  'label' => 'Plazos legales',     'desc' => 'ROPO, ITB, autorizaciones y plazos PAC'],
                        ['icon' => 'cog-6-tooth',            'label' => 'Personalizable',     'desc' => 'Elige qué alertas quieres recibir y cuándo'],
                    ],
                    'Subcontratación'         => [
                        ['icon' => 'user-plus',              'label' => 'Empresas de servicio','desc' => 'Tratamientos, vendimia y labores subcontratadas'],
                        ['icon' => 'document-text',          'label' => 'Partes de trabajo',  'desc' => 'Registro con fecha, parcela y servicio realizado'],
                        ['icon' => 'calculator',             'label' => 'Costes',             'desc' => 'Integración con costes por parcela y campaña'],
                    ],
                    'Seguros Agrarios'        => [
                        ['icon' => 'shield-exclamation',     'label' => 'Pólizas',            'desc' => 'Registro de seguros de helada, granizo y sequía'],
                        ['icon' => 'calendar',               'label' => 'Vencimientos',       'desc' => 'Alertas de renovación antes de la campaña'],
                        ['icon' => 'document-text',          'label' => 'Siniestros',         'desc' => 'Historial de partes y resoluciones por parcela'],
                    ],
                    'Costes por Parcela'      => [
                        ['icon' => 'table-cells',            'label' => 'Coste por ha',       'desc' => 'Desglose de gastos directos e indirectos por parcela'],
                        ['icon' => 'chart-bar',              'label' => 'Rentabilidad',       'desc' => 'Margen por parcela comparado con ingresos de entrega'],
                        ['icon' => 'arrow-trending-down',    'label' => 'Optimización',       'desc' => 'Identifica qué parcelas son menos rentables'],
                    ],
                    'VeriFactu'               => [
                        ['icon' => 'document-check',         'label' => 'Facturas verificadas', 'desc' => 'Emisión de facturas con sello VeriFactu homologado por la AEAT'],
                        ['icon' => 'arrow-up-tray',          'label' => 'Envío automático',     'desc' => 'Remisión automática al sistema de la Agencia Tributaria'],
                        ['icon' => 'shield-check',           'label' => 'Trazabilidad fiscal',  'desc' => 'Registro inmutable de cada factura emitida o anulada'],
                    ],
                    'Exportaciones CUE'       => [
                        ['icon' => 'arrow-up-tray',          'label' => 'CUE oficial',        'desc' => 'Exportación al formato del Cuaderno Único de Explotación'],
                        ['icon' => 'document-check',         'label' => 'Validación',         'desc' => 'Verificación previa de datos antes de envío'],
                        ['icon' => 'clock',                  'label' => 'Historial',          'desc' => 'Registro de exportaciones anteriores por campaña'],
                    ],
                    default                   => [
                        ['icon' => 'cog-6-tooth',            'label' => 'En desarrollo',      'desc' => 'Módulo en proceso de construcción'],
                        ['icon' => 'rocket-launch',          'label' => 'Próximamente',       'desc' => 'Estará disponible muy pronto'],
                        ['icon' => 'star',                   'label' => 'Prioritario',        'desc' => 'Forma parte del roadmap activo'],
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
