<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon',
    'title',
    'description',
    'href' => null,
    'iconGradient' => 'from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]',
    'hoverBorder' => 'hover:border-[var(--color-agro-green-light)]/50',
    'hoverText' => 'group-hover:text-[var(--color-agro-green)]',
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
    'icon',
    'title',
    'description',
    'href' => null,
    'iconGradient' => 'from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]',
    'hoverBorder' => 'hover:border-[var(--color-agro-green-light)]/50',
    'hoverText' => 'group-hover:text-[var(--color-agro-green)]',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $tag = $href ? 'a' : 'div';
    $classAttr = $href ? 'group' : 'group cursor-pointer';
    $hrefAttr = $href ? 'href="' . e($href) . '"' : '';
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    <a href="<?php echo e($href); ?>" class="<?php echo e($classAttr); ?>">
<?php else: ?>
    <div class="<?php echo e($classAttr); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="glass-card rounded-xl p-6 hover-lift h-full border-2 border-transparent <?php echo e($hoverBorder); ?> transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br <?php echo e($iconGradient); ?> flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($icon, '<svg')): ?>
                    <?php echo $icon; ?>

                <?php else: ?>
                    <span class="text-3xl"><?php echo e($icon); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
                <div class="w-8 h-8 rounded-lg bg-[var(--color-agro-green-bg)] flex items-center justify-center group-hover:bg-[var(--color-agro-green-light)]/20 transition-colors duration-300">
                    <svg class="w-4 h-4 text-[var(--color-agro-green-dark)] group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2 <?php echo e($hoverText); ?> transition-colors duration-300">
            <?php echo e($title); ?>

        </h3>
        <p class="text-sm text-gray-600 leading-relaxed">
            <?php echo e($description); ?>

        </p>
    </div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($href): ?>
    </a>
<?php else: ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/components/feature-card.blade.php ENDPATH**/ ?>