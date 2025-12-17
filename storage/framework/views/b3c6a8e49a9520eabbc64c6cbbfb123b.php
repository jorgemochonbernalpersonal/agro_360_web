<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['headers', 'emptyMessage' => 'No hay registros', 'emptyDescription' => null, 'emptyIcon' => null, 'color' => 'green']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['headers', 'emptyMessage' => 'No hay registros', 'emptyDescription' => null, 'emptyIcon' => null, 'color' => 'green']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colorClasses = [
        'green' => [
            'header' => 'bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-bright)]/30',
            'text' => 'text-[var(--color-agro-green-dark)]',
            'pagination' => 'bg-gradient-to-r from-[var(--color-agro-green-bg)]/30 to-transparent',
        ],
        'brown' => [
            'header' => 'bg-gradient-to-r from-[var(--color-agro-brown-bg)] to-[var(--color-agro-brown-bright)]/30',
            'text' => 'text-[var(--color-agro-brown-dark)]',
            'pagination' => 'bg-gradient-to-r from-[var(--color-agro-brown-bg)]/30 to-transparent',
        ],
        'blue' => [
            'header' => 'bg-gradient-to-r from-[var(--color-agro-blue)]/20 to-blue-50',
            'text' => 'text-[var(--color-agro-blue)]',
            'pagination' => 'bg-gradient-to-r from-[var(--color-agro-blue)]/20 to-transparent',
        ],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['green'];
?>

<div class="glass-card rounded-2xl overflow-hidden shadow-xl">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($slot) && $slot->isNotEmpty()): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="<?php echo e($colors['header']); ?>">
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-6 py-4 text-left text-xs font-bold <?php echo e($colors['text']); ?> uppercase tracking-wider <?php echo e(is_string($header) && str_contains($header, 'Acciones') ? 'text-right' : ''); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($header)): ?>
                                    <div class="flex items-center gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header['icon'])): ?>
                                            <?php echo $header['icon']; ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php echo e($header['label']); ?>

                                    </div>
                                <?php else: ?>
                                    <?php echo e($header); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php echo e($slot); ?>

                </tbody>
            </table>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pagination)): ?>
            <div class="px-6 py-4 border-t border-gray-200 <?php echo e($colors['pagination']); ?>">
                <?php echo e($pagination); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['message' => $emptyMessage,'description' => $emptyDescription,'icon' => $emptyIcon]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyMessage),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyDescription),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emptyIcon)]); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($emptyAction)): ?>
                 <?php $__env->slot('action', null, []); ?> 
                    <?php echo e($emptyAction); ?>

                 <?php $__env->endSlot(); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/components/data-table.blade.php ENDPATH**/ ?>