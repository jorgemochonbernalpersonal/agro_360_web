<?php

namespace App\Policies;

use App\Models\ProductLot;
use App\Models\User;

class ProductLotPolicy
{
    public function update(User $user, ProductLot $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, ProductLot $model): bool
    {
        return $this->update($user, $model);
    }
}
