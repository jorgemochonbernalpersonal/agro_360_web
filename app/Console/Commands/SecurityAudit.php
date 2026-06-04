<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a quick security audit of the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🛡️ Starting Agro365 Security Audit...');
        $this->newLine();

        $this->checkEnvironment();
        $this->checkDatabase();
        $this->checkMiddleware();
        $this->checkSensitiveFiles();

        $this->newLine();
        $this->info('✅ Audit complete. Please review the warnings above.');

        return Command::SUCCESS;
    }

    protected function checkEnvironment()
    {
        $this->comment('Checking Environment Configuration:');

        $debug = config('app.debug');
        if ($debug) {
            $this->warn('  ⚠️ APP_DEBUG is true. This should be false in production.');
        } else {
            $this->info('  ✅ APP_DEBUG is false.');
        }

        $env = config('app.env');
        $this->info("  ℹ️ Current environment: {$env}");

        if ($env === 'production' && ! config('session.secure')) {
            $this->warn('  ⚠️ SESSION_SECURE_COOKIE is false in production.');
        }
    }

    protected function checkDatabase()
    {
        $this->newLine();
        $this->comment('Checking Database Security:');

        try {
            DB::connection()->getPdo();
            $this->info('  ✅ Database connection is working.');
        } catch (\Exception $e) {
            $this->error('  ❌ Database connection failed.');
        }
    }

    protected function checkMiddleware()
    {
        $this->newLine();
        $this->comment('Checking Security Middleware:');

        $hasBotDefense = class_exists(\App\Http\Middleware\BotDefense::class);
        if ($hasBotDefense) {
            $this->info('  ✅ BotDefense middleware is present.');
        } else {
            $this->error('  ❌ BotDefense middleware is missing.');
        }

        $hasSecurityHeaders = class_exists(\App\Http\Middleware\SecurityHeaders::class);
        if ($hasSecurityHeaders) {
            $this->info('  ✅ SecurityHeaders middleware is present.');
        }
    }

    protected function checkSensitiveFiles()
    {
        $this->newLine();
        $this->comment('Checking for sensitive files in root:');

        $sensitiveFiles = [
            'fix-image-urls.php',
            'verify_fix.php',
            'fix-schema-escaping.php',
            'region-data.php',
            'env.production.example',
            'env.local.example',
        ];

        foreach ($sensitiveFiles as $file) {
            if (file_exists(base_path($file))) {
                $this->warn("  ⚠️ Sensitive file found in root: {$file}. Please remove it.");
            }
        }
    }
}
