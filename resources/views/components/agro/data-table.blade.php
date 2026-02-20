@props(['headers', 'emptyMessage' => 'No hay registros', 'emptyDescription' => null, 'emptyIcon' => null])

<x-agro.card :padding="false">
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        @foreach($headers as $header)
                            <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">
                                @if(is_array($header))
                                    <div class="flex items-center gap-2">
                                        @if(isset($header['icon']))
                                            {!! $header['icon'] !!}
                                        @endif
                                        {{ $header['label'] }}
                                    </div>
                                @else
                                    {{ $header }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-100">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @if(isset($pagination))
            <div class="px-6 py-4 border-t border-zinc-200">
                {{ $pagination }}
            </div>
        @endif
    @else
        <x-agro.empty-state
            :message="$emptyMessage"
            :description="$emptyDescription"
            :icon="$emptyIcon"
        >
            @if(isset($emptyAction))
                <x-slot name="action">
                    {{ $emptyAction }}
                </x-slot>
            @endif
        </x-agro.empty-state>
    @endif
</x-agro.card>
