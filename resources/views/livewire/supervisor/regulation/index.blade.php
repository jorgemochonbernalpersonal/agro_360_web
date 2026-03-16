<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Normativa DO"
        description="Marco regulatorio de la denominación de origen."
    />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <x-agro.card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="document-text" class="w-5 h-5 text-indigo-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-800">Pliego de condiciones</p>
                    <p class="text-xs text-zinc-400">Requisitos varietales, agronómicos y enológicos</p>
                </div>
                <span class="ml-auto text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full uppercase">Próximamente</span>
            </div>
            <ul class="space-y-1.5 text-sm text-zinc-500">
                @foreach(['Variedades autorizadas', 'Rendimientos máximos', 'Prácticas de cultivo', 'Elaboración y crianza', 'Características analíticas mínimas'] as $item)
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-indigo-300 flex-shrink-0"></span>{{ $item }}</li>
                @endforeach
            </ul>
        </x-agro.card>

        <x-agro.card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="clipboard-document-list" class="w-5 h-5 text-blue-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-800">Reglamento interno</p>
                    <p class="text-xs text-zinc-400">Normativa de funcionamiento de la DO</p>
                </div>
                <span class="ml-auto text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full uppercase">Próximamente</span>
            </div>
            <ul class="space-y-1.5 text-sm text-zinc-500">
                @foreach(['Órganos de gobierno', 'Registro de operadores', 'Procedimientos de control', 'Sanciones y expedientes', 'Modificaciones del pliego'] as $item)
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-300 flex-shrink-0"></span>{{ $item }}</li>
                @endforeach
            </ul>
        </x-agro.card>

        <x-agro.card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="check-badge" class="w-5 h-5 text-emerald-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-800">Autorizaciones</p>
                    <p class="text-xs text-zinc-400">Plantación, elaboración y certificaciones</p>
                </div>
                <span class="ml-auto text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full uppercase">Próximamente</span>
            </div>
            <ul class="space-y-1.5 text-sm text-zinc-500">
                @foreach(['Autorizaciones de plantación', 'Autorizaciones de elaboración', 'Certificaciones ecológicas DO', 'Derechos de replantación'] as $item)
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300 flex-shrink-0"></span>{{ $item }}</li>
                @endforeach
            </ul>
        </x-agro.card>

        <x-agro.card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="arrow-top-right-on-square" class="w-5 h-5 text-rose-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-800">Comunicaciones administrativas</p>
                    <p class="text-xs text-zinc-400">INFOVI y MAPAMA</p>
                </div>
                <span class="ml-auto text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full uppercase">Próximamente</span>
            </div>
            <ul class="space-y-1.5 text-sm text-zinc-500">
                @foreach(['INFOVI — Registro vitivinícola', 'Comunicaciones al MAPAMA', 'Declaraciones de cosecha', 'Registro de operadores CCAA'] as $item)
                    <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-rose-300 flex-shrink-0"></span>{{ $item }}</li>
                @endforeach
            </ul>
        </x-agro.card>

    </div>

    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>Gestión documental en construcción</flux:callout.heading>
        <flux:callout.text>
            La carga y consulta de documentos normativos (pliego, reglamento, autorizaciones) estará disponible próximamente.
            Por ahora, la calificación de vinos e inspecciones están operativas en sus respectivos módulos.
        </flux:callout.text>
    </flux:callout>

</div>
