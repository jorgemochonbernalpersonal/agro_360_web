<?php

namespace App\Policies;

use App\Models\LabelBatch;
use App\Models\User;

class LabelBatchPolicy
{
    public function update(User $user, LabelBatch $model): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $model->user_id === $user->id;
    }

    public function delete(User $user, LabelBatch $model): bool
    {
        return $this->update($user, $model);
    }
}
