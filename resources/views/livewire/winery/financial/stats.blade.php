<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Estadísticas Financieras"
    description="Análisis de KPIs, tendencias y comparativas económicas de tu bodega."
    icon="presentation-chart-bar"
>
</x-agro.page-header>

<x-agro.card>
    <div class="flex flex-col items-center text-center py-8 gap-4">
        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-purple-100 dark:bg-purple-900/30">
            <flux:icon icon="presentation-chart-bar" class="w-8 h-8 text-purple-600 dark:text-purple-400" />
        </div>
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-1">
                Estadísticas financieras en preparación
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-lg mx-auto">
                Este módulo mostrará gráficas de evolución de ingresos, análisis de rentabilidad
                por variedad, comparativas anuales y KPIs financieros clave de tu bodega.
            </p>
        </div>

        {{-- Chart placeholder --}}
        <div class="w-full mt-4 opacity-40 pointer-events-none select-none">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-4">Evolución de ingresos</p>
                <div class="flex items-end gap-2 h-24 justify-center">
                    @foreach([40, 60, 45, 75, 55, 80, 65, 90, 70, 85, 60, 95] as $height)
                        <div class="flex-1 bg-zinc-200 dark:bg-zinc-700 rounded-t"
                            style="height: {{ $height }}%"></div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-2">
                    @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $month)
                        <span class="text-xs text-zinc-400">{{ $month }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full opacity-40 pointer-events-none select-none">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Rentabilidad por variedad</p>
                <p class="text-sm text-zinc-300 dark:text-zinc-600">Próximamente</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Comparativa anual</p>
                <p class="text-sm text-zinc-300 dark:text-zinc-600">Próximamente</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Top clientes</p>
                <p class="text-sm text-zinc-300 dark:text-zinc-600">Próximamente</p>
            </div>
        </div>

        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">
            Próximamente disponible — estamos preparando los informes financieros
        </p>
    </div>
</x-agro.card>
</div>
