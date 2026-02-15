<?php

namespace App\Macros;

use Illuminate\Support\Str;

class StringMacros
{
    /**
     * Registrar todos los macros personalizados para strings
     */
    public static function register(): void
    {
        self::registerFormatNif();
        self::registerFormatPhone();
        self::registerObfuscateEmail();
        self::registerTruncateMiddle();
        self::registerFormatCurrency();
    }

    /**
     * Formatear NIF/CIF
     */
    protected static function registerFormatNif(): void
    {
        Str::macro('formatNif', function (string $nif) {
            // Eliminar espacios y guiones
            $nif = strtoupper(preg_replace('/[\s\-]/', '', $nif));

            // Formato: 12345678-A o X1234567-A
            if (strlen($nif) === 9) {
                return substr($nif, 0, 8) . '-' . substr($nif, 8);
            }

            return $nif;
        });
    }

    /**
     * Formatear teléfono español
     */
    protected static function registerFormatPhone(): void
    {
        Str::macro('formatPhone', function (string $phone) {
            // Eliminar espacios y guiones
            $phone = preg_replace('/[\s\-]/', '', $phone);

            // Formato español: +34 XXX XXX XXX
            if (strlen($phone) === 9) {
                return '+34 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
            }

            if (strlen($phone) === 11 && substr($phone, 0, 2) === '34') {
                return '+' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 3) . ' ' . substr($phone, 5, 3) . ' ' . substr($phone, 8);
            }

            return $phone;
        });
    }

    /**
     * Ofuscar email
     */
    protected static function registerObfuscateEmail(): void
    {
        Str::macro('obfuscateEmail', function (string $email) {
            [$local, $domain] = explode('@', $email);

            if (strlen($local) <= 2) {
                return substr($local, 0, 1) . '***@' . $domain;
            }

            return substr($local, 0, 2) . '***@' . $domain;
        });
    }

    /**
     * Truncar desde el medio
     */
    protected static function registerTruncateMiddle(): void
    {
        Str::macro('truncateMiddle', function (string $string, int $maxLength = 50, string $separator = '...') {
            if (strlen($string) <= $maxLength) {
                return $string;
            }

            $separatorLength = strlen($separator);
            $charsToShow = $maxLength - $separatorLength;
            $frontChars = ceil($charsToShow / 2);
            $backChars = floor($charsToShow / 2);

            return substr($string, 0, $frontChars) . $separator . substr($string, -$backChars);
        });
    }

    /**
     * Formatear como moneda EUR
     */
    protected static function registerFormatCurrency(): void
    {
        Str::macro('formatCurrency', function (float $amount, string $currency = 'EUR', int $decimals = 2) {
            $formatted = number_format($amount, $decimals, ',', '.');
            
            return match($currency) {
                'EUR' => $formatted . ' €',
                'USD' => '$ ' . $formatted,
                default => $formatted . ' ' . $currency,
            };
        });
    }
}
