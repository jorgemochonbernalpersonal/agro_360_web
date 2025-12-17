<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'icon' => null,
    'color' => 'green',
]));

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

foreach (array_filter(([
    'title',
    'icon' => null,
    'color' => 'green',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colorMap = [
        'green' => 'text-[var(--color-agro-green-dark)]',
        'blue' => 'text-[var(--color-agro-blue)]',
        'brown' => 'text-[var(--color-agro-brown-dark)]',
        'purple' => 'text-purple-700',
        'gray' => 'text-gray-700',
    ];
    $textColor = $colorMap[$color] ?? $colorMap['green'];
?>

<div class="border-b border-gray-200 pb-6 <?php echo e($attributes->get('class')); ?>">
    <h3 class="text-lg font-bold <?php echo e($textColor); ?> mb-4 flex items-center gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <?php echo $icon; ?>

        <?php else: ?>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php echo e($title); ?>

    </h3>
    <?php echo e($slot); ?>

</div>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/components/form-section.blade.php ENDPATH**/ ?>