<div>
@if($announcements->isNotEmpty())
    <div class="space-y-2">
        @foreach($announcements as $a)
            @php
                $tone = match($a->type) {
                    'action_required' => 'warning',
                    'harvest_alert'   => 'danger',
                    default           => 'info',
                };
            @endphp
            <x-agro.alert-banner
                :tone="$tone"
                :icon="$a->typeIcon()"
                :title="$a->title"
            >
                {{ $a->body }}
                <span class="block mt-1 text-[10px] opacity-70">
                    {{ $a->winery->name }} · {{ $a->published_at->diffForHumans() }}
                </span>
            </x-agro.alert-banner>
        @endforeach

        @if($announcements->count() >= 3)
            <a href="{{ roleRoute('viticulturist.announcements') }}" wire:navigate
               class="text-xs text-agro-600 hover:underline font-medium">
                {{ __('Ver todos los avisos →') }}
            </a>
        @endif
    </div>
@endif
</div>
