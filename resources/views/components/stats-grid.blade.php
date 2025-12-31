@props(['stats' => []])

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    @foreach($stats as $stat)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-{{ $stat['color'] }}-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $stat['icon'] !!}
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
