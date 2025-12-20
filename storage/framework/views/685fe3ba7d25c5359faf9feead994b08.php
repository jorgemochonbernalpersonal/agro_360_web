<div class="space-y-6 animate-fade-in">
    <?php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
    ?>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['icon' => $icon,'title' => 'Contenedores','description' => 'Gestiona tus contenedores. Puedes crearlos independientemente y asignarlos a cosechas.','iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'title' => 'Contenedores','description' => 'Gestiona tus contenedores. Puedes crearlos independientemente y asignarlos a cosechas.','icon-color' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]']); ?>
         <?php $__env->slot('actionButton', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('viticulturist.digital-notebook.containers.create')).'','variant' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('viticulturist.digital-notebook.containers.create')).'','variant' => 'primary']); ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Contenedor
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
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

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Contenedores</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['total'])); ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Peso Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['total_weight'], 2)); ?> kg</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Entregados</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['delivered'])); ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <?php if (isset($component)) { $__componentOriginalb9f1d4c7e4c2b3dfba76201bf93babfd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9f1d4c7e4c2b3dfba76201bf93babfd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-section','data' => ['title' => 'Filtros de Búsqueda','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Filtros de Búsqueda','color' => 'green']); ?>
        <?php if (isset($component)) { $__componentOriginal5a8c2f7be39be27ec791b2034cff2725 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a8c2f7be39be27ec791b2034cff2725 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-input','data' => ['wire:model.live.debounce.300ms' => 'search','placeholder' => 'Buscar por número, ubicación, parcela...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live.debounce.300ms' => 'search','placeholder' => 'Buscar por número, ubicación, parcela...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5a8c2f7be39be27ec791b2034cff2725)): ?>
<?php $attributes = $__attributesOriginal5a8c2f7be39be27ec791b2034cff2725; ?>
<?php unset($__attributesOriginal5a8c2f7be39be27ec791b2034cff2725); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5a8c2f7be39be27ec791b2034cff2725)): ?>
<?php $component = $__componentOriginal5a8c2f7be39be27ec791b2034cff2725; ?>
<?php unset($__componentOriginal5a8c2f7be39be27ec791b2034cff2725); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'selectedCampaign']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'selectedCampaign']); ?>
            <option value="">Todas las campañas</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($campaign->id); ?>">Campaña <?php echo e($campaign->year); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedCampaign): ?>
            <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'selectedHarvest']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'selectedHarvest']); ?>
                <option value="">Todas las cosechas</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $harvests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $harvest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($harvest->id); ?>">
                        <?php echo e($harvest->activity->plot->name ?? 'Sin parcela'); ?> - 
                        <?php echo e($harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad'); ?> 
                        (<?php echo e($harvest->harvest_start_date->format('d/m/Y')); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'filterStatus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filterStatus']); ?>
            <option value="">Todos los estados</option>
            <option value="filled">Llenado</option>
            <option value="in_transit">En tránsito</option>
            <option value="delivered">Entregado</option>
            <option value="stored">Almacenado</option>
            <option value="empty">Vacío</option>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'filterAvailability']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filterAvailability']); ?>
            <option value="">Todos</option>
            <option value="available">Disponibles</option>
            <option value="assigned">Asignados</option>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'filterType']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filterType']); ?>
            <option value="">Todos los tipos</option>
            <option value="caja">Caja</option>
            <option value="pallet">Pallet</option>
            <option value="contenedor">Contenedor</option>
            <option value="saco">Saco</option>
            <option value="cuba">Cuba</option>
            <option value="other">Otro</option>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $attributes = $__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__attributesOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0)): ?>
<?php $component = $__componentOriginal4e9104b073735a9cf7ecaeefab5771b0; ?>
<?php unset($__componentOriginal4e9104b073735a9cf7ecaeefab5771b0); ?>
<?php endif; ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $selectedCampaign || $selectedHarvest || $filterStatus || $filterType): ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['wire:click' => '$set(\'search\', \'\'); $set(\'selectedCampaign\', \'\'); $set(\'selectedHarvest\', \'\'); $set(\'filterStatus\', \'\'); $set(\'filterType\', \'\')','variant' => 'ghost','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'search\', \'\'); $set(\'selectedCampaign\', \'\'); $set(\'selectedHarvest\', \'\'); $set(\'filterStatus\', \'\'); $set(\'filterType\', \'\')','variant' => 'ghost','size' => 'sm']); ?>
                    Limpiar Filtros
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9f1d4c7e4c2b3dfba76201bf93babfd)): ?>
<?php $attributes = $__attributesOriginalb9f1d4c7e4c2b3dfba76201bf93babfd; ?>
<?php unset($__attributesOriginalb9f1d4c7e4c2b3dfba76201bf93babfd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9f1d4c7e4c2b3dfba76201bf93babfd)): ?>
<?php $component = $__componentOriginalb9f1d4c7e4c2b3dfba76201bf93babfd; ?>
<?php unset($__componentOriginalb9f1d4c7e4c2b3dfba76201bf93babfd); ?>
<?php endif; ?>

    <!-- Tabla de Contenedores -->
    <?php
        $headers = [
            ['label' => 'Contenedor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'],
            ['label' => 'Cosecha', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            ['label' => 'Peso', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>'],
            ['label' => 'Ubicación', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Fechas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            'Acciones',
        ];
    ?>

    <?php if (isset($component)) { $__componentOriginalc8463834ba515134d5c98b88e1a9dc03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-table','data' => ['headers' => $headers,'emptyMessage' => 'No hay contenedores registrados','emptyDescription' => ''.e(($search || $selectedCampaign || $filterStatus || $filterType) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores aparecerán aquí cuando se registren en las cosechas').'','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($headers),'empty-message' => 'No hay contenedores registrados','empty-description' => ''.e(($search || $selectedCampaign || $filterStatus || $filterType) ? 'No se encontraron contenedores con los filtros seleccionados' : 'Los contenedores aparecerán aquí cuando se registren en las cosechas').'','color' => 'green']); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($containers->count() > 0): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $containers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $container): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal5624e7818f90ab26cc102f1b791c1b71 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5624e7818f90ab26cc102f1b791c1b71 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-row','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">
                                    <?php echo e(ucfirst($container->container_type)); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container->container_number): ?>
                                        #<?php echo e($container->container_number); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Cantidad: <?php echo e($container->quantity); ?>

                                </div>
                            </div>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container->harvest): ?>
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo e($container->harvest->activity->plot->name ?? 'Sin parcela'); ?>

                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?php echo e($container->harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad'); ?>

                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <?php echo e($container->harvest->harvest_start_date->format('d/m/Y')); ?>

                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    Disponible
                                </span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div>
                            <div class="text-sm font-bold text-gray-900">
                                <?php echo e(number_format($container->weight, 2)); ?> kg
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container->weight_per_unit): ?>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?php echo e(number_format($container->weight_per_unit, 2)); ?> kg/unidad
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container->location): ?>
                            <span class="text-sm text-gray-700"><?php echo e($container->location); ?></span>
                        <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php
                            $statusColors = [
                                'filled' => 'bg-blue-100 text-blue-800',
                                'in_transit' => 'bg-yellow-100 text-yellow-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'stored' => 'bg-purple-100 text-purple-800',
                                'empty' => 'bg-gray-100 text-gray-800',
                            ];
                            $statusLabels = [
                                'filled' => 'Llenado',
                                'in_transit' => 'En tránsito',
                                'delivered' => 'Entregado',
                                'stored' => 'Almacenado',
                                'empty' => 'Vacío',
                            ];
                            $color = $statusColors[$container->status] ?? 'bg-gray-100 text-gray-800';
                            $label = $statusLabels[$container->status] ?? ucfirst($container->status);
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($color); ?>">
                            <?php echo e($label); ?>

                        </span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="text-xs text-gray-600">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container->filled_date): ?>
                                <div>Llenado: <?php echo e($container->filled_date->format('d/m/Y')); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($container->delivery_date): ?>
                                <div class="mt-1">Entrega: <?php echo e($container->delivery_date->format('d/m/Y')); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginale879916077dd6f89968249d7765eac40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale879916077dd6f89968249d7765eac40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-cell','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-cell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="flex items-center gap-2">
                            <a 
                                href="<?php echo e(route('viticulturist.digital-notebook.containers.edit', $container->id)); ?>"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition"
                            >
                                Editar
                            </a>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $attributes = $__attributesOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__attributesOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale879916077dd6f89968249d7765eac40)): ?>
<?php $component = $__componentOriginale879916077dd6f89968249d7765eac40; ?>
<?php unset($__componentOriginale879916077dd6f89968249d7765eac40); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5624e7818f90ab26cc102f1b791c1b71)): ?>
<?php $attributes = $__attributesOriginal5624e7818f90ab26cc102f1b791c1b71; ?>
<?php unset($__attributesOriginal5624e7818f90ab26cc102f1b791c1b71); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5624e7818f90ab26cc102f1b791c1b71)): ?>
<?php $component = $__componentOriginal5624e7818f90ab26cc102f1b791c1b71; ?>
<?php unset($__componentOriginal5624e7818f90ab26cc102f1b791c1b71); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8463834ba515134d5c98b88e1a9dc03)): ?>
<?php $attributes = $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03; ?>
<?php unset($__attributesOriginalc8463834ba515134d5c98b88e1a9dc03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8463834ba515134d5c98b88e1a9dc03)): ?>
<?php $component = $__componentOriginalc8463834ba515134d5c98b88e1a9dc03; ?>
<?php unset($__componentOriginalc8463834ba515134d5c98b88e1a9dc03); ?>
<?php endif; ?>

    <!-- Paginación -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($containers->hasPages()): ?>
        <div class="mt-4">
            <?php echo e($containers->links()); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/livewire/viticulturist/digital-notebook/containers/index.blade.php ENDPATH**/ ?>