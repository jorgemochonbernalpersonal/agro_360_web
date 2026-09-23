<div>
    @if (! $this->result)
        <form wire:submit="submit" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            @foreach ($questions as $key => $question)
                <fieldset wire:key="q-{{ $key }}" class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <legend class="sr-only">Pregunta {{ $loop->iteration }}</legend>
                    <p class="font-medium text-gray-900 mb-3">{{ $loop->iteration }}. {{ $question['question'] }}</p>
                    <div class="flex gap-3">
                        @foreach ([true => 'Sí', false => 'No'] as $value => $label)
                            <label @class([
                                'cursor-pointer px-5 py-2 rounded-lg border text-sm font-semibold transition',
                                'bg-[var(--color-agro-green-dark)] text-white border-transparent' => $answers[$key] === (bool) $value,
                                'bg-white text-gray-700 border-gray-300 hover:border-gray-400' => $answers[$key] !== (bool) $value,
                            ])>
                                <input type="radio" class="sr-only" name="answer-{{ $key }}" wire:click="$set('answers.{{ $key }}', {{ $value ? 'true' : 'false' }})">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            @error('answers') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

            <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white font-semibold">Ver mi resultado</button>
        </form>
    @else
        @php $r = $this->result; @endphp
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6" aria-live="polite">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6 mb-6">
                <div @class([
                    'shrink-0 w-28 h-28 rounded-full flex items-center justify-center text-3xl font-bold text-white',
                    'bg-green-600' => $r['level'] === 'ready',
                    'bg-amber-500' => $r['level'] === 'partial',
                    'bg-red-600' => $r['level'] === 'risk',
                ])>{{ $r['score'] }}%</div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $r['label'] }}</h2>
                    <p class="text-gray-600">Cumples {{ count($questions) - count($r['pending']) }} de {{ count($questions) }} puntos clave.</p>
                </div>
            </div>

            @if ($r['pending'])
                <h3 class="font-bold text-gray-900 mb-3">Lo que te falta</h3>
                <ul class="space-y-3 mb-6">
                    @foreach ($r['pending'] as $item)
                        <li class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="font-medium text-gray-900">{{ $item['question'] }}</p>
                            <p class="text-sm text-gray-700 mt-1">{{ $item['tip'] }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mb-6 text-gray-700">Tienes todo lo básico en orden. Revisa que tu aplicación exporte el cuaderno en formato interoperable antes de 2027.</p>
            @endif

            <button type="button" wire:click="restart" class="text-sm font-semibold text-[var(--color-agro-green-dark)] hover:underline">Repetir el test</button>
        </div>

        <div class="mt-8">
            <livewire:tools.lead-capture
                :key="'lead-'.$r['score']"
                tool="test-cuaderno-digital-2027"
                :context="['score' => $r['score'], 'answers' => $answers]"
                title="¿Quieres ayuda para llegar a 2027?"
                subtitle="Déjanos tu email y te contamos cómo dejar el cuaderno en regla antes de la fecha. Sin compromiso."
            />
        </div>
    @endif
</div>
