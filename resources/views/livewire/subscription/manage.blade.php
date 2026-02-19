<div class="p-8 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border-2 border-agro-400/30">
            <div class="flex items-center gap-4 mb-6">
                <div class="text-5xl">💳</div>
                <div>
                    <h1 class="text-3xl font-bold text-agro-700">
                        Suscripciones
                    </h1>
                    <p class="text-agro-600 text-lg">
                        Gestiona tu suscripción a Agro365
                    </p>
                </div>
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-6">
                    <flux:callout.text>{{ session('message') }}</flux:callout.text>
                </flux:callout>
            @endif

            @if (session()->has('error'))
                <flux:callout variant="danger" class="mb-6">
                    <flux:callout.text>{{ session('error') }}</flux:callout.text>
                </flux:callout>
            @endif

            @if($activeSubscription)
                <div class="bg-gradient-to-r from-green-100 to-green-50 rounded-xl p-6 mb-6 border border-green-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-green-800 mb-2">Suscripción Activa</h2>
                            <p class="text-green-700">
                                <strong>Plan:</strong> {{ $activeSubscription->plan_type === 'yearly' ? 'Anual' : 'Mensual' }}
                            </p>
                            <p class="text-green-700">
                                <strong>Precio:</strong> {{ number_format($activeSubscription->amount, 2) }} €
                            </p>
                            <p class="text-green-700">
                                <strong>Válida hasta:</strong> {{ $activeSubscription->ends_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button wire:click="cancelSubscription"
                            wire:confirm="¿Estás seguro de que quieres cancelar tu suscripción?"
                            variant="danger">
                            Cancelar Suscripción
                        </flux:button>
                    </div>
                </div>
            @else
                <!-- Banner de Fase Beta -->
                <div class="mb-8 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 rounded-2xl p-8 border-2 border-blue-200 shadow-xl">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="text-6xl">🎉</div>
                        <div>
                            <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                                ¡Bienvenido a la Fase Beta de Agro365!
                            </h2>
                            <p class="text-zinc-700 text-lg">
                                Como early adopter, disfrutarás de beneficios exclusivos
                            </p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <!-- Beneficio 1 -->
                        <div class="bg-white/70 backdrop-blur rounded-xl p-6 border border-blue-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <flux:icon icon="clock" class="size-6 text-blue-600" />
                                </div>
                                <h3 class="text-xl font-bold text-zinc-900">6 Meses Gratuitos</h3>
                            </div>
                            <p class="text-zinc-600">
                                Acceso completo a todas las funcionalidades sin coste alguno durante el periodo beta
                            </p>
                        </div>

                        <!-- Beneficio 2 -->
                        <div class="bg-white/70 backdrop-blur rounded-xl p-6 border border-purple-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <flux:icon icon="currency-euro" class="size-6 text-purple-600" />
                                </div>
                                <h3 class="text-xl font-bold text-zinc-900">25% Descuento de Por Vida (primeros 50 viticultores)</h3>
                            </div>
                            <p class="text-zinc-600">
                                Tras la fase beta, los <strong>50 primeros viticultores</strong> disfrutarán de un <strong>25% de descuento permanente</strong> en cualquier plan
                            </p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-6 border-l-4 border-amber-400">
                        <div class="flex items-start gap-3">
                            <flux:icon icon="information-circle" class="size-6 text-amber-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="font-bold text-amber-900 mb-2">¿Qué significa esto para ti?</h4>
                                @php
                                    $betaEndDate = now()->addMonths(6)->endOfMonth();
                                @endphp
                                <ul class="space-y-2 text-amber-800 text-sm">
                                    <li class="flex items-center gap-2">
                                        <flux:icon icon="check" class="size-4 text-green-600 flex-shrink-0" />
                                        <span>Uso totalmente <strong>gratuito hasta {{ $betaEndDate->format('d/m/Y') }}</strong></span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <flux:icon icon="check" class="size-4 text-green-600 flex-shrink-0" />
                                        <span>Después: <del class="text-zinc-500">12€/mes</del> → <strong class="text-green-600">solo 9€/mes</strong> (Plan Mensual, 25% dto.)</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <flux:icon icon="check" class="size-4 text-green-600 flex-shrink-0" />
                                        <span>O bien: <del class="text-zinc-500">120€/año</del> → <strong class="text-green-600">solo 90€/año</strong> (Plan Anual, 7,50€/mes, 25% dto.)</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <flux:icon icon="check" class="size-4 text-green-600 flex-shrink-0" />
                                        <span>El 25% de descuento de por vida está limitado a los <strong>50 primeros viticultores</strong> que se registren antes del <strong>{{ $betaEndDate->format('d/m/Y') }}</strong></span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <flux:icon icon="check" class="size-4 text-green-600 flex-shrink-0" />
                                        <span>Este descuento es <strong>permanente</strong> mientras mantengas tu suscripción</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-center p-6 bg-white/50 rounded-xl border border-zinc-200">
                        <div class="inline-flex items-center gap-2 text-zinc-700">
                            <flux:icon icon="information-circle" class="size-5 text-blue-500" />
                            <p class="text-sm">
                                <strong>Nota:</strong> El sistema de pagos estará disponible al finalizar la fase beta. 
                                Mientras tanto, ¡disfruta de Agro365 sin límites!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Previsualización de Planes (deshabilitado) -->
                <div class="mb-6 opacity-60 pointer-events-none">
                    <h2 class="text-xl font-bold text-agro-700 mb-4 text-center">
                        Planes Futuros (25% de descuento para ti)
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Plan Mensual -->
                        <div class="border-2 rounded-xl p-6 border-zinc-200 bg-zinc-50/50">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-zinc-900">Plan Mensual</h3>
                            </div>
                            <div class="mb-4">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl text-zinc-400 line-through">12€</span>
                                    <span class="text-4xl font-bold text-agro-700">9€</span>
                                    <span class="text-zinc-600">/mes</span>
                                </div>
                                <p class="text-sm text-green-600 font-semibold mt-1">Tu precio especial (25% dto.)</p>
                            </div>
                            <ul class="space-y-2 text-zinc-700 mb-6">
                                <li class="flex items-center">
                                    <flux:icon icon="check" class="size-5 text-green-500 mr-2 flex-shrink-0" />
                                    Acceso completo a todas las funcionalidades
                                </li>
                                <li class="flex items-center">
                                    <flux:icon icon="check" class="size-5 text-green-500 mr-2 flex-shrink-0" />
                                    Soporte técnico incluido
                                </li>
                                <li class="flex items-center">
                                    <flux:icon icon="check" class="size-5 text-green-500 mr-2 flex-shrink-0" />
                                    Cancelación en cualquier momento
                                </li>
                            </ul>
                        </div>

                        <!-- Plan Anual -->
                        <div class="border-2 rounded-xl p-6 border-zinc-200 bg-zinc-50/50 relative">
                            <div class="absolute top-4 right-4 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">
                                MEJOR OFERTA
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-zinc-900">Plan Anual</h3>
                            </div>
                            <div class="mb-4">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl text-zinc-400 line-through">120€</span>
                                    <span class="text-4xl font-bold text-agro-700">90€</span>
                                    <span class="text-zinc-600">/año</span>
                                </div>
                                <p class="text-sm text-green-600 font-semibold mt-1">Tu precio especial (7,50€/mes, 25% dto.)</p>
                            </div>
                            <ul class="space-y-2 text-zinc-700 mb-6">
                                <li class="flex items-center">
                                    <flux:icon icon="check" class="size-5 text-green-500 mr-2 flex-shrink-0" />
                                    Acceso completo a todas las funcionalidades
                                </li>
                                <li class="flex items-center">
                                    <flux:icon icon="check" class="size-5 text-green-500 mr-2 flex-shrink-0" />
                                    Soporte técnico prioritario
                                </li>
                                <li class="flex items-center">
                                    <flux:icon icon="check" class="size-5 text-green-500 mr-2 flex-shrink-0" />
                                    Ahorro de 18€/año vs plan mensual
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('redirect-to-paypal', (event) => {
                        window.location.href = event.url;
                    });
                });
            </script>
</div>
