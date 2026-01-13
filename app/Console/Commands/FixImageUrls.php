<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FixImageUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:image-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Image URLs - Replace url() with asset() for images in Blade templates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $viewsPath = resource_path('views');
        $fixed = 0;
        $errors = 0;

        $this->info("🔧 Fixing image URLs in Blade templates...");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewsPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filepath = $file->getPathname();
                
                $content = file_get_contents($filepath);
                $originalContent = $content;
                
                // Replace url('images/...') with asset('images/...')
                $content = preg_replace(
                    '/\{\{\s*url\([\'"]images\/([^\'"]+)[\'"]\)\s*\}\}/',
                    '{{ asset(\'images/$1\') }}',
                    $content
                );
                
                // Also replace in JSON-LD and other contexts
                $content = preg_replace(
                    '/url\([\'"]images\/([^\'"]+)[\'"]\)/',
                    'asset(\'images/$1\')',
                    $content
                );
                
                if ($content !== $originalContent) {
                    $relativePath = str_replace($viewsPath, '', $filepath);
                    $this->line("📝 Fixing: $relativePath");
                    
                    if (file_put_contents($filepath, $content) !== false) {
                        $fixed++;
                    } else {
                        $this->error("  ❌ Error: Could not write to file");
                        $errors++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("========================================");
        $this->info("📊 Summary:");
        $this->info("  ✅ Files fixed: $fixed");
        $this->info("  ❌ Errors: $errors");
        $this->info("========================================");

        return Command::SUCCESS;
    }
}
