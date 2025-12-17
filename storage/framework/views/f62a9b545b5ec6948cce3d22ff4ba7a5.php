<div>
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
        <div class="p-8 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border-2 border-[var(--color-agro-green-light)]/30">
            <div class="flex items-center gap-4 mb-6">
                <div class="text-5xl">💳</div>
                <div>
                    <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)]">
                        Suscripciones
                    </h1>
                    <p class="text-[var(--color-agro-green)] text-lg">
                        Gestiona tu suscripción a Agro365
                    </p>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <?php echo e(session('message')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(session()->has('error')): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeSubscription): ?>
                <div class="bg-gradient-to-r from-green-100 to-green-50 rounded-xl p-6 mb-6 border border-green-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-green-800 mb-2">Suscripción Activa</h2>
                            <p class="text-green-700">
                                <strong>Plan:</strong> <?php echo e($activeSubscription->plan_type === 'yearly' ? 'Anual' : 'Mensual'); ?>

                            </p>
                            <p class="text-green-700">
                                <strong>Precio:</strong> <?php echo e(number_format($activeSubscription->amount, 2)); ?> €
                            </p>
                            <p class="text-green-700">
                                <strong>Válida hasta:</strong> <?php echo e($activeSubscription->ends_at->format('d/m/Y')); ?>

                            </p>
                        </div>
                        <button wire:click="cancelSubscription" 
                            wire:confirm="¿Estás seguro de que quieres cancelar tu suscripción?"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                            Cancelar Suscripción
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Selecciona un Plan</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Plan Mensual -->
                        <div class="border-2 rounded-xl p-6 cursor-pointer transition-all 
                            <?php echo e($selectedPlan === 'monthly' ? 'border-[var(--color-agro-green)] bg-green-50' : 'border-gray-300'); ?>"
                            wire:click="selectPlan('monthly')">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900">Plan Mensual</h3>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPlan === 'monthly'): ?>
                                    <div class="w-6 h-6 bg-[var(--color-agro-green)] rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <span class="text-4xl font-bold text-[var(--color-agro-green-dark)]">8€</span>
                                <span class="text-gray-600">/mes</span>
                            </div>
                            <ul class="space-y-2 text-gray-700 mb-6">
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Acceso completo a todas las funcionalidades
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Soporte técnico incluido
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Cancelación en cualquier momento
                                </li>
                            </ul>
                        </div>

                        <!-- Plan Anual -->
                        <div class="border-2 rounded-xl p-6 cursor-pointer transition-all relative
                            <?php echo e($selectedPlan === 'yearly' ? 'border-[var(--color-agro-green)] bg-green-50' : 'border-gray-300'); ?>"
                            wire:click="selectPlan('yearly')">
                            <div class="absolute top-4 right-4 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">
                                MEJOR OFERTA
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-900">Plan Anual</h3>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPlan === 'yearly'): ?>
                                    <div class="w-6 h-6 bg-[var(--color-agro-green)] rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <span class="text-4xl font-bold text-[var(--color-agro-green-dark)]">90€</span>
                                <span class="text-gray-600">/año</span>
                                <p class="text-sm text-gray-500 mt-1">Ahorra 6€ al año</p>
                            </div>
                            <ul class="space-y-2 text-gray-700 mb-6">
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Acceso completo a todas las funcionalidades
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Soporte técnico prioritario
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Ahorro del 6% vs plan mensual
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <button wire:click="initiatePayment" 
                            class="px-8 py-3 bg-[var(--color-agro-green)] text-white rounded-lg hover:bg-[var(--color-agro-green-dark)] transition text-lg font-semibold">
                            Pagar con PayPal
                        </button>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('redirect-to-paypal', (event) => {
                        window.location.href = event.url;
                    });
                });
            </script>
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
</div>
<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/livewire/subscription/manage.blade.php ENDPATH**/ ?>