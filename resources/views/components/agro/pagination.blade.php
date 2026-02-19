@props(['paginator'])

@if($paginator->hasPages())
    <nav class="flex items-center justify-between">
        <flux:subheading>
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </flux:subheading>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($paginator->onFirstPage())
                <flux:button variant="ghost" size="sm" icon="chevron-left" disabled />
            @else
                <flux:button variant="ghost" size="sm" icon="chevron-left" wire:click="previousPage" />
            @endif

            {{-- Page Numbers --}}
            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if($page == $paginator->currentPage())
                    <flux:button variant="primary" size="sm">{{ $page }}</flux:button>
                @else
                    <flux:button variant="ghost" size="sm" wire:click="gotoPage({{ $page }})">{{ $page }}</flux:button>
                @endif
            @endforeach

            {{-- Next --}}
            @if(!$paginator->hasMorePages())
                <flux:button variant="ghost" size="sm" icon="chevron-right" disabled />
            @else
                <flux:button variant="ghost" size="sm" icon="chevron-right" wire:click="nextPage" />
            @endif
        </div>
    </nav>
@endif
