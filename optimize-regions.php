<?php

/**
 * Script para optimizar las 7 regiones vinícolas restantes
 * Actualiza contenido con datos precisos y únicos por región
 */

$regionsData = [
    'priorat' => [
        'category' => 'DOQ',
        'badge' => 'DOQ Priorat - Denominación de Origen Calificada',
        'superficie' => '1.900',
        'bodegas' => '100+',
        'viticultores' => '600+',
        'produccion' => '5 millones',
        'zonas' => 'Gratallops, Porrera, Poboleda, Torroja, La Morera, La Vilella, Bellmunt, El Lloar, Scala Dei, Masos de Falset, Solanes del Molar',
        'clima' => 'Mediterráneo con influencia continental: Veranos muy cálidos y secos, inviernos suaves. Precipitación 400-500mm anuales. Altitud 100-700m.',
        'rendimiento' => '6.000 kg/ha',
    ],
    'rias-baixas' => [
        'category' => 'DO',
        'badge' => 'DO Rías Baixas',
        'superficie' => '4.000',
        'bodegas' => '180+',
        'viticultores' => '5.500+',
        'produccion' => '35 millones',
        'zonas' => 'Val do Salnés, Condado do Tea, O Rosal, Soutomaior, Ribeira do Ulla',
        'clima' => 'Atlántico húmedo: Lluvias abundantes (1.200-1.600mm anuales). Temperaturas suaves. Alta humedad relativa. Influencia oceánica directa.',
        'rendimiento' => '10.000 kg/ha',
    ],
    'rueda' => [
        'category' => 'DO',
        'badge' => 'DO Rueda',
        'superficie' => '18.000',
        'bodegas' => '80+',
        'viticultores' => '1.500+',
        'produccion' => '85 millones',
        'zonas' => 'Valladolid, Segovia y Ávila. Municipios: Rueda, La Seca, Serrada, Medina del Campo',
        'clima' => 'Continental: Inviernos fríos con heladas, veranos cálidos y secos. Precipitación 300-500mm anuales. Gran amplitud térmica.',
        'rendimiento' => '12.000 kg/ha',
    ],
    'toro' => [
        'category' => 'DO',
        'badge' => 'DO Toro',
        'superficie' => '5.800',
        'bodegas' => '60+',
        'viticultores' => '1.200+',
        'produccion' => '20 millones',
        'zonas' => 'Zamora y Valladolid. Municipios: Toro, Morales, Venialbo, San Román de Hornija',
        'clima' => 'Continental extremo: Inviernos muy fríos, veranos muy cálidos (hasta 42°C). Precipitación 350-450mm. Sequía estival.',
        'rendimiento' => '7.000 kg/ha',
    ],
    'penedes' => [
        'category' => 'DO',
        'badge' => 'DO Penedès',
        'superficie' => '26.000',
        'bodegas' => '280+',
        'viticultores' => '2.500+',
        'produccion' => '200 millones',
        'zonas' => 'Penedès Superior, Penedès Central, Penedès Marítimo',
        'clima' => 'Mediterráneo: Suave y templado. Influencia marítima en la costa. Precipitación 500-600mm anuales.',
        'rendimiento' => 'Variable según tipo',
    ],
    'la-mancha' => [
        'category' => 'DO',
        'badge' => 'DO La Mancha',
        'superficie' => '158.000',
        'bodegas' => '280+',
        'viticultores' => '17.000+',
        'produccion' => '2.500 millones',
        'zonas' => 'Albacete, Ciudad Real, Cuenca y Toledo. La DO más grande del mundo',
        'clima' => 'Continental extremo: Inviernos fríos, veranos muy cálidos. Precipitación 300-400mm. Gran sequía estival.',
        'rendimiento' => 'Variable',
    ],
    'jumilla' => [
        'category' => 'DO',
        'badge' => 'DO Jumilla',
        'superficie' => '30.000',
        'bodegas' => '45+',
        'viticultores' => '2.500+',
        'produccion' => '70 millones',
        'zonas' => 'Murcia y Albacete. Altiplano de 400-800m',
        'clima' => 'Mediterráneo continental: Veranos muy cálidos y secos, inviernos fríos. Precipitación 300mm. Altitud modera temperaturas.',
        'rendimiento' => 'Variable',
    ],
];

$viewsPath = __DIR__ . '/resources/views/content/';
$filesUpdated = 0;

echo "🔧 Optimizando páginas de regiones vinícolas...\n\n";

foreach ($regionsData as $slug => $data) {
    $filename = "software-viticultores-{$slug}.blade.php";
    $filepath = $viewsPath . $filename;
    
    if (!file_exists($filepath)) {
        echo "⚠️  Archivo no encontrado: {$filename}\n";
        continue;
    }
    
    echo "📄 Optimizando: {$slug}...\n";
    
    $content = file_get_contents($filepath);
    
    // Actualizar badge
    $content = preg_replace(
        '/<span class="text-sm font-semibold text-red-800">[^<]+<\/span>/',
        '<span class="text-sm font-semibold text-red-800">' . $data['badge'] . '</span>',
        $content,
        1
    );
    
    // Actualizar superficie
    $content = preg_replace(
        '/<li><strong>Superficie:<\/strong> [^<]+<\/li>/',
        '<li><strong>Superficie:</strong> ' . $data['superficie'] . ' hectáreas</li>',
        $content,
        1
    );
    
    // Actualizar bodegas
    $content = preg_replace(
        '/<li><strong>Bodegas:<\/strong> [^<]+<\/li>/',
        '<li><strong>Bodegas:</strong> ' . $data['bodegas'] . ' bodegas registradas</li>',
        $content,
        1
    );
    
    // Actualizar viticultores
    $content = preg_replace(
        '/<li><strong>Viticultores:<\/strong> [^<]+<\/li>/',
        '<li><strong>Viticultores:</strong> ' . $data['viticultores'] . ' viticultores</li>',
        $content,
        1
    );
    
    // Actualizar producción
    $content = preg_replace(
        '/<li><strong>Producción anual:<\/strong> [^<]+<\/li>/',
        '<li><strong>Producción anual:</strong> ' . $data['produccion'] . ' de litros</li>',
        $content,
        1
    );
    
    // Actualizar zonas/provincias
    $content = preg_replace(
        '/<li><strong>(Zonas|Provincias):<\/strong> [^<]+<\/li>/',
        '<li><strong>Zonas:</strong> ' . $data['zonas'] . '</li>',
        $content,
        1
    );
    
    // Actualizar clima
    $content = preg_replace(
        '/<strong>Clima [^:]+:<\/strong> [^<]+/',
        '<strong>' . explode(':', $data['clima'])[0] . ':</strong> ' . explode(':', $data['clima'], 2)[1],
        $content,
        1
    );
    
    file_put_contents($filepath, $content);
    $filesUpdated++;
    echo "   ✅ Optimizado\n\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Optimización completada!\n";
echo "📊 Archivos actualizados: {$filesUpdated}/7\n";
echo str_repeat('=', 60) . "\n";
