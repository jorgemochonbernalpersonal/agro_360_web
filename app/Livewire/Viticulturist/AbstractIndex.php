<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Shared\AbstractIndex as SharedAbstractIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class AbstractIndex extends SharedAbstractIndex
{
    /**
     * Returns the authenticated viticulturist's user ID.
     */
    protected function viticulturistId(): int
    {
        return Auth::id();
    }

    /**
     * Find a model owned by the authenticated viticulturist, or abort 404.
     *
     * @template T of Model
     * @param  class-string<T>  $modelClass
     * @return T
     */
    protected function findOwned(string $modelClass, int $id): Model
    {
        return $modelClass::where('viticulturist_id', $this->viticulturistId())->findOrFail($id);
    }
}
