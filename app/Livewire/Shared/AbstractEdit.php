<?php

namespace App\Livewire\Shared;

use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

abstract class AbstractEdit extends Component
{
    use WithToastNotifications;

    abstract protected function rules(): array;

    abstract protected function performUpdate(): void;

    abstract protected function successMessage(): string;

    abstract protected function indexRoute(): string;

    protected function viewData(): array
    {
        return [];
    }

    public function save(): mixed
    {
        $this->validate();
        $this->performUpdate();
        $this->toastSuccess($this->successMessage());

        return redirect()->route($this->indexRoute());
    }

    public function render(): View
    {
        return view($this->resolveViewName(), $this->viewData())
            ->layout('layouts.app');
    }

    protected function resolveViewName(): string
    {
        $relative = str_replace('App\\Livewire\\', '', static::class);

        return 'livewire.' . implode('.', array_map(
            fn(string $part) => Str::kebab($part),
            explode('\\', $relative),
        ));
    }
}
