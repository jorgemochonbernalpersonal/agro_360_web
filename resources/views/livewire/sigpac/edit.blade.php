<div class="space-y-6">
    <x-agro.page-header
        title="{{ __('Editar Código SIGPAC') }}"
        :description="__('Edita el código de identificación SIGPAC. Completa cada campo según el formato SIGPAC.')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('sigpac.codes') }}" variant="outline" icon="arrow-left" data-cy="back-button">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit.prevent="update" class="space-y-8">
            <!-- Parcela OBLIGATORIA -->
            <x-agro.form-section title="{{ __('Parcela') }}">
                <flux:field>
                    <flux:label for="plot_id">{{ __('Parcela *') }}</flux:label>
                    <flux:select
                        wire:model.live="plot_id"
                        id="plot_id"
                        required
                    >
                        <option value="">{{ __('Seleccionar parcela') }}</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">
                                {{ $plot->name }}
                                @if($plot->municipality)
                                    - {{ $plot->municipality->name }}
                                @endif
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>
            </x-agro.form-section>

            <!-- Usos SIGPAC de la parcela -->
            <x-agro.form-section title="{{ __('Usos SIGPAC') }}">
                <flux:field>
                    <flux:label for="sigpac_use">{{ __('Usos de la Parcela') }}</flux:label>
                    <flux:select wire:model="sigpac_use" id="sigpac_use" multiple size="5">
                        @foreach ($sigpacUses as $use)
                            <option value="{{ $use->id }}">{{ $use->code }} - {{ $use->description }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('Mantén pulsado Ctrl (o Cmd en Mac) para seleccionar varios usos.') }}</flux:description>
                    <flux:error name="sigpac_use" />
                </flux:field>
            </x-agro.form-section>

            <!-- Código SIGPAC con cajita por campo -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-zinc-800">{{ __('Código SIGPAC') }}</h2>
                </div>

                @foreach ($sigpacCodes as $index => $sigpac)
                    @php
                        $isValid = $this->isCodeValid($index);
                        $hasDuplicate = $this->hasDuplicate($index);
                        $fullCode = $this->getFullCode($index);
                        $hasErrors = $errors->has("sigpacCodes.{$index}.*");
                        $formattedCode = strlen($fullCode) === 24
                            ? sprintf('%s-%s-%s-%s-%s-%s-%s-%s',
                                substr($fullCode, 0, 2),
                                substr($fullCode, 2, 2),
                                substr($fullCode, 4, 3),
                                substr($fullCode, 7, 3),
                                substr($fullCode, 10, 3),
                                substr($fullCode, 13, 3),
                                substr($fullCode, 16, 5),
                                substr($fullCode, 21, 3))
                            : '';
                    @endphp

                    <div
                        x-data="{
                            parseError: '',
                            pasteRef: '',
                            padField(el) {
                                const len = parseInt(el.maxLength);
                                if (el.value.length > 0 && el.value.length < len) {
                                    el.value = el.value.padStart(len, '0');
                                    el.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            },
                            advance(el) {
                                if (el.value.length >= parseInt(el.maxLength)) {
                                    const inputs = el.closest('.sigpac-fields').querySelectorAll('input[maxlength]');
                                    const idx = Array.from(inputs).indexOf(el);
                                    if (idx < inputs.length - 1) inputs[idx + 1].focus();
                                }
                            },
                            parsePaste(index) {
                                this.parseError = '';
                                const raw = this.pasteRef.replace(/\s/g, '');
                                const parts = raw.split('-').filter(p => p !== '');
                                let fields = {};
                                if (parts.length === 7) {
                                    fields = {
                                        code_province:             parts[0].padStart(2,'0'),
                                        code_municipality:         parts[1].padStart(3,'0'),
                                        code_aggregate:            parts[2].padStart(3,'0'),
                                        code_zone:                 parts[3].padStart(3,'0'),
                                        code_polygon:              parts[4].padStart(3,'0'),
                                        code_plot:                 parts[5].padStart(5,'0'),
                                        code_enclosure:            parts[6].padStart(3,'0'),
                                    };
                                } else if (parts.length === 8) {
                                    fields = {
                                        code_autonomous_community: parts[0].padStart(2,'0'),
                                        code_province:             parts[1].padStart(2,'0'),
                                        code_municipality:         parts[2].padStart(3,'0'),
                                        code_aggregate:            parts[3].padStart(3,'0'),
                                        code_zone:                 parts[4].padStart(3,'0'),
                                        code_polygon:              parts[5].padStart(3,'0'),
                                        code_plot:                 parts[6].padStart(5,'0'),
                                        code_enclosure:            parts[7].padStart(3,'0'),
                                    };
                                } else {
                                    this.parseError = 'Formato no reconocido. Usa PP-MMM-AAA-ZZZ-PPP-PPPPP-EEE (7 segmentos) o CA-PP-MMM-AAA-ZZZ-PPP-PPPPP-EEE (8 segmentos).';
                                    return;
                                }
                                $wire.fillSigpacCode(index, fields);
                                this.pasteRef = '';
                            }
                        }"
                        class="bg-white rounded-2xl shadow-lg border-2 transition-all duration-300
                            @if($isValid && !$hasDuplicate) border-green-400 shadow-green-100
                            @elseif($hasErrors || $hasDuplicate) border-red-400 shadow-red-100
                            @else border-zinc-200 hover:border-agro-500 hover:shadow-xl @endif"
                    >
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between
                            @if($isValid && !$hasDuplicate) bg-green-50 @elseif($hasErrors || $hasDuplicate) bg-red-50 @else bg-zinc-50 @endif">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm
                                    @if($isValid && !$hasDuplicate) bg-green-500 text-white
                                    @elseif($hasErrors || $hasDuplicate) bg-red-500 text-white
                                    @else bg-zinc-300 text-zinc-600 @endif">1</div>
                                <div>
                                    <h3 class="text-lg font-semibold text-zinc-800">{{ __('Código SIGPAC') }}</h3>
                                    @if($isValid && !$hasDuplicate)
                                        <p class="text-xs text-green-700 font-medium flex items-center gap-1 mt-0.5">
                                            <flux:icon icon="check-circle" class="size-4" /> Código válido: <span class="font-mono">{{ $formattedCode }}</span>
                                        </p>
                                    @elseif($hasDuplicate)
                                        <p class="text-xs text-red-700 font-medium flex items-center gap-1 mt-0.5">
                                            <flux:icon icon="x-circle" class="size-4" /> Duplicado detectado
                                        </p>
                                    @elseif($hasErrors)
                                        <p class="text-xs text-red-700 font-medium flex items-center gap-1 mt-0.5">
                                            <flux:icon icon="x-circle" class="size-4" /> Revisa los campos
                                        </p>
                                    @else
                                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Completa los campos o pega la referencia SIGPAC') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Pegar referencia completa -->
                        <div class="px-6 pt-5 pb-0">
                            <div class="flex gap-2 items-end">
                                <div class="flex-1">
                                    <label class="text-xs font-medium text-zinc-500 mb-1 block">
                                        Pegar referencia SIGPAC completa
                                        <span class="text-zinc-400 font-normal">{{ __('(ej: 28-079-000-000-012-00045-003)') }}</span>
                                    </label>
                                    <input
                                        type="text"
                                        x-model="pasteRef"
                                        x-on:keydown.enter.prevent="parsePaste({{ $index }})"
                                        placeholder="{{ __('PP-MMM-AAA-ZZZ-PPP-PPPPP-EEE') }}"
                                        class="w-full font-mono text-sm border border-zinc-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-agro-500"
                                    >
                                </div>
                                <flux:button type="button" x-on:click="parsePaste({{ $index }})" variant="primary">
                                    Rellenar campos
                                </flux:button>
                            </div>
                            <p x-show="parseError" x-text="parseError" class="mt-1 text-xs text-red-600"></p>
                        </div>

                        <div class="mx-6 mt-4 mb-0 border-t border-dashed border-zinc-200 flex items-center justify-center">
                            <span class="bg-white px-3 -mt-2.5 text-xs text-zinc-400">{{ __('o rellena campo a campo') }}</span>
                        </div>

                        <!-- Campos individuales -->
                        <div class="p-6 sigpac-fields">
                            <div class="grid grid-cols-4 md:grid-cols-8 gap-3">
                                @php
                                    $fieldDefs = [
                                        ['key' => 'code_autonomous_community', 'label' => 'CA',        'hint' => '2 díg.', 'placeholder' => '13',    'max' => 2],
                                        ['key' => 'code_province',             'label' => 'Provincia', 'hint' => '2 díg.', 'placeholder' => '28',    'max' => 2],
                                        ['key' => 'code_municipality',         'label' => 'Municipio', 'hint' => '3 díg.', 'placeholder' => '079',   'max' => 3],
                                        ['key' => 'code_aggregate',            'label' => 'Agregado',  'hint' => '3 díg.', 'placeholder' => '000',   'max' => 3],
                                        ['key' => 'code_zone',                 'label' => 'Zona',      'hint' => '3 díg.', 'placeholder' => '000',   'max' => 3],
                                        ['key' => 'code_polygon',              'label' => 'Polígono',  'hint' => '3 díg.', 'placeholder' => '012',   'max' => 3],
                                        ['key' => 'code_plot',                 'label' => 'Parcela',   'hint' => '5 díg.', 'placeholder' => '00045', 'max' => 5],
                                        ['key' => 'code_enclosure',            'label' => 'Recinto',   'hint' => '3 díg.', 'placeholder' => '003',   'max' => 3],
                                    ];
                                @endphp
                                @foreach($fieldDefs as $field)
                                    @php
                                        $hasFieldError = $errors->has("sigpacCodes.{$index}.{$field['key']}");
                                        $isDupField = $hasDuplicate && in_array($field['key'], ['code_polygon','code_plot','code_enclosure']);
                                    @endphp
                                    <div>
                                        <label class="block text-xs font-semibold text-zinc-600 mb-1">
                                            {{ $field['label'] }} <span class="font-normal text-zinc-400">({{ $field['hint'] }})</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model.live="sigpacCodes.{{ $index }}.{{ $field['key'] }}"
                                            placeholder="{{ $field['placeholder'] }}"
                                            maxlength="{{ $field['max'] }}"
                                            x-on:input="advance($el)"
                                            x-on:blur="padField($el)"
                                            class="w-full font-mono text-center text-sm border rounded-lg px-2 py-2 focus:outline-none focus:ring-2 transition-colors
                                                {{ $hasFieldError || $isDupField ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-zinc-300 focus:ring-agro-500 focus:border-agro-500' }}"
                                        >
                                        @error("sigpacCodes.{$index}.{$field['key']}")
                                            <p class="mt-0.5 text-xs text-red-600 leading-tight">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            @if($isValid && !$hasDuplicate)
                                <div class="mt-4 p-4 bg-green-50 border border-green-300 rounded-xl flex items-center gap-3">
                                    <flux:icon icon="check-circle" class="size-5 text-green-600 shrink-0" />
                                    <div>
                                        <p class="text-xs text-green-700 font-medium">{{ __('Código generado') }}</p>
                                        <p class="text-base font-mono font-bold text-green-900 tracking-widest">{{ $formattedCode }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($hasDuplicate)
                                <div class="mt-4 p-4 bg-red-50 border border-red-300 rounded-xl flex items-center gap-3">
                                    <flux:icon icon="x-circle" class="size-5 text-red-600 shrink-0" />
                                    <p class="text-sm text-red-700"><strong>{{ __('Duplicado:') }}</strong> Polígono, Parcela y Recinto coinciden con otro código.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-200">
                <flux:button href="{{ route('sigpac.codes') }}" variant="outline">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary">{{ __('Actualizar Código SIGPAC') }}</flux:button>
            </div>
        </form>
    </x-agro.card>
</div>
