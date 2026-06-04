<?php

namespace App\Livewire\Shared;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

abstract class AbstractEdit extends Component
{
    use WithOwnershipRules, WithToastNotifications;

    public function save(): mixed
    {
        $this->validate();
        $this->performUpdate();
        $this->toastSuccess($this->successMessage());

        return redirect()->route($this->resolveIndexRoute());
    }

    public function render(): View
    {
        return view($this->resolveViewName(), $this->viewData())
            ->layout('layouts.app');
    }

    abstract protected function rules(): array;

    abstract protected function performUpdate(): void;

    abstract protected function successMessage(): string;

    abstract protected function indexRoute(): string;

    protected function viewData(): array
    {
        return [];
    }

    /**
     * Resolve the index route respecting the producer role prefix.
     */
    protected function resolveIndexRoute(): string
    {
        $route = $this->indexRoute();

        if (Auth::user()?->isProducer() && str_starts_with($route, 'winery.')) {
            $producerRoute = Str::replaceFirst('winery.', 'producer.', $route);
            if (\Illuminate\Support\Facades\Route::has($producerRoute)) {
                return $producerRoute;
            }
        }

        return $route;
    }

    protected function resolveViewName(): string
    {
        $relative = str_replace('App\\Livewire\\', '', static::class);

        return 'livewire.'.implode('.', array_map(
            fn (string $part) => Str::kebab($part),
            explode('\\', $relative),
        ));
    }
}
