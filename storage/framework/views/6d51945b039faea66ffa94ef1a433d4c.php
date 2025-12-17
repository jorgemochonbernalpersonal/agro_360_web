<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agro365</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles / Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
        <!-- Sidebar -->
        <?php if (isset($component)) { $__componentOriginal2880b66d47486b4bfeaf519598a469d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2880b66d47486b4bfeaf519598a469d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $attributes = $__attributesOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $component = $__componentOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__componentOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
        
        <!-- Top Bar -->
        <?php if (isset($component)) { $__componentOriginaleb97fe2d2a21304911c5baf409644ddc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleb97fe2d2a21304911c5baf409644ddc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.top-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('top-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleb97fe2d2a21304911c5baf409644ddc)): ?>
<?php $attributes = $__attributesOriginaleb97fe2d2a21304911c5baf409644ddc; ?>
<?php unset($__attributesOriginaleb97fe2d2a21304911c5baf409644ddc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleb97fe2d2a21304911c5baf409644ddc)): ?>
<?php $component = $__componentOriginaleb97fe2d2a21304911c5baf409644ddc; ?>
<?php unset($__componentOriginaleb97fe2d2a21304911c5baf409644ddc); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <!-- Main Content -->
    <main class="min-h-screen transition-all duration-300 <?php if(auth()->guard()->check()): ?> pt-16 lg:pl-72 <?php endif; ?>" id="main-content">
        <div class="<?php if(auth()->guard()->check()): ?> p-4 lg:p-8 <?php else: ?> p-0 <?php endif; ?>">
            <?php echo e($slot); ?>

        </div>
    </main>
    
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    
    <!-- Script para ajustar el main con el sidebar colapsable -->
    <script>
        if (typeof window.mainContentObserver === 'undefined') {
            window.mainContentObserver = setInterval(() => {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');
                
                if (sidebar && mainContent && window.innerWidth >= 1024) {
                    const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                    
                    if (isCollapsed) {
                        mainContent.classList.remove('lg:pl-72');
                        mainContent.classList.add('lg:pl-20');
                    } else {
                        mainContent.classList.remove('lg:pl-20');
                        mainContent.classList.add('lg:pl-72');
                    }
                }
            }, 100);
        }
    </script>
</body>
</html>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/layouts/app.blade.php ENDPATH**/ ?>