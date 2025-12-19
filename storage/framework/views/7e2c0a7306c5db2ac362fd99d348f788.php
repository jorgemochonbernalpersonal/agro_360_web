<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <div class="text-center mb-6">
            <div class="inline-block max-w-[200px] mx-auto mb-4">
                <img 
                    src="<?php echo e(asset('images/logo.png')); ?>" 
                    alt="Agro365 Logo" 
                    class="w-full h-auto object-contain drop-shadow-2xl"
                >
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifica tu Email</h2>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status') == 'verification-link-sent'): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <p class="text-sm font-medium">Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.</p>
            </div>
        <?php else: ?>
            <p class="text-gray-600 text-sm mb-6 text-center">
                Gracias por registrarte. Antes de continuar, por favor verifica tu email haciendo clic en el enlace que te enviamos a 
                <strong><?php echo e(auth()->user()->email); ?></strong>.
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('verification.send')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg"
            >
                Reenviar Email de Verificación
            </button>
        </form>

        <div class="mt-6 text-center">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Cerrar Sesión
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-4">
                ¿No recibiste el email? Revisa tu carpeta de spam o solicita un nuevo enlace.
            </p>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/auth/verify-email.blade.php ENDPATH**/ ?>