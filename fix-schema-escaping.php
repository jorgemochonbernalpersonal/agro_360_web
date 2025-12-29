<?php

/**
 * Script to fix unescaped @ symbols in Schema.org JSON-LD markup
 * This prevents Blade from interpreting @type, @context as directives
 */

$viewsPath = __DIR__ . '/resources/views';
$fixed = 0;
$errors = 0;

// Find all blade files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getPathname();
        
        // Read file content
        $content = file_get_contents($filepath);
        
        // Check if file contains JSON-LD with unescaped @
        if (strpos($content, '"@type"') !== false || strpos($content, '"@context"') !== false) {
            echo "Fixing: " . str_replace($viewsPath, '', $filepath) . "\n";
            
            // Replace unescaped @ symbols in JSON-LD
            $newContent = str_replace('"@type"', '"@@type"', $content);
            $newContent = str_replace('"@context"', '"@@context"', $newContent);
            
            // Write back to file
            if (file_put_contents($filepath, $newContent) !== false) {
                $fixed++;
            } else {
                echo "  ERROR: Could not write to file\n";
                $errors++;
            }
        }
    }
}

echo "\n";
echo "========================================\n";
echo "Summary:\n";
echo "  Files fixed: $fixed\n";
echo "  Errors: $errors\n";
echo "========================================\n";
echo "\nDone! Now clearing view cache...\n";
