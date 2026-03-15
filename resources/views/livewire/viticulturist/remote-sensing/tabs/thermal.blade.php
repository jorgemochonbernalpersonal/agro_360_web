{{-- Tab Térmico: LST, estrés térmico --}}
@livewire('viticulturist.remote-sensing.thermal-stress-card', ['plot' => $selectedPlot, 'sigpacId' => $selectedSigpacId], key('thermal-'.$selectedPlot->id.'-'.$selectedSigpacId))
