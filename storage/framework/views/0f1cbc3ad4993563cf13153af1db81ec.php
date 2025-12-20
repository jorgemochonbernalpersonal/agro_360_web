<?php
    use App\Models\Plot;
    use App\Models\AgriculturalActivity;
    use App\Models\SigpacCode;
    use App\Models\PlotPlanting;
    use App\Models\Invoice;
    use App\Models\Client;
    use App\Models\Harvest;
    use App\Models\HarvestContainer;
    use App\Models\Campaign;
    
    $user = auth()->user();
    
    // KPIs Básicos
    $totalPlots = Plot::forUser($user)->count();
    $totalArea = Plot::forUser($user)->sum('area') ?? 0;
    $activitiesThisYear = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->whereYear('created_at', date('Y'))
        ->count();
    $averageAreaPerPlot = $totalPlots > 0 ? $totalArea / $totalPlots : 0;
    
    // KPIs Financieros
    $totalInvoiced = Invoice::forUser($user->id)
        ->whereYear('invoice_date', date('Y'))
        ->where('status', '!=', 'cancelled')
        ->sum('total_amount') ?? 0;
    
    $pendingInvoices = Invoice::forUser($user->id)
        ->where('payment_status', 'unpaid')
        ->where('status', '!=', 'cancelled')
        ->count();
    
    $uninvoicedHarvests = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id);
    })
    ->whereDoesntHave('invoiceItems')
    ->count();
    
    $activeClients = Client::forUser($user->id)->where('active', true)->count();
    
    // KPIs de Cosecha
    $totalHarvested = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->whereYear('activity_date', date('Y'));
    })
    ->sum('total_weight') ?? 0;
    
    $harvestsThisMonth = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->whereMonth('activity_date', date('m'))
          ->whereYear('activity_date', date('Y'));
    })
    ->count();
    
    // Contenedores - disponibles (sin asignar a cosecha)
    $availableContainers = HarvestContainer::whereDoesntHave('harvests')->count();
    
    // Distribución por variedad
    $userPlotIds = Plot::forUser($user)->pluck('id');
    $plotsByVariety = PlotPlanting::selectRaw('grape_variety_id, COUNT(DISTINCT plot_id) as count, SUM(area_planted) as total_area')
        ->whereIn('plot_id', $userPlotIds)
        ->whereNotNull('grape_variety_id')
        ->where('status', 'active')
        ->groupBy('grape_variety_id')
        ->with('grapeVariety')
        ->get();
    
    // Actividades por tipo (últimos 30 días)
    $activitiesByType = AgriculturalActivity::selectRaw('activity_type, COUNT(*) as count')
        ->where('viticulturist_id', $user->id)
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('activity_type')
        ->get();
    
    // Últimas 5 cosechas
    $recentHarvests = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id);
    })
    ->with(['activity.plot', 'plotPlanting.grapeVariety'])
    ->orderBy('harvest_start_date', 'desc')
    ->take(5)
    ->get();
    
    // Facturas pendientes
    $pendingInvoicesList = Invoice::forUser($user->id)
        ->where('payment_status', 'unpaid')
        ->where('status', '!=', 'cancelled')
        ->with('client')
        ->orderBy('due_date', 'asc')
        ->take(5)
        ->get();
    
    // Top clientes - calcular total facturado manualmente
    $topClients = Client::forUser($user->id)
        ->with('invoices')
        ->get()
        ->map(function($client) {
            $client->total_invoiced = $client->invoices()
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;
            return $client;
        })
        ->sortByDesc('total_invoiced')
        ->take(5)
        ->values();
    
    // Ingresos mensuales (últimos 6 meses)
    $monthlyIncome = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $income = Invoice::forUser($user->id)
            ->whereYear('invoice_date', $month->year)
            ->whereMonth('invoice_date', $month->month)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount') ?? 0;
        $monthlyIncome[] = [
            'month' => $month->format('M Y'),
            'income' => $income
        ];
    }
    
    // Alertas
    $alerts = [];
    
    // Facturas próximas a vencer (7 días)
    $upcomingDueInvoices = Invoice::forUser($user->id)
        ->where('payment_status', 'unpaid')
        ->where('status', '!=', 'cancelled')
        ->whereNotNull('due_date')
        ->whereBetween('due_date', [now(), now()->addDays(7)])
        ->count();
    if ($upcomingDueInvoices > 0) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "{$upcomingDueInvoices} factura(s) próxima(s) a vencer en 7 días",
            'route' => route('viticulturist.invoices.index')
        ];
    }
    
    // Cosechas sin facturar (más de 30 días)
    $oldUninvoicedHarvests = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->where('activity_date', '<=', now()->subDays(30));
    })
    ->whereDoesntHave('invoiceItems')
    ->count();
    if ($oldUninvoicedHarvests > 0) {
        $alerts[] = [
            'type' => 'info',
            'message' => "{$oldUninvoicedHarvests} cosecha(s) sin facturar (más de 30 días)",
            'route' => route('viticulturist.invoices.harvest.index')
        ];
    }
    
    $dashboardIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
?>

<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['icon' => $dashboardIcon,'title' => 'Dashboard','description' => 'Resumen general de tu actividad agrícola y financiera','iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dashboardIcon),'title' => 'Dashboard','description' => 'Resumen general de tu actividad agrícola y financiera','icon-color' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

        <!-- Alertas -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($alerts) > 0): ?>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-<?php echo e($alert['type'] === 'warning' ? 'yellow' : 'blue'); ?>-50 border-l-4 border-<?php echo e($alert['type'] === 'warning' ? 'yellow' : 'blue'); ?>-400 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-<?php echo e($alert['type'] === 'warning' ? 'yellow' : 'blue'); ?>-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm font-medium text-<?php echo e($alert['type'] === 'warning' ? 'yellow' : 'blue'); ?>-800"><?php echo e($alert['message']); ?></p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($alert['route'])): ?>
                                <a href="<?php echo e($alert['route']); ?>" wire:navigate class="text-sm font-semibold text-<?php echo e($alert['type'] === 'warning' ? 'yellow' : 'blue'); ?>-600 hover:text-<?php echo e($alert['type'] === 'warning' ? 'yellow' : 'blue'); ?>-800">
                                    Ver →
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- KPI Cards - Primera Fila (Básicos) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Parcelas -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Parcelas</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]"><?php echo e($totalPlots); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)]/20 to-[var(--color-agro-green)]/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Área Total -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Área Total</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]"><?php echo e(number_format($totalArea, 1)); ?></p>
                        <p class="text-xs text-gray-400 mt-1">hectáreas</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Actividades Año -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Actividades <?php echo e(date('Y')); ?></p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]"><?php echo e($activitiesThisYear); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Promedio Área -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Promedio/Parcela</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]"><?php echo e(number_format($averageAreaPerPlot, 1)); ?></p>
                        <p class="text-xs text-gray-400 mt-1">ha/parcela</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards - Segunda Fila (Financieros y Cosecha) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
            <!-- Total Facturado -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Facturado <?php echo e(date('Y')); ?></p>
                        <p class="text-2xl font-bold text-green-600"><?php echo e(number_format($totalInvoiced, 2)); ?> €</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Facturas Pendientes -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pendientes</p>
                        <p class="text-2xl font-bold text-orange-600"><?php echo e($pendingInvoices); ?></p>
                        <p class="text-xs text-gray-400 mt-1">facturas</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Cosechas Sin Facturar -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Sin Facturar</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo e($uninvoicedHarvests); ?></p>
                        <p class="text-xs text-gray-400 mt-1">cosechas</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Cosechado -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Cosechado <?php echo e(date('Y')); ?></p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo e(number_format($totalHarvested, 0)); ?></p>
                        <p class="text-xs text-gray-400 mt-1">kg</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Clientes Activos -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Clientes</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo e($activeClients); ?></p>
                        <p class="text-xs text-gray-400 mt-1">activos</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Contenedores Disponibles -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Contenedores</p>
                        <p class="text-2xl font-bold text-teal-600"><?php echo e($availableContainers); ?></p>
                        <p class="text-xs text-gray-400 mt-1">disponibles</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-teal-100 to-teal-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Ingresos Mensuales -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Ingresos Mensuales</h3>
                    <a href="<?php echo e(route('viticulturist.invoices.index')); ?>" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors">
                        Ver todas →
                    </a>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($monthlyIncome) > 0): ?>
                    <div class="space-y-3">
                        <?php
                            $maxIncome = max(array_column($monthlyIncome, 'income'));
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $monthlyIncome; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $percentage = $maxIncome > 0 ? ($month['income'] / $maxIncome) * 100 : 0;
                            ?>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700"><?php echo e($month['month']); ?></span>
                                    <span class="text-sm font-bold text-gray-900"><?php echo e(number_format($month['income'], 2)); ?> €</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500" style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No hay ingresos registrados</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Distribución por Variedad -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Distribución por Variedad</h3>
                    <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plotsByVariety->count() > 0): ?>
                    <div class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plotsByVariety; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variety): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $percentage = ($variety->count / $totalPlots) * 100;
                                $colors = ['bg-[var(--color-agro-green-dark)]', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500'];
                                $color = $colors[$index % count($colors)];
                            ?>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700"><?php echo e($variety->grapeVariety->name ?? 'Sin variedad'); ?></span>
                                    <span class="text-sm font-bold text-gray-900"><?php echo e($variety->count); ?> (<?php echo e(number_format($percentage, 1)); ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="<?php echo e($color); ?> h-2.5 rounded-full transition-all duration-500" style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No hay datos de variedades disponibles</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cosechas Recientes -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Cosechas Recientes</h3>
                    <a href="<?php echo e(route('viticulturist.invoices.harvest.index')); ?>" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors">
                        Facturar →
                    </a>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentHarvests->count() > 0): ?>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentHarvests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $harvest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isInvoiced = $harvest->invoiceItems()->exists();
                            ?>
                            <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        <?php echo e($harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad'); ?> - <?php echo e($harvest->activity->plot->name ?? 'Sin parcela'); ?>

                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e(number_format($harvest->total_weight, 2)); ?> kg - <?php echo e($harvest->harvest_start_date->format('d/m/Y')); ?>

                                    </p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isInvoiced): ?>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Facturada</span>
                                    <?php else: ?>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sin facturar</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isInvoiced): ?>
                                    <a href="<?php echo e(route('viticulturist.invoices.create', ['harvest_id' => $harvest->id])); ?>" wire:navigate class="text-green-600 hover:text-green-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No hay cosechas registradas</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Facturas Pendientes -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Facturas Pendientes</h3>
                    <a href="<?php echo e(route('viticulturist.invoices.index')); ?>" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors">
                        Ver todas →
                    </a>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingInvoicesList->count() > 0): ?>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pendingInvoicesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isOverdue = $invoice->due_date && $invoice->due_date < now();
                            ?>
                            <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors <?php echo e($isOverdue ? 'bg-red-50 border border-red-200' : ''); ?>">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($invoice->invoice_number); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($invoice->client->full_name ?? 'Sin cliente'); ?></p>
                                    <p class="text-xs font-semibold text-gray-900 mt-1"><?php echo e(number_format($invoice->total_amount, 2)); ?> €</p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOverdue): ?>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Vencida</span>
                                    <?php elseif($invoice->due_date): ?>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Vence: <?php echo e($invoice->due_date->format('d/m/Y')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <a href="<?php echo e(route('viticulturist.invoices.show', $invoice->id)); ?>" wire:navigate class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No hay facturas pendientes</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Top Clientes -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Top Clientes</h3>
                    <a href="<?php echo e(route('viticulturist.clients.index')); ?>" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors">
                        Ver todos →
                    </a>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topClients->count() > 0): ?>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $topClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg font-bold text-indigo-600"><?php echo e($index + 1); ?></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($client->full_name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e(number_format($client->total_invoiced ?? 0, 2)); ?> € facturado</p>
                                </div>
                                <a href="<?php echo e(route('viticulturist.clients.show', $client->id)); ?>" wire:navigate class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No hay clientes registrados</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/viticulturist/dashboard.blade.php ENDPATH**/ ?>