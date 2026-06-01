<div class="space-y-6 animate-fade-in">

    <x-agro.page-header :title="__('Avisos de Bodegas')" :description="__('Comunicaciones de las bodegas con las que colaboras')" />

    @if($announcements->count() > 0)
        <div class="space-y-3">
            @foreach($announcements as $a)
                <x-agro.card wire:key="ann-{{ $a->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-{{ $a->typeColor() }}-50 flex items-center justify-center shrink-0 mt-0.5">
                            <flux:icon :icon="$a->typeIcon()" class="size-5 text-{{ $a->typeColor() }}-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-zinc-900">{{ $a->title }}</h3>
                                <flux:badge :color="$a->typeColor()" size="sm">{{ $a->typeLabel() }}</flux:badge>
                            </div>
                            <p class="text-sm text-zinc-600 whitespace-pre-line">{{ $a->body }}</p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-xs text-zinc-400">
                                    <flux:icon icon="building-storefront" class="size-3 inline" />
                                    {{ $a->winery->name }}
                                </span>
                                <span class="text-xs text-zinc-400">{{ $a->published_at->diffForHumans() }}</span>
                                @if($a->expires_at)
                                    <span class="text-xs text-zinc-400">{{ __('Expira') }} {{ $a->expires_at->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>
        <x-agro.pagination :paginator="$announcements" />
    @else
        <x-agro.empty-state
            icon="megaphone"
            :message="__('Sin avisos')"
            :description="__('No hay avisos activos de tus bodegas vinculadas')"
        />
    @endif

</div>
