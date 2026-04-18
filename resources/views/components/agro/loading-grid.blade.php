@props(['cols' => 4, 'count' => 8, 'target'])

<div
    wire:loading
    wire:target="{{ $target }}"
>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 {{ $cols >= 4 ? 'xl:grid-cols-4' : '' }} gap-6">
        @for($i = 0; $i < $count; $i++)
            <x-agro.skeleton-card />
        @endfor
    </div>
</div>
