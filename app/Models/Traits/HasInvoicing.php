<?php

namespace App\Models\Traits;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceGroup;
use App\Models\Tax;

trait HasInvoicing
{
    /**
     * Clientes del usuario
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Facturas del usuario
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Grupos de facturas
     */
    public function invoiceGroups()
    {
        return $this->hasMany(InvoiceGroup::class);
    }

    /**
     * Impuestos configurados
     */
    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'user_taxes')
            ->withPivot('is_default', 'order')
            ->withTimestamps();
    }

    /**
     * Impuesto por defecto
     */
    public function defaultTax()
    {
        return $this->belongsToMany(Tax::class, 'user_taxes')
            ->wherePivot('is_default', true)
            ->withPivot('is_default', 'order')
            ->withTimestamps();
    }
}
