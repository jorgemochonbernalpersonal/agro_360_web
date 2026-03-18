<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Resumen Económico"
    description="Vista consolidada de ingresos, gastos y márgenes de tu bodega."
    icon="chart-bar-square"
>
</x-agro.page-header>

<x-agro.card>
    <div class="flex flex-col items-center text-center py-8 gap-4">
        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30">
            <flux:icon icon="chart-bar-square" class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
        </div>
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-1">
                Informe financiero en preparación
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-lg mx-auto">
                Este módulo consolidará datos de liquidaciones de vendimia, facturas de venta,
                costes de operación y márgenes por producto. Estará disponible próximamente.
            </p>
        </div>

        {{-- Metric placeholders --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full mt-4 opacity-40 pointer-events-none select-none">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Ingresos totales</p>
                <p class="text-2xl font-bold text-zinc-300 dark:text-zinc-600">—</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Gastos de uva</p>
                <p class="text-2xl font-bold text-zinc-300 dark:text-zinc-600">—</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Margen bruto</p>
                <p class="text-2xl font-bold text-zinc-300 dark:text-zinc-600">—</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Facturación pendiente</p>
                <p class="text-2xl font-bold text-zinc-300 dark:text-zinc-600">—</p>
            </div>
        </div>

        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">
            Próximamente disponible — estamos preparando los informes financieros
        </p>
    </div>
</x-agro.card>
</div>
