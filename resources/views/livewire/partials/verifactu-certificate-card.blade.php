{{-- Certificado de firma VeriFactu — cada usuario sube el suyo propio. --}}
<x-agro.card>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <flux:icon icon="finger-print" class="size-5 text-agro-600" />
            <div>
                <p class="font-bold text-zinc-900">{{ __('Certificado de firma VeriFactu') }}</p>
                <p class="text-sm text-zinc-500">{{ __('Firma tus propias facturas ante la AEAT con tu certificado digital') }}</p>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-4">
        @if($hasVerifactuCertificate)
            <div @class([
                'flex items-center justify-between p-4 rounded-xl border',
                'bg-red-50 border-red-200' => $verifactuCertificateExpired,
                'bg-amber-50 border-amber-200' => ! $verifactuCertificateExpired && $verifactuCertificateExpiringSoon,
                'bg-green-50 border-green-200' => ! $verifactuCertificateExpired && ! $verifactuCertificateExpiringSoon,
            ])>
                <div>
                    <p @class([
                        'text-sm font-semibold',
                        'text-red-800' => $verifactuCertificateExpired,
                        'text-amber-800' => ! $verifactuCertificateExpired && $verifactuCertificateExpiringSoon,
                        'text-green-800' => ! $verifactuCertificateExpired && ! $verifactuCertificateExpiringSoon,
                    ])>
                        @if($verifactuCertificateExpired)
                            {{ __('Certificado caducado') }}
                        @elseif($verifactuCertificateExpiringSoon)
                            {{ __('Certificado próximo a caducar') }}
                        @else
                            {{ __('Certificado activo') }}
                        @endif
                    </p>
                    <p class="text-xs mt-0.5
                        @if($verifactuCertificateExpired) text-red-600
                        @elseif($verifactuCertificateExpiringSoon) text-amber-600
                        @else text-green-600 @endif">
                        {{ __('Caduca el :date', ['date' => $verifactuCertificateExpiresAt]) }}
                    </p>
                </div>
                <flux:button
                    wire:click="removeVerifactuCertificate"
                    wire:confirm="{{ __('¿Eliminar el certificado? No podrás enviar facturas a la AEAT hasta que subas uno nuevo.') }}"
                    variant="ghost" size="sm" icon="trash"
                >
                    {{ __('Eliminar') }}
                </flux:button>
            </div>
        @else
            <div class="flex items-center gap-2 p-4 bg-zinc-50 border border-zinc-200 rounded-xl text-sm text-zinc-600">
                {{ __('Sin certificado configurado. Sin él no podrás enviar facturas a la AEAT en producción.') }}
            </div>
        @endif

        <form wire:submit="uploadVerifactuCertificate" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>{{ __('Archivo del certificado (.p12 / .pfx)') }}</flux:label>
                <input type="file" wire:model="verifactuCertificateFile" accept=".p12,.pfx"
                    class="block w-full text-sm text-zinc-600 border border-zinc-200 rounded-lg file:mr-3 file:py-2 file:px-3 file:border-0 file:bg-zinc-100 file:text-zinc-700 file:rounded-lg" />
                <flux:error name="verifactuCertificateFile" />
                <div wire:loading wire:target="verifactuCertificateFile" class="text-xs text-zinc-400 mt-1">{{ __('Subiendo…') }}</div>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Contraseña del certificado') }}</flux:label>
                <flux:input type="password" wire:model="verifactuCertificatePassword" viewable />
                <flux:error name="verifactuCertificatePassword" />
                <p class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1">
                    <flux:icon icon="lock-closed" class="size-3 shrink-0" />
                    {{ __('Se guarda cifrada. Nunca se muestra en claro.') }}
                </p>
            </flux:field>

            <div class="md:col-span-2 flex justify-end">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="uploadVerifactuCertificate">
                    {{ $hasVerifactuCertificate ? __('Reemplazar certificado') : __('Subir certificado') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-agro.card>
