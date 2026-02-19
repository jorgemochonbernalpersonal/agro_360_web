@props(['title' => null, 'description' => null, 'backUrl' => null])

<div class="space-y-6 animate-fade-in">
    @if($title)
        <x-agro.page-header :title="$title" :description="$description">
            @if($backUrl)
                <x-slot:actions>
                    <flux:button href="{{ $backUrl }}" variant="outline" icon="arrow-left">
                        Volver
                    </flux:button>
                </x-slot:actions>
            @endif
        </x-agro.page-header>
    @endif

    <x-agro.card>
        {{ $slot }}
    </x-agro.card>
</div>
