<?php

/**
 * Fix Image URLs - Replace url() with asset() for images
 * This fixes the issue where images don't load on different computers
 */

$viewsPath = __DIR__ . '/resources/views';
$fixed = 0;
$errors = 0;

// Find all blade files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

echo "🔧 Fixing image URLs in Blade templates...\n\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getPathname();
        
        // Read file content
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
        
        // Check if file was modified
        if ($content !== $originalContent) {
            $relativePath = str_replace($viewsPath, '', $filepath);
            echo "📝 Fixing: $relativePath\n";
            
            // Write back to file
            if (file_put_contents($filepath, $content) !== false) {
                $fixed++;
            } else {
                echo "  ❌ Error: Could not write to file\n";
                $errors++;
            }
        }
    }
}

echo "\n========================================\n";
echo "📊 Summary:\n";
echo "  ✅ Files fixed: $fixed\n";
echo "  ❌ Errors: $errors\n";
echo "========================================\n";

echo "\n✅ Done! Image URLs now use asset() helper.\n";
echo "💡 Images will now load correctly on all computers.\n";
