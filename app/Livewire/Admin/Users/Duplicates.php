<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Duplicates extends Component
{
    use WithToastNotifications;

    public string $mode = 'email'; // email | name

    public function merge(int $keepId, int $deleteId): void
    {
        $keep   = User::findOrFail($keepId);
        $delete = User::findOrFail($deleteId);

        if ($keep->isAdmin() || $delete->isAdmin()) {
            $this->toastError(__('No se pueden fusionar cuentas de administrador.'));
            return;
        }

        // Reassign relations
        DB::table('plots')->where('viticulturist_id', $deleteId)->update(['viticulturist_id' => $keepId]);
        DB::table('agricultural_activities')->where('viticulturist_id', $deleteId)->update(['viticulturist_id' => $keepId]);
        DB::table('support_tickets')->where('user_id', $deleteId)->update(['user_id' => $keepId]);
        DB::table('invoices')->where('user_id', $deleteId)->update(['user_id' => $keepId]);
        DB::table('subscriptions')->where('user_id', $deleteId)->update(['user_id' => $keepId]);
        DB::table('clients')->where('user_id', $deleteId)->update(['user_id' => $keepId]);

        $deleteName = $delete->name;
        $delete->delete();

        $this->toastSuccess("Cuenta de \"{$deleteName}\" fusionada en \"{$keep->name}\" y eliminada.");
    }

    public function render()
    {
        $groups = collect();

        if ($this->mode === 'email') {
            // Find emails with common domain/prefix — group by normalized email
            $users = User::excludeDemo()
                ->orderBy('email')
                ->get(['id', 'name', 'email', 'role', 'can_login', 'created_at', 'email_verified_at']);

            // Group by email prefix (before +) and domain
            $buckets = [];
            foreach ($users as $user) {
                $normalized = strtolower(preg_replace('/\+[^@]*/', '', $user->email));
                $buckets[$normalized][] = $user;
            }

            $groups = collect($buckets)->filter(fn ($g) => count($g) > 1)->values();

        } else {
            // Group by normalized name (lowercase, no extra spaces)
            $users = User::excludeDemo()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'can_login', 'created_at', 'email_verified_at']);

            $buckets = [];
            foreach ($users as $user) {
                $key = strtolower(preg_replace('/\s+/', ' ', trim($user->name)));
                $buckets[$key][] = $user;
            }

            $groups = collect($buckets)->filter(fn ($g) => count($g) > 1)->values();
        }

        return view('livewire.admin.users.duplicates', compact('groups'))
            ->layout('layouts.app', [
                'title'       => __('Duplicados - Admin - Agro365'),
                'description' => __('Detección y fusión de cuentas duplicadas'),
            ]);
    }
}
