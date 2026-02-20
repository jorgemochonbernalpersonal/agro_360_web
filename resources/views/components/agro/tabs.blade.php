@props(['tabs', 'active', 'wireMethod' => 'switchTab'])

<div class="border-b border-zinc-200 mb-6">
    <nav class="flex gap-1 -mb-px">
        @foreach($tabs as $key => $tab)
            @php
                $label = is_array($tab) ? ($tab['label'] ?? $key) : $tab;
                $count = is_array($tab) ? ($tab['count'] ?? null) : null;
                $isActive = $active === $key;
            @endphp
            <button
                wire:click="{{ $wireMethod }}('{{ $key }}')"
                @class([
                    'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
                    'border-agro-500 text-agro-700' => $isActive,
                    'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' => !$isActive,
                ])
            >
                {{ $label }}
                @if($count !== null)
                    <span @class([
                        'ml-1.5 text-xs',
                        'text-agro-600' => $isActive,
                        'text-zinc-400' => !$isActive,
                    ])>({{ $count }})</span>
                @endif
            </button>
        @endforeach
    </nav>
</div>
