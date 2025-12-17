@props(['status' => null, 'active' => true, 'label' => null, 'type' => 'default'])

@php
    if($status !== null) {
        $active = $status;
    }
    
    $label = $label ?? ($active ? 'Activa' : 'Inactiva');
    
    if($type === 'default') {
        $classes = $active 
            ? 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 ring-1 ring-green-600/20' 
            : 'bg-gradient-to-r from-gray-50 to-slate-50 text-gray-600 ring-1 ring-gray-400/20';
    } else {
        $typeClasses = [
            'success' => 'bg-green-50 text-green-700 ring-1 ring-green-600/20',
            'warning' => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
            'danger' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            'info' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            'gray' => 'bg-gray-50 text-gray-700 ring-1 ring-gray-400/20',
        ];
        $classes = $typeClasses[$type] ?? $typeClasses['gray'];
    }
@endphp

<span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg {{ $classes }}">
    @if($active && $type === 'default')
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
    @elseif(!$active && $type === 'default')
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
    @endif
    {{ $label }}
</span>

