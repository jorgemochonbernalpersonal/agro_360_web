@extends('layouts.tools')

@section('title', 'Test: ¿estás listo para el cuaderno digital 2027?')
@section('description', 'Test gratuito de 8 preguntas: descubre si cumples con el cuaderno de explotación digital obligatorio y qué te falta antes de 2027.')
@section('breadcrumb', 'Test cuaderno digital 2027')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Estás listo para el cuaderno digital 2027?</h1>
        <p class="text-lg text-gray-600 max-w-3xl">8 preguntas de sí o no. En un minuto sabrás qué te falta para cumplir y por dónde empezar.</p>
    </div>

    <livewire:tools.notebook-readiness-check />

    <article class="prose max-w-none mt-14">
        <h2>Qué cambia con el cuaderno digital</h2>
        <p>El cuaderno de explotación ya es obligatorio. Desde el 1 de enero de 2026 debe registrarse la fertilización y, desde el 1 de enero de 2027, los tratamientos fitosanitarios en formato electrónico interoperable (RD 1054/2022, modificado por el RD 34/2025).</p>
        <p>Tienes toda la explicación en nuestra <a href="{{ content_route('content.cuaderno-campo-digital-2027') }}">guía del cuaderno de campo digital 2027</a>.</p>
    </article>

    @include('tools.partials.related')
@endsection
