{{-- Sidebar Item Component --}}
@if(isset($item['divider']) && $item['divider'])
    <div class="mx-2 my-1.5 border-t border-white/10"></div>
@else
<div class="mb-0.5">
    <a
        href="{{ route($item['route']) }}"
        wire:navigate
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
               {{ $item['active']
                   ? 'bg-white/15 text-white shadow-sm'
                   : 'text-white/60 hover:bg-white/8 hover:text-white'
               }}"
        title="{{ $item['label'] }}"
        data-cy="sidebar-nav-{{ strtolower(str_replace(' ', '-', $item['label'])) }}"
    >
        <span class="flex-shrink-0 w-5 flex items-center justify-center {{ $item['active'] ? 'text-white' : 'text-white/50 group-hover:text-white/80' }} transition-colors duration-200">
            @if(isset($item['icon']))
                <flux:icon icon="{{ $item['icon'] }}" class="w-5 h-5" />
            @else
                {!! $item['icon_svg'] !!}
            @endif
        </span>
        <span class="font-medium text-sm flex-1 sidebar-text whitespace-nowrap overflow-hidden leading-tight">{{ $item['label'] }}</span>
        @if(isset($item['badge']) && $item['badge'] > 0)
            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full sidebar-indicator">
                {{ $item['badge'] }}
            </span>
        @elseif(isset($item['wip']) && $item['wip'])
            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-400/20 text-amber-300 rounded-full sidebar-indicator whitespace-nowrap">Pronto</span>
        @elseif($item['active'])
            <div class="w-1.5 h-1.5 rounded-full bg-white/60 sidebar-indicator flex-shrink-0"></div>
        @endif
    </a>
    
    @if(isset($item['submenu']) && $item['active'])
        <div class="ml-8 mt-1 space-y-0.5 sidebar-submenu" data-cy="sidebar-submenu-{{ strtolower(str_replace(' ', '-', $item['label'])) }}">
            @foreach($item['submenu'] as $subitem)
                @php
                    $canShow = true;
                    if (str_contains($subitem['route'], 'digital-notebook.treatment.create') || 
                        str_contains($subitem['route'], 'digital-notebook.fertilization.create') ||
                        str_contains($subitem['route'], 'digital-notebook.irrigation.create') ||
                        str_contains($subitem['route'], 'digital-notebook.cultural.create') ||
                        str_contains($subitem['route'], 'digital-notebook.observation.create')) {
                        $canShow = auth()->user()->can('create', \App\Models\AgriculturalActivity::class);
                    } elseif (str_contains($subitem['route'], 'digital-notebook')) {
                        $canShow = auth()->user()->can('viewAny', \App\Models\AgriculturalActivity::class);
                    }
                @endphp
                @if($canShow)
                    @php
                        $routeUrl = route($subitem['route']);
                        if (isset($subitem['query'])) {
                            $routeUrl .= '?' . http_build_query($subitem['query']);
                        }
                    @endphp
                    <a
                        href="{{ $routeUrl }}"
                        wire:navigate
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all duration-200
                               {{ $subitem['active']
                                   ? 'bg-white/12 text-white font-semibold'
                                   : 'text-white/50 hover:bg-white/8 hover:text-white/80'
                               }}"
                        data-cy="sidebar-submenu-item-{{ strtolower(str_replace(' ', '-', $subitem['label'])) }}"
                    >
                        <span class="w-1 h-1 rounded-full {{ $subitem['active'] ? 'bg-white' : 'bg-white/30' }} flex-shrink-0"></span>
                        {{ $subitem['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endif
