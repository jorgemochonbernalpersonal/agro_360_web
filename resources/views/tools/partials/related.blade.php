{{-- Enlaces al resto de herramientas + CTA a la app. Espera $related (slug => tool). --}}
<section class="mt-14">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Otras herramientas gratis</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($related as $relatedSlug => $relatedTool)
            <a href="{{ route('tools.show', $relatedSlug) }}" class="flex gap-4 bg-white rounded-xl border border-gray-200 p-5 hover:border-[var(--color-agro-green)] transition">
                <div class="text-2xl" aria-hidden="true">{{ $relatedTool['icon'] }}</div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $relatedTool['title'] }}</h3>
                    <p class="text-sm text-gray-600">{{ $relatedTool['summary'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="mt-10 rounded-2xl bg-[var(--color-agro-green-dark)] text-white p-8">
    <h2 class="text-2xl font-bold mb-2">Anótalo directamente en tu cuaderno digital</h2>
    <p class="text-white/80 mb-5">Con Agro365 registras tratamientos, fertilizaciones y vendimia desde el móvil, listo para la normativa 2027.</p>
    <a href="{{ content_route('content.cuaderno-campo-digital-2027') }}" class="inline-block px-5 py-3 rounded-lg bg-white text-[var(--color-agro-green-dark)] font-semibold">Ver cómo funciona</a>
</section>
