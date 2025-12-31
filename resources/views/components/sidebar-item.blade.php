{{-- Sidebar Item Component --}}
<div class="mb-1">
    <a 
        href="{{ route($item['route']) }}" 
        class="flex items-center space-x-4 px-4 py-3 rounded-xl transition-all duration-200 group
               {{ $item['active'] 
                   ? 'bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white shadow-lg shadow-[var(--color-agro-green)]/30' 
                   : 'text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)]' 
               }}"
        title="{{ $item['label'] }}"
        data-cy="sidebar-nav-{{ strtolower(str_replace(' ', '-', $item['label'])) }}"
    >
        <span class="flex-shrink-0 w-8 text-center">
            {!! $item['icon_svg'] ?? $item['icon'] !!}
        </span>
        <span class="font-semibold flex-1 sidebar-text whitespace-nowrap overflow-hidden">{{ $item['label'] }}</span>
        @if(isset($item['badge']) && $item['badge'] > 0)
            <span class="px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full sidebar-indicator">
                {{ $item['badge'] }}
            </span>
        @elseif($item['active'])
            <div class="w-2 h-2 rounded-full bg-white sidebar-indicator"></div>
        @endif
    </a>
    
    @if(isset($item['submenu']) && $item['active'])
        <div class="ml-12 mt-2 space-y-1 sidebar-submenu" data-cy="sidebar-submenu-{{ strtolower(str_replace(' ', '-', $item['label'])) }}">
            @foreach($item['submenu'] as $subitem)
                @php
                    // Determinar qué policy usar según la ruta
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
                        // Agregar query string si existe en el item
                        $routeUrl = route($subitem['route']);
                        if (isset($subitem['query'])) {
                            $routeUrl .= '?' . http_build_query($subitem['query']);
                        }
                    @endphp
                    <a 
                        href="{{ $routeUrl }}" 
                        class="flex items-center px-4 py-2 rounded-lg text-sm transition-all duration-200
                               {{ $subitem['active'] 
                                   ? 'bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] font-medium border-l-4 border-[var(--color-agro-green-dark)]' 
                                   : 'text-gray-600 hover:bg-gray-100 hover:text-[var(--color-agro-green-dark)]' 
                               }}"
                        data-cy="sidebar-submenu-item-{{ strtolower(str_replace(' ', '-', $subitem['label'])) }}"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-3 opacity-50"></span>
                        {{ $subitem['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
