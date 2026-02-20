<x-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-agro-50 p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-16 mx-auto object-contain">
            </div>

            <x-agro.card>
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <flux:icon icon="check" class="size-10 text-green-600" />
                    </div>
                    <flux:heading size="xl">¡Cuenta Verificada!</flux:heading>
                    <flux:subheading class="mt-2">Tu email ha sido verificado exitosamente. Ya puedes acceder a todas las funcionalidades de Agro365.</flux:subheading>
                </div>

                <flux:callout variant="success" icon="check-circle" class="mb-6">
                    <flux:callout.text>Bienvenido a Agro365, <strong>{{ auth()->user()->name }}</strong></flux:callout.text>
                </flux:callout>

                <div class="space-y-3">
                    <flux:button href="{{ route($dashboardRoute) }}" variant="primary" class="w-full">
                        Ir al Dashboard
                    </flux:button>
                    <p class="text-xs text-zinc-500 text-center">
                        Serás redirigido automáticamente en <span id="countdown">5</span> segundos...
                    </p>
                </div>
            </x-agro.card>
        </div>
    </div>

    @push('scripts')
    <script>
        let countdown = 5;
        const el = document.getElementById('countdown');
        const url = '{{ route($dashboardRoute) }}';
        const timer = setInterval(() => {
            countdown--;
            el.textContent = countdown;
            if (countdown <= 0) { clearInterval(timer); window.location.href = url; }
        }, 1000);
    </script>
    @endpush
</x-app-layout>
