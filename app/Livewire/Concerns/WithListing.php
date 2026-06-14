<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Boilerplate común de las vistas de listado (index).
 *
 * Aporta lo que se repite en decenas de componentes: paginación, búsqueda y
 * pestañas, con reseteo de página y persistencia en la URL. El componente sigue
 * definiendo sus propios filtros y la query en render().
 *
 * Uso básico (tab por defecto 'active'):
 *   use App\Livewire\Concerns\WithListing;
 *   class Index extends Component {
 *       use WithListing, WithToastNotifications;
 *       // definir aquí solo los filtros propios (p.ej. $filterType) y render()
 *   }
 *
 * Tab por defecto distinto de 'active' (p.ej. 'all', 'pending'):
 *   protected function defaultTab(): string { return 'all'; }
 *
 * Al adoptarlo, elimina del componente: WithPagination, las propiedades
 * $search/$currentTab, los métodos switchTab()/updatingSearch() y las entradas
 * 'search'/'currentTab' de $queryString (las cubre este trait).
 */
trait WithListing
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /**
     * No se inicializa con un valor literal: el default real lo fija
     * mountWithListing() a partir de defaultTab(), de modo que cada componente
     * pueda usar un tab por defecto propio (PHP no permite redeclarar el valor
     * inicial de una propiedad de trait en la clase que lo usa).
     */
    public string $currentTab = '';

    /**
     * Fija el tab por defecto cuando no viene de la URL. Es indiferente al orden
     * respecto a la hidratación del query string: si la URL trae `tab`, gana la
     * URL (currentTab ya no está vacío); si no, se rellena con defaultTab().
     */
    public function mountWithListing(): void
    {
        if ($this->currentTab === '') {
            $this->currentTab = $this->defaultTab();
        }
    }

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

    /**
     * queryString del trait. Livewire lo mergea automáticamente vía la
     * convención queryString{NombreTrait}(). Se define como método (no como
     * propiedad) para poder calcular el `except` a partir de defaultTab(): así
     * el tab por defecto se omite de la URL aunque varíe por componente.
     */
    protected function queryStringWithListing(): array
    {
        return [
            'currentTab' => ['as' => 'tab', 'except' => $this->defaultTab()],
        ];
    }

    /** Tab por defecto. Sobrescribir para usar otro (p.ej. 'all', 'pending'). */
    protected function defaultTab(): string
    {
        return 'active';
    }
}
