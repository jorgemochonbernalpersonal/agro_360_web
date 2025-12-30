<?php

/**
 * Script para corregir errores críticos en páginas de regiones vinícolas
 * 
 * Corrige:
 * - URLs canónicas incorrectas
 * - Keywords con referencias a Rioja
 * - Breadcrumb Schema.org
 * - Open Graph URLs
 */

$regionsData = [
    'ribera-duero' => [
        'name' => 'Ribera del Duero',
        'category' => 'DO',
        'slug' => 'ribera-duero',
        'keywords' => 'software viticultores ribera del duero, cuaderno campo ribera, gestión viñedo ribera, tinta del país, DO ribera del duero, consejo regulador ribera',
    ],
    'rueda' => [
        'name' => 'Rueda',
        'category' => 'DO',
        'slug' => 'rueda',
        'keywords' => 'software viticultores rueda, cuaderno campo rueda, gestión viñedo rueda, verdejo, DO rueda, vinos blancos rueda',
    ],
    'priorat' => [
        'name' => 'Priorat',
        'category' => 'DOQ',
        'slug' => 'priorat',
        'keywords' => 'software viticultores priorat, cuaderno campo priorat, gestión viñedo priorat, garnacha priorat, DOQ priorat, llicorella',
    ],
    'rias-baixas' => [
        'name' => 'Rías Baixas',
        'category' => 'DO',
        'slug' => 'rias-baixas',
        'keywords' => 'software viticultores rías baixas, cuaderno campo rías baixas, gestión viñedo galicia, albariño, DO rías baixas',
    ],
    'penedes' => [
        'name' => 'Penedès',
        'category' => 'DO',
        'slug' => 'penedes',
        'keywords' => 'software viticultores penedès, cuaderno campo penedès, gestión viñedo penedès, xarel·lo, macabeo, DO penedès, cava',
    ],
    'la-mancha' => [
        'name' => 'La Mancha',
        'category' => 'DO',
        'slug' => 'la-mancha',
        'keywords' => 'software viticultores la mancha, cuaderno campo la mancha, gestión viñedo la mancha, airén, tempranillo, DO la mancha',
    ],
    'toro' => [
        'name' => 'Toro',
        'category' => 'DO',
        'slug' => 'toro',
        'keywords' => 'software viticultores toro, cuaderno campo toro, gestión viñedo toro, tinta de toro, DO toro, zamora',
    ],
    'jumilla' => [
        'name' => 'Jumilla',
        'category' => 'DO',
        'slug' => 'jumilla',
        'keywords' => 'software viticultores jumilla, cuaderno campo jumilla, gestión viñedo jumilla, monastrell, DO jumilla, murcia',
    ],
];

$viewsPath = __DIR__ . '/resources/views/content/';
$errorsFound = 0;
$filesFixed = 0;

echo "🔧 Iniciando corrección de páginas de regiones vinícolas...\n\n";

foreach ($regionsData as $region) {
    $filename = "software-viticultores-{$region['slug']}.blade.php";
    $filepath = $viewsPath . $filename;
    
    if (!file_exists($filepath)) {
        echo "⚠️  Archivo no encontrado: {$filename}\n";
        continue;
    }
    
    echo "📄 Procesando: {$region['name']}...\n";
    
    $content = file_get_contents($filepath);
    $originalContent = $content;
    $regionErrors = 0;
    
    // 1. Corregir URL canonical
    $content = preg_replace(
        '/\<link rel="canonical" href="\{\{ url\(\'\/software-viticultores-rioja\'\) \}\}"\>/',
        '<link rel="canonical" href="{{ url(\'/software-viticultores-' . $region['slug'] . '\') }}">',
        $content,
        -1,
        $count
    );
    if ($count > 0) {
        echo "   ✓ Corregida URL canonical\n";
        $regionErrors += $count;
    }
    
    // 2. Corregir Open Graph URL
    $content = preg_replace(
        '/\<meta property="og:url" content="\{\{ url\(\'\/software-viticultores-rioja\'\) \}\}"\>/',
        '<meta property="og:url" content="{{ url(\'/software-viticultores-' . $region['slug'] . '\') }}">',
        $content,
        -1,
        $count
    );
    if ($count > 0) {
        echo "   ✓ Corregida Open Graph URL\n";
        $regionErrors += $count;
    }
    
    // 3. Corregir keywords (reemplazar referencias a rioja)
    $content = preg_replace(
        '/\<meta name="keywords" content="[^"]*rioja[^"]*"\>/',
        '<meta name="keywords" content="' . $region['keywords'] . '">',
        $content,
        -1,
        $count
    );
    if ($count > 0) {
        echo "   ✓ Corregidas keywords\n";
        $regionErrors += $count;
    }
    
    // 4. Corregir Breadcrumb Schema.org URL
    $content = preg_replace(
        '/\{"@@type": "ListItem", "position": 3, "name": "[^"]+", "item": "\{\{ url\(\'\/software-viticultores-rioja\'\) \}\}"\}/',
        '{"@@type": "ListItem", "position": 3, "name": "' . $region['name'] . '", "item": "{{ url(\'/software-viticultores-' . $region['slug'] . '\') }}"}',
        $content,
        -1,
        $count
    );
    if ($count > 0) {
        echo "   ✓ Corregido Breadcrumb Schema.org\n";
        $regionErrors += $count;
    }
    
    // 5. Corregir categoría DOCa incorrecta (solo para regiones que no son DOCa)
    if ($region['category'] !== 'DOCa') {
        $content = preg_replace(
            '/DOCa ' . preg_quote($region['name'], '/') . ' - Denominación de Origen Calificada/',
            $region['category'] . ' ' . $region['name'],
            $content,
            -1,
            $count
        );
        if ($count > 0) {
            echo "   ✓ Corregida categoría DO/DOQ\n";
            $regionErrors += $count;
        }
    }
    
    // Guardar cambios si hubo modificaciones
    if ($content !== $originalContent) {
        file_put_contents($filepath, $content);
        $filesFixed++;
        $errorsFound += $regionErrors;
        echo "   ✅ {$regionErrors} errores corregidos en {$region['name']}\n\n";
    } else {
        echo "   ℹ️  No se encontraron errores en {$region['name']}\n\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Corrección completada!\n";
echo "📊 Resumen:\n";
echo "   - Archivos procesados: " . count($regionsData) . "\n";
echo "   - Archivos corregidos: {$filesFixed}\n";
echo "   - Total de errores corregidos: {$errorsFound}\n";
echo str_repeat('=', 60) . "\n";
