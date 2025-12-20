<div class="space-y-6 animate-fade-in">
    <?php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    ?>

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['icon' => $icon,'title' => 'Plantaciones','description' => 'Listado global de plantaciones de variedades en tus parcelas','iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'title' => 'Plantaciones','description' => 'Listado global de plantaciones de variedades en tus parcelas','icon-color' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]']); ?>
         <?php $__env->slot('actionButton', null, []); ?> 
            <a href="<?php echo e(route('plots.index')); ?>" class="group">
                <button
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ver parcelas
                </button>
            </a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-input','data' => ['wire:model.live' => 'search','placeholder' => 'Buscar por parcela o variedad...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'search','placeholder' => 'Buscar por parcela o variedad...']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'status']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'status']); ?>
            <option value="">Todos los estados</option>
            <option value="active">Activa</option>
            <option value="removed">Arrancada</option>
            <option value="experimental">Experimental</option>
            <option value="replanting">Replantación</option>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter-select','data' => ['wire:model.live' => 'year']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'year']); ?>
            <option value="">Todos los años</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($yearOption); ?>"><?php echo e($yearOption); ?></option>
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
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $status !== '' || $year !== ''): ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['wire:click' => 'clearFilters','variant' => 'ghost','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'clearFilters','variant' => 'ghost','size' => 'sm']); ?>
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

    <?php
        $headers = [
            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Variedad', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/></svg>'],
            ['label' => 'Superficie (ha)', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>'],
            ['label' => 'Año', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5A2 2 0 003 7v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            'Acciones',
        ];
    ?>

    <?php if (isset($component)) { $__componentOriginalc8463834ba515134d5c98b88e1a9dc03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8463834ba515134d5c98b88e1a9dc03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-table','data' => ['headers' => $headers,'emptyMessage' => 'No hay plantaciones registradas','emptyDescription' => 'Comienza creando una plantación sobre una parcela']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($headers),'empty-message' => 'No hay plantaciones registradas','empty-description' => 'Comienza creando una plantación sobre una parcela']); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plantings->count() > 0): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plantings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($planting->name): ?>
                            <span class="text-sm font-semibold text-purple-700">
                                <?php echo e($planting->name); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-sm text-gray-400 italic">—</span>
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
                        <div class="flex flex-col">
                            <a href="<?php echo e(route('plots.show', $planting->plot)); ?>"
                               class="text-sm font-bold text-[var(--color-agro-green-dark)] hover:underline">
                                <?php echo e($planting->plot->name); ?>

                            </a>
                            <span class="text-xs text-gray-500">
                                <?php echo e($planting->plot->municipality?->name ?? ''); ?>

                            </span>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($planting->grapeVariety): ?>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">
                                    <?php echo e($planting->grapeVariety->name); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($planting->grapeVariety->code): ?>
                                        (<?php echo e($planting->grapeVariety->code); ?>)
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                                <?php
                                    $colorMap = [
                                        'red' => ['Tinto', 'bg-red-100 text-red-800'],
                                        'white' => ['Blanco', 'bg-yellow-100 text-yellow-800'],
                                        'rose' => ['Rosado', 'bg-pink-100 text-pink-800'],
                                    ];
                                    $colorInfo = $planting->grapeVariety->color ? ($colorMap[$planting->grapeVariety->color] ?? null) : null;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($colorInfo): ?>
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo e($colorInfo[1]); ?>">
                                        <?php echo e($colorInfo[0]); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-sm text-gray-500">Sin variedad</span>
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
                        <span class="text-sm font-medium text-gray-900">
                            <?php echo e(number_format($planting->area_planted, 3)); ?> ha
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
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-700">
                                <?php echo e($planting->planting_year ?? '—'); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($planting->planting_date): ?>
                                <span class="text-xs text-gray-500">
                                    <?php echo e($planting->planting_date->format('d/m/Y')); ?>

                                </span>
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
                        <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'removed' => 'bg-red-100 text-red-800',
                                'experimental' => 'bg-purple-100 text-purple-800',
                                'replanting' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $label = [
                                'active' => 'Activa',
                                'removed' => 'Arrancada',
                                'experimental' => 'Experimental',
                                'replanting' => 'Replantación',
                            ][$planting->status] ?? $planting->status;
                        ?>
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?php echo e($statusColors[$planting->status] ?? 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo e($label); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($planting->irrigated): ?>
                                <span class="inline-flex items-center gap-1 text-[11px] text-[var(--color-agro-blue)]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2.69l5.66 5.66a6 6 0 11-11.32 0L12 2.69z" />
                                    </svg>
                                    Con riego
                                </span>
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
                        <span class="text-sm text-gray-700">
                            <?php echo e($planting->plot->viticulturist?->name ?? 'Sin asignar'); ?>

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
                        <?php if (isset($component)) { $__componentOriginal35f57f4e82a16e7ad7641b9fb6c7f399 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal35f57f4e82a16e7ad7641b9fb6c7f399 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table-actions','data' => ['align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table-actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right']); ?>
                            <?php if (isset($component)) { $__componentOriginald4c6978101b1c254eb70511d3c21c03f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c6978101b1c254eb70511d3c21c03f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-button','data' => ['variant' => 'view','href' => ''.e(route('plots.show', $planting->plot)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'view','href' => ''.e(route('plots.show', $planting->plot)).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $attributes = $__attributesOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $component = $__componentOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__componentOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $planting->plot)): ?>
                                <?php if (isset($component)) { $__componentOriginald4c6978101b1c254eb70511d3c21c03f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c6978101b1c254eb70511d3c21c03f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action-button','data' => ['variant' => 'edit','href' => ''.e(route('plots.plantings.edit', $planting)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('action-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'edit','href' => ''.e(route('plots.plantings.edit', $planting)).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $attributes = $__attributesOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__attributesOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c6978101b1c254eb70511d3c21c03f)): ?>
<?php $component = $__componentOriginald4c6978101b1c254eb70511d3c21c03f; ?>
<?php unset($__componentOriginald4c6978101b1c254eb70511d3c21c03f); ?>
<?php endif; ?>
                            <?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal35f57f4e82a16e7ad7641b9fb6c7f399)): ?>
<?php $attributes = $__attributesOriginal35f57f4e82a16e7ad7641b9fb6c7f399; ?>
<?php unset($__attributesOriginal35f57f4e82a16e7ad7641b9fb6c7f399); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal35f57f4e82a16e7ad7641b9fb6c7f399)): ?>
<?php $component = $__componentOriginal35f57f4e82a16e7ad7641b9fb6c7f399; ?>
<?php unset($__componentOriginal35f57f4e82a16e7ad7641b9fb6c7f399); ?>
<?php endif; ?>
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

             <?php $__env->slot('pagination', null, []); ?> 
                <?php echo e($plantings->links()); ?>

             <?php $__env->endSlot(); ?>
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
</div>


<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/livewire/plots/plantings/index.blade.php ENDPATH**/ ?>