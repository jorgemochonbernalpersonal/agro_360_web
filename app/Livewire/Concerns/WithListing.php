<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Boilerplate común de las vistas de listado (index).
 *
 * Aporta lo que se repite en decenas de componentes: paginación, búsqueda y
 * pestañas activas/inactivas, con reseteo de página y persistencia en la URL.
 * El componente sigue definiendo sus propios filtros y la query en render().
 *
 * Uso:
 *   use App\Livewire\Concerns\WithListing;
 *   class Index extends Component {
 *       use WithListing, WithToastNotifications;
 *       // definir aquí solo los filtros propios (p.ej. $filterType) y render()
 *   }
 *
 * Al adoptarlo, elimina del componente: WithPagination, las propiedades
 * $search/$currentTab, los métodos switchTab()/updatingSearch() y las entradas
 * 'search'/'currentTab' de $queryString (las cubre #[Url] de este trait).
 */
trait WithListing
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'tab', except: 'active')]
    public string $currentTab = 'active';

    /** Reinicia la paginación al teclear en el buscador. */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Cambia de pestaña y reinicia la paginación. */
    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }
}
