<?php

namespace App\Listeners;

use App\Models\WineryViticulturist;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class EnsureViticulturistSelfRecord
{
    public function handle(Login $event): void
    {
        // No crear WineryViticulturist con winery_id=null.
        // El registro se crea solo cuando una bodega real vincula al viticultor.
    }
}
