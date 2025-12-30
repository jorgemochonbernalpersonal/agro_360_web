{{-- ✅ SEO: Componente reutilizable para enlaces relacionados --}}
@php
    $relatedLinks = \App\Helpers\SeoHelper::getRelatedLinks(request()->path());
@endphp

@if(count($relatedLinks) > 0)
<section class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-6">Artículos Relacionados</h2>
        <div class="grid md:grid-cols-2 gap-4">
            @foreach($relatedLinks as $link)
            <a href="{{ $link['url'] }}" class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200">
                <h3 class="font-semibold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ $link['title'] }}</h3>
                <p class="text-gray-600 text-sm">{{ $link['description'] }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

