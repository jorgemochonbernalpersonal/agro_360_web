<?php

namespace App\Livewire\Shared;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Database\Eloquent\Model;
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
     * Column on the edited model that holds the owning user's id.
     * Override per role (e.g. 'viticulturist_id'). Defaults to 'user_id'.
     */
    protected function ownerColumn(): string
    {
        return 'user_id';
    }

    /**
     * The authenticated user's id, for ownership comparisons in performUpdate().
     */
    protected function ownerId(): int
    {
        return (int) Auth::id();
    }

    /**
     * Guard ownership in mount(): aborts 403 if the model is not owned by the
     * authenticated user. Roles with relational ownership (e.g. harvest via
     * activity) should override this with their own check.
     */
    protected function authorizeOwnership(Model $model): void
    {
        if ($model->getAttribute($this->ownerColumn()) !== Auth::id()) {
            abort(403);
        }
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
