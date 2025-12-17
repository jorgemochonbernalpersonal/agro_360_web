<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'view', 'href' => null, 'wireClick' => null, 'wireConfirm' => null, 'disabled' => false]));

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

foreach (array_filter((['variant' => 'view', 'href' => null, 'wireClick' => null, 'wireConfirm' => null, 'disabled' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variants = [
        'view' => 'text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)]',
        'edit' => 'text-blue-600 hover:bg-blue-50',
        'delete' => 'text-red-600 hover:bg-red-50',
        'activate' => 'text-purple-600 hover:bg-purple-50',
        'info' => 'text-purple-600 hover:bg-purple-50',
    ];
    
    $icons = [
        'view' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'delete' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
        'activate' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];
    
    $titles = [
        'view' => 'Ver detalles',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'activate' => 'Activar',
        'info' => 'Activar',
    ];
    
    $classes = 'p-2 rounded-lg transition-all duration-200 group/btn ' . $variants[$variant];
    if($disabled) {
        $classes .= ' opacity-50 cursor-not-allowed pointer-events-none';
    }
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href && !$disabled): ?>
    <a href="<?php echo e($href); ?>" class="<?php echo e($classes); ?>" title="<?php echo e($titles[$variant]); ?>">
        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <?php echo $icons[$variant]; ?>

        </svg>
    </a>
<?php elseif($wireClick && !$disabled): ?>
    <button 
        wire:click="<?php echo e($wireClick); ?>"
        <?php if($wireConfirm): ?> wire:confirm="<?php echo e($wireConfirm); ?>" <?php endif; ?>
        class="<?php echo e($classes); ?>"
        title="<?php echo e($titles[$variant]); ?>"
    >
        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <?php echo $icons[$variant]; ?>

        </svg>
    </button>
<?php else: ?>
    <button class="<?php echo e($classes); ?>" title="<?php echo e($titles[$variant]); ?>" <?php if($disabled): ?> disabled <?php endif; ?>>
        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <?php echo $icons[$variant]; ?>

        </svg>
    </button>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/components/action-button.blade.php ENDPATH**/ ?>