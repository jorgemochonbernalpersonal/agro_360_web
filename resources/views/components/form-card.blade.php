@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]',
    'backUrl' => null,
])

<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($title)
        <x-page-header
            :icon="$icon"
            :title="$title"
            :description="$description"
            :icon-color="$iconColor"
        >
            @if($backUrl)
                <x-slot:actionButton>
                    <x-button href="{{ $backUrl }}" variant="secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </x-button>
                </x-slot:actionButton>
            @endif
        </x-page-header>
    @endif

    <!-- Formulario -->
    <div class="glass-card rounded-2xl p-8">
        {{ $slot }}
    </div>
</div>

