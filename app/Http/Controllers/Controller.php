<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve per_page from request. Supports "all" to load everything.
     * When "all" is passed, returns $max (default 1000) so paginate() returns one page.
     */
    protected function resolvePerPage(Request $request, int $default = 30, int $max = 500): int
    {
        $raw = $request->query('per_page', (string) $default);

        if ($raw === 'all') {
            return $max;
        }

        return min(max((int) $raw, 1), $max);
    }
}
