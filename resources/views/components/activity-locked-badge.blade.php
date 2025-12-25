@props(['activity'])

@if($activity->is_locked)
    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-300">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
        </svg>
        <span>Bloqueada</span>
        @if($activity->locked_at)
            <span class="text-gray-500" title="Bloqueada el {{ $activity->locked_at->format('d/m/Y H:i') }}">
                ({{ $activity->locked_at->diffForHumans() }})
            </span>
        @endif
    </div>
@endif
