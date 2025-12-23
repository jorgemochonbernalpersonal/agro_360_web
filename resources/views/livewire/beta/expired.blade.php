<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-purple-100 to-pink-100 rounded-full mb-4">
                <span class="text-4xl">⏰</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tu período de beta ha finalizado</h1>
            <p class="text-gray-600">Gracias por probar Agro365 durante la fase beta</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                ¡No pierdas acceso a tus datos!
            </h2>
            
            <div class="space-y-4 mb-6">
                <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-blue-900">Tus datos están seguros</p>
                        <p class="text-sm text-blue-700">Todas tus parcelas, cosechas, facturas y clientes están guardados y esperándote</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 bg-green-50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-green-900">Acceso inmediato al renovar</p>
                        <p class="text-sm text-green-700">Activa tu suscripción y continúa donde lo dejaste, sin perder nada</p>
                    </div>
                </div>
            </div>

            <div class="border-t pt-6">
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-600 mb-2">Precio especial para usuarios beta:</p>
                    <div class="flex items-baseline justify-center gap-2">
                        <span class="text-gray-400 line-through">12€</span>
                        <span class="text-4xl font-bold text-[var(--color-agro-green-dark)]">9€</span>
                        <span class="text-gray-600">/mes</span>
                    </div>
                    <p class="text-xs text-green-600 font-semibold mt-1">25% descuento de por vida</p>
                </div>

                <a href="{{ route('subscription.manage') }}" class="block w-full py-3 px-4 bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] text-white text-center rounded-lg font-semibold shadow-lg hover:shadow-xl transition">
                    Ver planes y activar
                </a>
            </div>
        </div>
    </div>
</div>
