<?php
/**
 * Quick Region Page Generator
 * Generates all 8 remaining wine region pages based on Rioja template
 */

$regions = [
    'ribera-duero' => [
        'name' => 'Ribera del Duero',
        'badge' => 'DO Ribera del Duero',
        'surface' => '23.000',
        'wineries' => '300+',
        'growers' => '8.000+',
        'production' => '100',
        'zones' => 'Burgos, Valladolid, Segovia, Soria',
        'variety_main' => 'Tinta del País (Tempranillo)',
        'variety_pct' => '95%',
        'variety_others' => 'Cabernet Sauvignon, Merlot, Malbec, Garnacha, Albillo',
        'climate' => 'Continental extremo: inviernos muy fríos (-18°C), veranos cálidos (40°C). Amplitud térmica de 20°C día/noche.',
        'yield' => '7.000'
    ],
    'rueda' => [
        'name' => 'Rueda',
        'badge' => 'DO Rueda',
        'surface' => '18.000',
        'wineries' => '80+',
        'growers' => '1.500+',
        'production' => '85',
        'zones' => 'Valladolid, Segovia, Ávila',
        'variety_main' => 'Verdejo',
        'variety_pct' => '85%',
        'variety_others' => 'Sauvignon Blanc, Viura, Palomino',
        'climate' => 'Continental con influencia atlántica. Inviernos fríos con heladas frecuentes, veranos cálidos.',
        'yield' => '10.000'
    ],
    'priorat' => [
        'name' => 'Priorat',
        'badge' => 'DOQ Priorat',
        'surface' => '1.900',
        'wineries' => '100+',
        'growers' => '600+',
        'production' => '5',
        'zones' => 'Tarragona (Cataluña)',
        'variety_main' => 'Garnacha y Cariñena',
        'variety_pct' => '70%',
        'variety_others' => 'Cabernet Sauvignon, Syrah, Merlot',
        'climate' => 'Mediterráneo de montaña. Veranos cálidos y secos, inviernos suaves. Suelos de licorella característicos.',
        'yield' => '6.000'
    ],
    'rias-baixas' => [
        'name' => 'Rías Baixas',
        'badge' => 'DO Rías Baixas',
        'surface' => '4.000',
        'wineries' => '180+',
        'growers' => '5.000+',
        'production' => '25',
        'zones' => 'Val do Salnés, Condado do Tea, O Rosal, Soutomaior, Ribeira do Ulla',
        'variety_main' => 'Albariño',
        'variety_pct' => '96%',
        'variety_others' => 'Treixadura, Loureira, Caiño Blanco',
        'climate' => 'Atlántico húmedo. Inviernos suaves, veranos templados. Precipitación muy alta: 1.500-1.800mm anuales.',
        'yield' => '10.000'
    ],
    'penedes' => [
        'name' => 'Penedès',
        'badge' => 'DO Penedès',
        'surface' => '26.000',
        'wineries' => '280+',
        'growers' => '2.500+',
        'production' => '200',
        'zones' => 'Baix Penedès, Mitjà Penedès, Alt Penedès',
        'variety_main' => 'Xarel·lo, Macabeo, Parellada',
        'variety_pct' => '60%',
        'variety_others' => 'Chardonnay, Cabernet Sauvignon, Merlot, Tempranillo',
        'climate' => 'Mediterráneo con influencia marítima. Veranos cálidos, inviernos suaves. Tres zonas climáticas diferenciadas.',
        'yield' => '12.000'
    ],
    'la-mancha' => [
        'name' => 'La Mancha',
        'badge' => 'DO La Mancha',
        'surface' => '158.000',
        'wineries' => '280+',
        'growers' => '17.000+',
        'production' => '2.000',
        'zones' => 'Albacete, Ciudad Real, Cuenca, Toledo',
        'variety_main' => 'Airén',
        'variety_pct' => '50%',
        'variety_others' => 'Tempranillo, Garnacha, Syrah, Cabernet Sauvignon',
        'climate' => 'Continental extremo. Inviernos fríos, veranos muy cálidos (45°C). Precipitación muy baja: 300-400mm.',
        'yield' => '10.000'
    ],
    'toro' => [
        'name' => 'Toro',
        'badge' => 'DO Toro',
        'surface' => '5.800',
        'wineries' => '60+',
        'growers' => '1.200+',
        'production' => '25',
        'zones' => 'Zamora, Valladolid',
        'variety_main' => 'Tinta de Toro (Tempranillo)',
        'variety_pct' => '90%',
        'variety_others' => 'Garnacha, Verdejo, Malvasía',
        'climate' => 'Continental extremo. Inviernos muy fríos, veranos muy cálidos. Amplitud térmica extrema.',
        'yield' => '7.000'
    ],
    'jumilla' => [
        'name' => 'Jumilla',
        'badge' => 'DO Jumilla',
        'surface' => '23.000',
        'wineries' => '45+',
        'growers' => '2.500+',
        'production' => '70',
        'zones' => 'Murcia, Albacete',
        'variety_main' => 'Monastrell',
        'variety_pct' => '80%',
        'variety_others' => 'Tempranillo, Garnacha, Syrah, Cabernet Sauvignon',
        'climate' => 'Mediterráneo continental. Veranos muy cálidos y secos, inviernos suaves. Altitud media 700m.',
        'yield' => '7.000'
    ]
];

$template = file_get_contents(__DIR__ . '/resources/views/content/software-viticultores-rioja.blade.php');

foreach ($regions as $slug => $data) {
    $content = $template;
    
    // Replace Rioja-specific content with region data
    $content = str_replace('Rioja', $data['name'], $content);
    $content = str_replace('DOCa Rioja', $data['badge'], $content);
    $content = str_replace('65.000', $data['surface'], $content);
    $content = str_replace('500+', $data['wineries'], $content);
    $content = str_replace('14.000+', $data['growers'], $content);
    $content = str_replace('280', $data['production'], $content);
    $content = str_replace('Rioja Alta, Rioja Alavesa, Rioja Oriental', $data['zones'], $content);
    $content = str_replace('Tempranillo (75%)', $data['variety_main'] . ' (' . $data['variety_pct'] . ')', $content);
    $content = str_replace('6.500', $data['yield'], $content);
    
    // Save file
    $filename = "software-viticultores-{$slug}.blade.php";
    file_put_contents(__DIR__ . "/resources/views/content/{$filename}", $content);
    
    echo "✅ {$data['name']} generado\n";
}

echo "\n🎉 Todas las regiones generadas!\n";
