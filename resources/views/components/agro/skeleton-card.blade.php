{{-- Skeleton loader que imita la estructura de una plot card (Executive Dashboard style) --}}
<div class="bg-white rounded-2xl shadow-xl overflow-hidden">

    {{-- Skeleton header (imita gradiente agro + icono + título + badge) --}}
    <div class="px-6 py-4 bg-zinc-50 border-b border-zinc-200">
        <div class="flex items-center gap-3">
            <div class="skeleton w-8 h-8 rounded-full shrink-0"></div>
            <div class="flex-1 space-y-1.5 min-w-0">
                <div class="skeleton h-4 w-40 rounded"></div>
                <div class="skeleton h-3 w-28 rounded"></div>
            </div>
            <div class="skeleton h-5 w-14 rounded-full shrink-0"></div>
        </div>
    </div>

    {{-- Skeleton body (imita 3 filas de métricas + progress bar) --}}
    <div class="px-6 py-5 space-y-3">
        <div class="flex items-center gap-2">
            <div class="skeleton w-4 h-4 rounded shrink-0"></div>
            <div class="skeleton h-3 w-36 rounded"></div>
        </div>
        <div class="flex items-center gap-2">
            <div class="skeleton w-4 h-4 rounded shrink-0"></div>
            <div class="skeleton h-3 w-28 rounded"></div>
        </div>
        <div class="flex items-center gap-2">
            <div class="skeleton w-4 h-4 rounded shrink-0"></div>
            <div class="skeleton h-3 w-20 rounded"></div>
        </div>

        {{-- Progress bar skeleton --}}
        <div class="pt-2 space-y-1.5">
            <div class="flex justify-between">
                <div class="skeleton h-3 w-10 rounded"></div>
                <div class="skeleton h-3 w-24 rounded"></div>
            </div>
            <div class="skeleton h-2 w-full rounded-full"></div>
        </div>
    </div>

    {{-- Skeleton footer (imita fila de botones de acción) --}}
    <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
        <div class="flex gap-2 justify-center">
            @for($i = 0; $i < 5; $i++)
                <div class="skeleton w-7 h-7 rounded-lg"></div>
            @endfor
        </div>
    </div>

</div>
