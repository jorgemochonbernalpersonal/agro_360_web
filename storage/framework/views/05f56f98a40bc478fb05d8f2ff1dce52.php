<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => '📋',
    'title',
    'description' => null,
    'iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]',
    'badgeIcon' => null,
    'badgeColor' => 'bg-[var(--color-agro-yellow)]',
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
    'icon' => '📋',
    'title',
    'description' => null,
    'iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]',
    'badgeIcon' => null,
    'badgeColor' => 'bg-[var(--color-agro-yellow)]',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="glass-card rounded-2xl p-8 hover-lift">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-6">
            <div class="relative">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br <?php echo e($iconColor); ?> flex items-center justify-center shadow-lg animate-scale-in">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($icon, '<svg')): ?>
                        <?php echo $icon; ?>

                    <?php else: ?>
                        <span class="text-4xl"><?php echo e($icon); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($badgeIcon): ?>
                    <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full <?php echo e($badgeColor); ?> flex items-center justify-center shadow-md">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($badgeIcon, '<svg')): ?>
                            <?php echo $badgeIcon; ?>

                        <?php else: ?>
                            <span class="text-sm"><?php echo e($badgeIcon); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-[var(--color-agro-green-dark)] mb-2">
                    <?php echo e($title); ?>

                </h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($description): ?>
                    <p class="text-lg text-gray-600 flex items-center gap-2">
                        <?php echo e($description); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actionButton)): ?>
            <div class="flex-shrink-0">
                <?php echo e($actionButton); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/components/page-header.blade.php ENDPATH**/ ?>