@props(['icon' => null])

<div class="relative">
    @if($icon)
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            {!! $icon !!}
        </div>
    @endif
    <x-select {{ $attributes->merge(['class' => $icon ? 'pl-12 pr-10' : 'pr-10']) }}>
        {{ $slot }}
    </x-select>
    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</div>

