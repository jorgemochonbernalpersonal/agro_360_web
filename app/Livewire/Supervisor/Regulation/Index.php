<?php

namespace App\Livewire\Supervisor\Regulation;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $user = Auth::user();

        return view('livewire.supervisor.regulation.index', [
            'doName' => $user->name,
        ]);
    }
}
