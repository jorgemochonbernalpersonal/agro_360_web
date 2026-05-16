<?php

if (!function_exists('format_area')) {
    /**
     * Formatear área en hectáreas
     */
    function format_area(float $hectares, int $decimals = 2): string
    {
        return number_format($hectares, $decimals, ',', '.') . ' ha';
    }
}

if (!function_exists('format_money')) {
    /**
     * Formatear cantidad de dinero
     */
    function format_money(float $amount, string $currency = 'EUR', int $decimals = 2): string
    {
        $formatted = number_format($amount, $decimals, ',', '.');
        
        return match($currency) {
            'EUR' => $formatted . ' €',
            'USD' => '$ ' . $formatted,
            default => $formatted . ' ' . $currency,
        };
    }
}

if (!function_exists('calculate_tax')) {
    /**
     * Calcular impuesto sobre un monto
     */
    function calculate_tax(float $amount, float $taxRate): float
    {
        return round($amount * ($taxRate / 100), 2);
    }
}

if (!function_exists('calculate_total_with_tax')) {
    /**
     * Calcular total con impuesto incluido
     */
    function calculate_total_with_tax(float $amount, float $taxRate): float
    {
        return round($amount * (1 + $taxRate / 100), 2);
    }
}

if (!function_exists('spanish_date')) {
    /**
     * Formatear fecha en español
     */
    function spanish_date($date, string $format = 'd/m/Y'): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }
}

if (!function_exists('spanish_datetime')) {
    /**
     * Formatear fecha y hora en español
     */
    function spanish_datetime($date): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format('d/m/Y H:i');
    }
}

if (!function_exists('time_ago')) {
    /**
     * Mostrar tiempo transcurrido en español
     */
    function time_ago($date): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->locale('es')->diffForHumans();
    }
}

if (!function_exists('campaign_year')) {
    /**
     * Obtener año de campaña actual
     */
    function campaign_year(): int
    {
        return now()->year;
    }
}

if (!function_exists('current_campaign')) {
    /**
     * Obtener campaña actual del usuario autenticado
     */
    function current_campaign(): ?\App\Models\Campaign
    {
        return auth()->user()?->campaigns()
            ->where('year', campaign_year())
            ->first();
    }
}

if (!function_exists('format_nif')) {
    /**
     * Formatear NIF/CIF español
     */
    function format_nif(string $nif): string
    {
        $nif = strtoupper(preg_replace('/[\s\-]/', '', $nif));

        if (strlen($nif) === 9) {
            return substr($nif, 0, 8) . '-' . substr($nif, 8);
        }

        return $nif;
    }
}

if (!function_exists('format_phone')) {
    /**
     * Formatear teléfono español
     */
    function format_phone(string $phone): string
    {
        $phone = preg_replace('/[\s\-]/', '', $phone);

        if (strlen($phone) === 9) {
            return '+34 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }

        return $phone;
    }
}

if (!function_exists('obfuscate_email')) {
    /**
     * Ofuscar email para privacidad
     */
    function obfuscate_email(string $email): string
    {
        [$local, $domain] = explode('@', $email);

        if (strlen($local) <= 2) {
            return substr($local, 0, 1) . '***@' . $domain;
        }

        return substr($local, 0, 2) . '***@' . $domain;
    }
}

if (!function_exists('flash_success')) {
    /**
     * Flash success message
     */
    function flash_success(string $message): void
    {
        session()->flash('success', $message);
    }
}

if (!function_exists('flash_error')) {
    /**
     * Flash error message
     */
    function flash_error(string $message): void
    {
        session()->flash('error', $message);
    }
}

if (!function_exists('flash_warning')) {
    /**
     * Flash warning message
     */
    function flash_warning(string $message): void
    {
        session()->flash('warning', $message);
    }
}

if (!function_exists('flash_info')) {
    /**
     * Flash info message
     */
    function flash_info(string $message): void
    {
        session()->flash('info', $message);
    }
}

if (!function_exists('is_production')) {
    /**
     * Verificar si estamos en producción
     */
    function is_production(): bool
    {
        return app()->environment('production');
    }
}

if (!function_exists('is_local')) {
    /**
     * Verificar si estamos en local
     */
    function is_local(): bool
    {
        return app()->environment('local');
    }
}

if (!function_exists('asset_versioned')) {
    /**
     * Asset con versioning automático
     */
    function asset_versioned(string $path): string
    {
        $timestamp = filemtime(public_path($path));
        return asset($path) . '?v=' . $timestamp;
    }
}

if (!function_exists('percentage')) {
    /**
     * Calcular porcentaje
     */
    function percentage(float $value, float $total): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($value / $total) * 100, 2);
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Truncar texto manteniendo palabras completas
     */
    function truncate_text(string $text, int $length = 100, string $ending = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return rtrim(substr($text, 0, $length)) . $ending;
    }
}

if (!function_exists('active_route')) {
    /**
     * Verificar si la ruta está activa (para menús)
     */
    function active_route(string $routeName, string $activeClass = 'active'): string
    {
        return request()->routeIs($routeName) ? $activeClass : '';
    }
}

if (!function_exists('roleRoute')) {
    /**
     * Generate a role-aware URL for shared Blade views.
     *
     * - Producer  → producer.{suffix}
     * - Winery    → winery.{suffix}
     * - Others (viticulturist, etc.) → {suffix}  (no prefix)
     *
     * Use instead of route('winery.X') in views shared across roles.
     */
    function roleRoute(string $suffix, mixed $parameters = []): string
    {
        $user = auth()->user();

        // Strip existing role prefix if present (allows roleRoute('winery.X') and roleRoute('X'))
        $cleanSuffix = preg_replace('/^(winery|viticulturist|producer)\./', '', $suffix);

        if ($user?->isProducer()) {
            $producerRoute = "producer.{$cleanSuffix}";
            if (\Illuminate\Support\Facades\Route::has($producerRoute)) {
                return route($producerRoute, $parameters);
            }
            // Fallback: try winery, then viticulturist, then original
        }

        if ($user?->hasWineryAccess()) {
            $wineryRoute = "winery.{$cleanSuffix}";
            if (\Illuminate\Support\Facades\Route::has($wineryRoute)) {
                return route($wineryRoute, $parameters);
            }
        }

        if ($user?->hasViticulturistAccess()) {
            $vitRoute = "viticulturist.{$cleanSuffix}";
            if (\Illuminate\Support\Facades\Route::has($vitRoute)) {
                return route($vitRoute, $parameters);
            }
        }

        // Last resort: try the clean suffix (shared routes like plots.*, sigpac.*)
        return route($cleanSuffix, $parameters);
    }
}
