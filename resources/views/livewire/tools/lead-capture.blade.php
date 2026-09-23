<div class="rounded-2xl border border-[var(--color-agro-green)]/30 bg-[var(--color-agro-green)]/5 p-6">
    @if ($sent)
        <p class="text-lg font-semibold text-[var(--color-agro-green-dark)]">¡Gracias! Te escribiremos pronto.</p>
    @else
        <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-gray-600 mb-4">{{ $subtitle }}</p>
        @endif

        <form wire:submit="save" class="space-y-4">
            <div class="grid sm:grid-cols-3 gap-3">
                <div>
                    <label for="lead-email-{{ $tool }}" class="sr-only">Email</label>
                    <input id="lead-email-{{ $tool }}" type="email" wire:model="email" placeholder="tu@email.com" autocomplete="email" required class="block w-full rounded-lg border-gray-300 shadow-sm">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="lead-name-{{ $tool }}" class="sr-only">Nombre</label>
                    <input id="lead-name-{{ $tool }}" type="text" wire:model="name" placeholder="Nombre (opcional)" autocomplete="name" class="block w-full rounded-lg border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="lead-profile-{{ $tool }}" class="sr-only">Perfil</label>
                    <select id="lead-profile-{{ $tool }}" wire:model="profile" class="block w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="">Soy… (opcional)</option>
                        @foreach ($profiles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Honeypot anti-bots: invisible para personas --}}
            <div class="hidden" aria-hidden="true">
                <label>Web <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <label class="flex items-start gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="consent" class="mt-1 rounded border-gray-300">
                <span>Acepto que Agro365 me escriba sobre este tema. He leído la <a href="{{ route('privacy') }}" class="underline" target="_blank">política de privacidad</a>.</span>
            </label>
            @error('consent') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

            <button type="submit" class="px-6 py-3 rounded-lg bg-[var(--color-agro-green-dark)] text-white font-semibold">Enviar</button>
        </form>
    @endif
</div>
