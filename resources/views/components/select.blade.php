@props(['error' => null, 'label' => null, 'hint' => null])

@php
    $baseClasses = 'w-full px-4 py-3 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 appearance-none bg-white cursor-pointer';
    $normalClasses = 'border-gray-300 text-gray-900 focus:border-[var(--color-agro-green-dark)] focus:ring-[var(--color-agro-green-dark)]/20';
    $errorClasses = 'border-red-400 bg-red-50 text-red-900 focus:border-red-500 focus:ring-red-500/20';
    $classes = $baseClasses . ' ' . ($error ? $errorClasses : $normalClasses);
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $attributes->get('id') }}" class="block text-sm font-semibold text-gray-700">
            {{ $label }}
            @if($attributes->has('required'))
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <select {{ $attributes->merge(['class' => $classes]) }}>
            {{ $slot }}
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
    
    @if($hint && !$error)
        <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
    @endif
    
    @if($error)
        <div class="flex items-center gap-1 mt-1">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm text-red-600 font-medium">{{ $error }}</p>
        </div>
    @endif
</div>

