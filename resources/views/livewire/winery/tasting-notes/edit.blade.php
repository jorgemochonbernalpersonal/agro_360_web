<x-agro.form-card
    title="{{ __('Editar Nota de Cata') }}"
    :description="__('Modifica la evaluación sensorial registrada.')"
    icon="beaker"
    icon-color="from-amber-500 to-amber-700"
    :back-url="roleRoute('tasting-notes.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Vino y catador ─────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Vino y catador') }}" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Vino') }}</flux:label>
                    <flux:select wire:model.live="wine_id" required>
                        <flux:select.option value="">{{ __('Seleccionar vino...') }}</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de cata') }}</flux:label>
                    <flux:input wire:model="evaluation_date" type="date" required />
                    <flux:error name="evaluation_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Enólogo / catador') }}</flux:label>
                    <flux:select wire:model="oenologist_id">
                        <flux:select.option value="">{{ __('Sin enólogo asignado') }}</flux:select.option>
                        @foreach($oenologists as $oenologist)
                            <flux:select.option value="{{ $oenologist->id }}">{{ $oenologist->surname }}, {{ $oenologist->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="oenologist_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nombre del catador (externo)') }}</flux:label>
                    <flux:input wire:model="evaluator_name" />
                    <flux:error name="evaluator_name" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Fase visual ─────────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Fase visual') }}" color="yellow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>{{ __('Color') }}</flux:label>
                    <flux:input wire:model="visual_color" />
                    <flux:error name="visual_color" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Limpidez') }}</flux:label>
                    <flux:select wire:model="visual_clarity">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($visualClarityOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="visual_clarity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Intensidad') }}</flux:label>
                    <flux:select wire:model="visual_intensity">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($visualIntensityOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="visual_intensity" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Fase olfativa ──────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Fase olfativa') }}" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>{{ __('Intensidad aromática') }}</flux:label>
                    <flux:select wire:model="aroma_intensity">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($aromaIntensityOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="aroma_intensity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Descriptores aromáticos') }}</flux:label>
                    <flux:textarea wire:model="aroma_descriptors" rows="3" />
                    <flux:error name="aroma_descriptors" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Fase gustativa ─────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Fase gustativa') }}" color="blue">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                <flux:field>
                    <flux:label>{{ __('Acidez') }}</flux:label>
                    <flux:select wire:model="palate_acidity">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($palateLevelOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="palate_acidity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Taninos') }}</flux:label>
                    <flux:select wire:model="palate_tannins">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($palateLevelOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="palate_tannins" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Cuerpo') }}</flux:label>
                    <flux:select wire:model="palate_body">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($palateBodyOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="palate_body" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Persistencia') }}</flux:label>
                    <flux:select wire:model="palate_finish">
                        <flux:select.option value="">—</flux:select.option>
                        @foreach($palateFinishOptions as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="palate_finish" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Valoración global ──────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Valoración global') }}" color="indigo">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>{{ __('Puntuación (0–100)') }}</flux:label>
                    <flux:input wire:model="overall_score" type="number" min="0" max="100" step="0.5" />
                    <flux:error name="overall_score" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Conclusión') }}</flux:label>
                    <flux:textarea wire:model="overall_conclusion" rows="3" />
                    <flux:error name="overall_conclusion" />
                </flux:field>

            </div>

            <flux:field class="mt-4">
                <flux:label>{{ __('Notas adicionales') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('tasting-notes.index')" submit-:label="__('Guardar cambios')" />
    </form>
</x-agro.form-card>
