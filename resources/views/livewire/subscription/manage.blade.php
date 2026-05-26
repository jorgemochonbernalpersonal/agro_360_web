<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">{{ __('Suscripción') }}</h1>
            <p class="text-zinc-500 text-sm">{{ __('Gestiona tu plan de Agro365') }}</p>
        </div>
    </div>

    @if (session()->has('upgrade_required'))
        @php $upgrade = session('upgrade_required'); @endphp
        <div class="bg-amber-50 border border-amber-300 rounded-xl p-5 flex items-start gap-4">
            <div class="flex-shrink-0 bg-amber-100 rounded-full p-2">
                <flux:icon icon="lock-closed" class="size-5 text-amber-600" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-amber-900">{{ __('Esta funcionalidad requiere el plan Completo') }}</p>
                <p class="text-sm text-amber-800 mt-0.5">
                    {!! __('Actualiza tu plan por <strong>{{ __(':price€/mes') }}</strong> para desbloquear funcionalidades avanzadas como PAC, facturación, almacén, maquinaria y más.', ['price' => $upgrade['price']]) !!}
                </p>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <flux:callout variant="info">
            <flux:callout.text>{{ session('info') }}</flux:callout.text>
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger">
            <flux:callout.text>{{ session('error') }}</flux:callout.text>
        </flux:callout>
    @endif

    @if($activeSubscription)
        {{-- Suscripción activa --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="check-circle" class="size-5 text-green-600" />
                        <span class="font-semibold text-green-800">{{ __('Plan Completo activo') }}</span>
                    </div>
                    <p class="text-green-700 text-sm">
                        <strong>{{ __('Tipo') }}:</strong> {{ $activeSubscription->plan_type === 'yearly' ? __('Anual') : __('Mensual') }}
                        &nbsp;·&nbsp;
                        <strong>{{ __('Precio') }}:</strong> {{ number_format($activeSubscription->amount, 2) }}€
                        &nbsp;·&nbsp;
                        <strong>{{ __('Válida hasta') }}:</strong> {{ $activeSubscription->ends_at->format('d/m/Y') }}
                    </p>
                </div>
                <flux:button
                    wire:click="cancelSubscription"
                    wire:confirm="{{ __('¿Seguro que quieres cancelar? Perderás el acceso al plan Completo al finalizar el periodo.') }}"
                    variant="danger"
                    size="sm"
                >
                    {{ __('Cancelar') }}
                </flux:button>
            </div>
        </div>

    @else
        {{-- Sin suscripción activa --}}

        @if($isWineryLinked)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
                <flux:icon icon="information-circle" class="size-5 text-blue-500 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-800">
                    <strong>{{ __('Tienes acceso básico gratuito') }}</strong> {{ __('porque estás vinculado a una bodega.') }}
                    {{ __('El cuaderno de campo, parcelas, SIGPAC y fenología son gratis para siempre.') }}
                    {{ __('Actualiza al plan Completo para acceder a PAC, facturación, almacén, maquinaria y más.') }}
                </div>
            </div>
        @endif

        {{-- Selector de plan --}}
        <div class="grid grid-cols-2 gap-4">

            {{-- Plan Mensual --}}
            <button
                wire:click="selectPlan('monthly')"
                class="relative border-2 rounded-xl p-6 text-left transition-all
                    {{ $selectedPlan === 'monthly'
                        ? 'border-[var(--color-agro-green)] bg-green-50'
                        : 'border-zinc-200 bg-white hover:border-zinc-300' }}"
            >
                <div class="font-semibold text-zinc-900 mb-1">{{ __('Mensual') }}</div>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-bold text-[var(--color-agro-green-dark)]">{{ number_format($monthlyPrice, 0) }}€</span>
                    <span class="text-zinc-500 text-sm">/{{ __('mes') }}</span>
                </div>
                @if($isWineryLinked)
                    <p class="text-xs text-green-600 font-medium mt-1">{{ __('Precio especial por estar vinculado a bodega') }}</p>
                @endif
                @if($selectedPlan === 'monthly')
                    <div class="absolute top-3 right-3">
                        <flux:icon icon="check-circle" class="size-5 text-[var(--color-agro-green)]" />
                    </div>
                @endif
            </button>

            {{-- Plan Anual --}}
            <button
                wire:click="selectPlan('yearly')"
                class="relative border-2 rounded-xl p-6 text-left transition-all
                    {{ $selectedPlan === 'yearly'
                        ? 'border-[var(--color-agro-green)] bg-green-50'
                        : 'border-zinc-200 bg-white hover:border-zinc-300' }}"
            >
                <div class="absolute top-3 right-3 bg-amber-400 text-amber-900 text-xs font-bold px-2 py-0.5 rounded">
                    {{ __('MEJOR OFERTA') }}
                </div>
                <div class="font-semibold text-zinc-900 mb-1">{{ __('Anual') }}</div>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-bold text-[var(--color-agro-green-dark)]">{{ number_format($yearlyPrice, 0) }}€</span>
                    <span class="text-zinc-500 text-sm">/{{ __('año') }}</span>
                </div>
                <p class="text-xs text-zinc-500 mt-1">{{ number_format($yearlyPrice / 12, 2) }}€/{{ __('mes') }} · {{ __('ahorras :n€', ['n' => number_format($monthlyPrice * 12 - $yearlyPrice, 0)]) }}</p>
                @if($isWineryLinked)
                    <p class="text-xs text-green-600 font-medium">{{ __('Precio especial por estar vinculado a bodega') }}</p>
                @endif
                @if($selectedPlan === 'yearly')
                    <div class="absolute top-8 right-3">
                        <flux:icon icon="check-circle" class="size-5 text-[var(--color-agro-green)]" />
                    </div>
                @endif
            </button>
        </div>

        {{-- Lo que incluye el plan Completo --}}
        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-5">
            <p class="text-sm font-semibold text-zinc-700 mb-3">{{ __('Plan Completo incluye todo lo básico más:') }}</p>
            <div class="grid grid-cols-2 gap-1.5 text-sm text-zinc-600">
                @foreach([
                    __('PAC y declaraciones'),
                    __('Facturación + Verifactu'),
                    __('Almacén de insumos'),
                    __('Maquinaria y equipos'),
                    __('Explotaciones (SIEX/DGC)'),
                    __('CUE exports'),
                    __('Residuos y análisis'),
                    __('Informes oficiales'),
                ] as $feature)
                    <div class="flex items-center gap-1.5">
                        <flux:icon icon="check" class="size-4 text-green-500 flex-shrink-0" />
                        {{ $feature }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Botón de pago --}}
        <flux:button
            wire:click="initiatePayment"
            wire:loading.attr="disabled"
            variant="primary"
            class="w-full"
        >
            <span wire:loading.remove>
                {{ $selectedPlan === 'yearly'
                    ? __('Suscribirse por :price€/año', ['price' => number_format($yearlyPrice, 0)])
                    : __('Suscribirse por :price€/mes', ['price' => number_format($monthlyPrice, 0)]) }}
            </span>
            <span wire:loading>{{ __('Redirigiendo a PayPal...') }}</span>
        </flux:button>

        <p class="text-center text-xs text-zinc-400">{{ __('Pago seguro vía PayPal · Cancela cuando quieras') }}</p>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('redirect-to-paypal', (event) => {
                window.location.href = event.url;
            });
        });
    </script>
</div>
