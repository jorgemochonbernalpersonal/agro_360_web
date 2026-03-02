<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

abstract class AbstractEdit extends Component
{
    use WithToastNotifications;

    /**
     * Validation rules for the form fields.
     */
    abstract protected function rules(): array;

    /**
     * Perform the model update after validation passes.
     */
    abstract protected function performUpdate(): void;

    /**
     * Toast message shown on successful update.
     */
    abstract protected function successMessage(): string;

    /**
     * Named route to redirect to after update.
     */
    abstract protected function indexRoute(): string;

    /**
     * Data passed to the view. Override to provide additional variables.
     */
    protected function viewData(): array
    {
        return [];
    }

    /**
     * Returns the authenticated viticulturist ID.
     */
    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    /**
     * Aborts with 403 if the model does not belong to the authenticated user.
     */
    protected function authorizeOwnership(Model $model): void
    {
        if ($model->viticulturist_id !== Auth::id()) {
            abort(403);
        }
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

    /**
     * Derives the Blade view name from the component class namespace.
     * App\Livewire\Viticulturist\AdvisoryMemberships\Edit
     * → livewire.viticulturist.advisory-memberships.edit
     */
    private function resolveViewName(): string
    {
        $relative = str_replace('App\\Livewire\\', '', static::class);

        return 'livewire.' . implode('.', array_map(
            fn(string $part) => Str::kebab($part),
            explode('\\', $relative),
        ));
    }
}
