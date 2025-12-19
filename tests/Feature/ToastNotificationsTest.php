<?php

namespace Tests\Feature;

use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

// Componente de prueba para testear el trait
class TestToastComponent extends Component
{
    use WithToastNotifications;

    public function testSuccess()
    {
        $this->toastSuccess('Operación exitosa');
    }

    public function testError()
    {
        $this->toastError('Error en la operación');
    }

    public function testInfo()
    {
        $this->toastInfo('Información importante');
    }

    public function testWarning()
    {
        $this->toastWarning('Advertencia');
    }

    public function render()
    {
        return '<div>Test Component</div>';
    }
}

class ToastNotificationsTest extends TestCase
{
    public function test_toast_success_dispatches_event(): void
    {
        Livewire::test(TestToastComponent::class)
            ->call('testSuccess')
            ->assertDispatched('toast', type: 'success', message: 'Operación exitosa');
    }

    public function test_toast_error_dispatches_event(): void
    {
        Livewire::test(TestToastComponent::class)
            ->call('testError')
            ->assertDispatched('toast', type: 'error', message: 'Error en la operación');
    }

    public function test_toast_info_dispatches_event(): void
    {
        Livewire::test(TestToastComponent::class)
            ->call('testInfo')
            ->assertDispatched('toast', type: 'info', message: 'Información importante');
    }

    public function test_toast_warning_dispatches_event(): void
    {
        Livewire::test(TestToastComponent::class)
            ->call('testWarning')
            ->assertDispatched('toast', type: 'warning', message: 'Advertencia');
    }
}

