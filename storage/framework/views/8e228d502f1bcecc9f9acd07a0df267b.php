<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('message')): ?>
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800"><?php echo e(session('message')); ?></p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800"><?php echo e(session('error')); ?></p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Header -->
    <?php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    ?>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['icon' => $icon,'title' => 'Calendario de Actividades','description' => $currentCampaign ? 'Campaña ' . $currentCampaign->name . ' - ' . $currentCampaign->year : 'Visualiza todas tus actividades agrícolas','iconColor' => 'from-[var(--color-agro-yellow)] to-yellow-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'title' => 'Calendario de Actividades','description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentCampaign ? 'Campaña ' . $currentCampaign->name . ' - ' . $currentCampaign->year : 'Visualiza todas tus actividades agrícolas'),'icon-color' => 'from-[var(--color-agro-yellow)] to-yellow-600']); ?>
         <?php $__env->slot('actionButton', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCampaign): ?>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">Campaña:</span>
                    <select 
                        wire:model.live="selectedCampaign" 
                        class="px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-600 focus:border-transparent transition-all bg-white"
                    >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($campaign->id); ?>">
                                <?php echo e($campaign->name); ?> (<?php echo e($campaign->year); ?>)
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($campaign->active): ?> [Activa] <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    <!-- Estadísticas del Mes -->
    <div class="glass-card rounded-xl p-6">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></div>
                <div class="text-sm text-gray-600">Total</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600"><?php echo e($stats['phytosanitary']); ?></div>
                <div class="text-sm text-gray-600">Tratamientos</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600"><?php echo e($stats['fertilization']); ?></div>
                <div class="text-sm text-gray-600">Fertilizaciones</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-cyan-600"><?php echo e($stats['irrigation']); ?></div>
                <div class="text-sm text-gray-600">Riegos</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600"><?php echo e($stats['cultural']); ?></div>
                <div class="text-sm text-gray-600">Labores</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-600"><?php echo e($stats['observation']); ?></div>
                <div class="text-sm text-gray-600">Observaciones</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Filtro por Tipo de Actividad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Actividad</label>
                <select 
                    wire:model.live="activityType" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-600 focus:border-transparent transition-all"
                >
                    <option value="">Todas las actividades</option>
                    <option value="phytosanitary">Tratamientos Fitosanitarios</option>
                    <option value="fertilization">Fertilizaciones</option>
                    <option value="irrigation">Riegos</option>
                    <option value="cultural">Labores Culturales</option>
                    <option value="observation">Observaciones</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="glass-card rounded-xl p-6">
        <!-- Navegación del Calendario -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <button 
                    wire:click="previousMonth"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    title="Mes anterior"
                >
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h2 class="text-2xl font-bold text-gray-900">
                    <?php echo e($monthName); ?> <?php echo e($currentYear); ?>

                </h2>
                <button 
                    wire:click="nextMonth"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    title="Mes siguiente"
                >
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <button 
                wire:click="goToToday"
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors font-semibold"
            >
                Hoy
            </button>
        </div>

        <!-- Días de la semana -->
        <div class="grid grid-cols-7 gap-2 mb-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-center text-sm font-bold text-gray-700 py-2">
                    <?php echo e($day); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Días del calendario -->
        <div class="grid grid-cols-7 gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $calendarDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div 
                    class="min-h-[100px] border-2 rounded-lg p-2 transition-all cursor-pointer hover:shadow-md
                        <?php echo e($day['isCurrentMonth'] ? 'bg-white border-gray-200' : 'bg-gray-50 border-gray-100 opacity-60'); ?>

                        <?php echo e($day['isToday'] ? 'ring-2 ring-yellow-500 border-yellow-500' : ''); ?>

                    "
                    wire:click="selectDate('<?php echo e($day['dateKey']); ?>')"
                >
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold <?php echo e($day['isToday'] ? 'text-yellow-600' : ($day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400')); ?>">
                            <?php echo e($day['day']); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day['activityCount'] > 0): ?>
                            <span class="text-xs font-bold text-gray-600 bg-gray-200 px-2 py-0.5 rounded-full">
                                <?php echo e($day['activityCount']); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <div class="space-y-1 mt-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $day['activities']->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div 
                                class="text-xs px-2 py-1 rounded border <?php echo e($this->getActivityTypeColor($activity->activity_type)); ?> truncate"
                                title="<?php echo e($this->getActivityTypeLabel($activity->activity_type)); ?> - <?php echo e($activity->plot->name); ?>"
                            >
                                <?php echo e($this->getActivityTypeLabel($activity->activity_type)); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($day['activityCount'] > 3): ?>
                            <div class="text-xs text-gray-500 font-semibold text-center">
                                +<?php echo e($day['activityCount'] - 3); ?> más
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Leyenda</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-100 border border-red-300"></div>
                <span class="text-sm text-gray-700">Tratamientos</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-blue-100 border border-blue-300"></div>
                <span class="text-sm text-gray-700">Fertilizaciones</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-cyan-100 border border-cyan-300"></div>
                <span class="text-sm text-gray-700">Riegos</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-yellow-100 border border-yellow-300"></div>
                <span class="text-sm text-gray-700">Labores</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-100 border border-gray-300"></div>
                <span class="text-sm text-gray-700">Observaciones</span>
            </div>
        </div>
    </div>

    <!-- Modal de Actividades del Día -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showActivityModal && $selectedActivity): ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeModal">
            <div class="glass-card rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        Actividades del <?php echo e($this->getFormattedSelectedDate()); ?>

                    </h3>
                    <button 
                        wire:click="closeModal"
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedActivity->count() > 0): ?>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border-2 rounded-lg p-4 <?php echo e($this->getActivityTypeColor($activity->activity_type)); ?>">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <span class="font-bold text-lg"><?php echo e($this->getActivityTypeLabel($activity->activity_type)); ?></span>
                                        <span class="text-sm text-gray-600 ml-2"><?php echo e($activity->plot->name); ?></span>
                                    </div>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->phytosanitaryTreatment): ?>
                                    <div class="text-sm mt-2">
                                        <p><strong>Producto:</strong> <?php echo e($activity->phytosanitaryTreatment->product->name); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->phytosanitaryTreatment->area_treated): ?>
                                            <p><strong>Área tratada:</strong> <?php echo e(number_format($activity->phytosanitaryTreatment->area_treated, 3)); ?> ha</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->phytosanitaryTreatment->target_pest): ?>
                                            <p><strong>Objetivo:</strong> <?php echo e($activity->phytosanitaryTreatment->target_pest); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php elseif($activity->fertilization): ?>
                                    <div class="text-sm mt-2">
                                        <p><strong>Fertilizante:</strong> <?php echo e($activity->fertilization->fertilizer_name ?: 'N/A'); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->fertilization->quantity): ?>
                                            <p><strong>Cantidad:</strong> <?php echo e(number_format($activity->fertilization->quantity, 2)); ?> kg</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php elseif($activity->irrigation): ?>
                                    <div class="text-sm mt-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->irrigation->water_volume): ?>
                                            <p><strong>Volumen de agua:</strong> <?php echo e(number_format($activity->irrigation->water_volume, 2)); ?> L</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php elseif($activity->culturalWork): ?>
                                    <div class="text-sm mt-2">
                                        <p><strong>Tipo de labor:</strong> <?php echo e($activity->culturalWork->work_type ?: 'N/A'); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->culturalWork->hours_worked): ?>
                                            <p><strong>Horas trabajadas:</strong> <?php echo e(number_format($activity->culturalWork->hours_worked, 2)); ?> h</p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php elseif($activity->observation): ?>
                                    <div class="text-sm mt-2">
                                        <p><strong>Tipo:</strong> <?php echo e($activity->observation->observation_type ?: 'N/A'); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->observation->severity): ?>
                                            <p><strong>Severidad:</strong> <?php echo e(ucfirst($activity->observation->severity)); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->crew): ?>
                                    <p class="text-sm mt-2"><strong>Cuadrilla:</strong> <?php echo e($activity->crew->name); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->machinery): ?>
                                    <p class="text-sm mt-2"><strong>Maquinaria:</strong> <?php echo e($activity->machinery->name); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->notes): ?>
                                    <p class="text-sm mt-2"><strong>Notas:</strong> <?php echo e($activity->notes); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">No hay actividades registradas para esta fecha</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/livewire/viticulturist/calendar.blade.php ENDPATH**/ ?>