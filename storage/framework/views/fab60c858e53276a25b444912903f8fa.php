<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 py-6 px-4">
    <!-- Elementos decorativos -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-agro-green-light)]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-[var(--color-agro-green)]/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="w-full max-w-md mx-auto relative z-10">
        <div class="text-center mb-3">
            <div class="inline-block max-w-[180px] mx-auto mb-2">
                <img 
                    src="<?php echo e(asset('images/logo.png')); ?>" 
                    alt="Agro365 Logo" 
                    class="w-full h-auto max-h-24 object-contain drop-shadow-lg"
                >
            </div>
            <p class="text-gray-600 text-sm font-medium">Gestión agrícola inteligente</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-1 text-center">Iniciar Sesión</h2>
            <p class="text-gray-500 mb-6 text-center text-sm">Ingresa tus credenciales para continuar</p>
            
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email
                    </label>
                    <input 
                        wire:model="email" 
                        type="email" 
                        id="email"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        placeholder="correo@ejemplo.com"
                        required
                    >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <p class="mt-1 text-sm text-red-600 font-medium"><?php echo e($message); ?></p> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input 
                        wire:model="password" 
                        type="password" 
                        id="password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        placeholder="••••••••"
                        required
                    >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <p class="mt-1 text-sm text-red-600 font-medium"><?php echo e($message); ?></p> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            wire:model="remember" 
                            type="checkbox" 
                            id="remember"
                            class="w-4 h-4 text-[var(--color-agro-green-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-green-dark)]"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Recordarme
                        </label>
                    </div>
                    <a href="#" class="text-sm text-[var(--color-agro-green-dark)] hover:underline">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg"
                >
                    Iniciar Sesión
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿No tienes cuenta? 
                    <a href="<?php echo e(route('register')); ?>" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/livewire/auth/login.blade.php ENDPATH**/ ?>