@extends('layouts.tools')

@section('title', 'Alertas de mildiu y oídio gratis por municipio')
@section('description', 'Consulta el riesgo de mildiu y oídio de la vid en tu municipio para los próximos días y recibe un aviso gratuito por email cuando sea alto.')
@section('breadcrumb', $tool['title'])

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">Alertas de mildiu y oídio</h1>
        <p class="text-lg text-gray-600 max-w-3xl">Mira el riesgo de los próximos días en tu municipio y apúntate para recibir un email cuando toque vigilar la viña.</p>
    </div>

    <livewire:tools.disease-alerts />

    <article class="prose max-w-none mt-14">
        <h2>Cómo calculamos el riesgo</h2>
        <h3>Mildiu</h3>
        <p>Usamos la clásica <strong>regla de los tres dieces</strong>: la infección primaria es posible cuando caen al menos 10 mm de lluvia en 48 horas, la temperatura media supera los 10 °C y los brotes miden más de 10 cm. Marcamos riesgo moderado cuando hay lluvia o muchas horas de humedad alta con temperaturas de 10 a 30 °C, condiciones que favorecen las infecciones secundarias.</p>
        <h3>Oídio</h3>
        <p>Calculamos el <strong>índice de Gubler-Thomas</strong>, desarrollado por la Universidad de California. Cuenta los días con al menos 6 horas seguidas entre 21 y 30 °C: tras tres días seguidos la epidemia se considera activa (índice 60) y el índice sube o baja cada día. De 0 a 30 el riesgo es bajo, de 40 a 50 moderado y a partir de 60 alto.</p>
        <p>Son modelos orientativos basados en la meteo del municipio, no de tu parcela concreta. Confirma siempre en campo y sigue las indicaciones de la etiqueta y de tu técnico antes de tratar.</p>
    </article>

    @include('tools.partials.related')
@endsection
