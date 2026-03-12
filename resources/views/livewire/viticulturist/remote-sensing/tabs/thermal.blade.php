{{-- Tab Térmico: LST, estrés térmico --}}
@livewire('viticulturist.remote-sensing.thermal-stress-card', ['plot' => $selectedPlot], key('thermal-'.$selectedPlot->id))
