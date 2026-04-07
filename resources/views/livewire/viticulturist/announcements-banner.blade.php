<div>
@if($announcements->isNotEmpty())
    <div class="space-y-2">
        @foreach($announcements as $a)
            <div class="flex items-start gap-3 p-3 rounded-xl border
                {{ $a->type === 'action_required' ? 'bg-amber-50 border-amber-200' : ($a->type === 'harvest_alert' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200') }}">
                <flux:icon :icon="$a->typeIcon()" class="size-5 mt-0.5 shrink-0
                    {{ $a->type === 'action_required' ? 'text-amber-600' : ($a->type === 'harvest_alert' ? 'text-red-600' : 'text-blue-600') }}" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold {{ $a->type === 'action_required' ? 'text-amber-900' : ($a->type === 'harvest_alert' ? 'text-red-900' : 'text-blue-900') }}">
                        {{ $a->title }}
                    </p>
                    <p class="text-xs {{ $a->type === 'action_required' ? 'text-amber-700' : ($a->type === 'harvest_alert' ? 'text-red-700' : 'text-blue-700') }} mt-0.5 line-clamp-2">
                        {{ $a->body }}
                    </p>
                    <p class="text-[10px] {{ $a->type === 'action_required' ? 'text-amber-500' : ($a->type === 'harvest_alert' ? 'text-red-500' : 'text-blue-500') }} mt-1">
                        {{ $a->winery->name }} · {{ $a->published_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        @endforeach
        @if($announcements->count() >= 3)
            <a href="{{ roleRoute('viticulturist.announcements') }}" wire:navigate class="text-xs text-agro-600 hover:underline font-medium">
                Ver todos los avisos →
            </a>
        @endif
    </div>
@endif
</div>
