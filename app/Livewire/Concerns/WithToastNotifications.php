<?php

namespace App\Livewire\Concerns;

trait WithToastNotifications
{
    /**
     * Mostrar notificación toast de éxito
     */
    protected function toastSuccess(string $message): void
    {
        $this->dispatch('toast', type: 'success', message: $message);
    }

    /**
     * Mostrar notificación toast de error
     */
    protected function toastError(string $message): void
    {
        $this->dispatch('toast', type: 'error', message: $message);
    }

    /**
     * Mostrar notificación toast de información
     */
    protected function toastInfo(string $message): void
    {
        $this->dispatch('toast', type: 'info', message: $message);
    }

    /**
     * Mostrar notificación toast de advertencia
     */
    protected function toastWarning(string $message): void
    {
        $this->dispatch('toast', type: 'warning', message: $message);
    }
}

