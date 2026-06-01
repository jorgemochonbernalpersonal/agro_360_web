<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'viticulturist', 'producer']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $invoice->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'winery', 'viticulturist', 'producer']);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $invoice->user_id === $user->id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }
}
