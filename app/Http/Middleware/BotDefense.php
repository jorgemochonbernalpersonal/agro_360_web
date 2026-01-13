<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Log;

class BotDefense
{
    /**
     * Common malicious paths targeted by bots.
     */
    protected array $maliciousPaths = [
        'wp-admin',
        'wp-login.php',
        '.env',
        '.git/',
        'setup.php',
        'config.php',
        'phpinfo.php',
        'xmlrpc.php',
        'backup.sql',
        'dump.sql',
        'database.sql',
        'pma/',
        'mysql/',
        'phpmyadmin/',
        'cgi-bin/',
        '.well-known/security.txt',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        foreach ($this->maliciousPaths as $maliciousPath) {
            if (str_contains($path, $maliciousPath)) {
                // Log the suspicious activity
                SecurityLogger::logAccessDenied(
                    auth()->id() ?? 0,
                    $request->fullUrl(),
                    'bot_detection: matched_path=' . $maliciousPath . ' ip=' . $request->ip()
                );

                Log::warning("Bot detection: Suspicious request to '{$path}' from IP: " . $request->ip());

                // Return a 404 instead of 403 to avoid confirming the path exists (security by obscurity)
                // or just slow down the bot.
                abort(404);
            }
        }

        return $next($request);
    }
}
