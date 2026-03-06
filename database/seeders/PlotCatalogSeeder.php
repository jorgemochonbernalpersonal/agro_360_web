<?php

namespace Database\Seeders;

use App\Models\IrrigationType;
use App\Models\Orientation;
use App\Models\PropertyType;
use App\Models\SoilType;
use App\Models\Topography;
use App\Models\Valley;
use Illuminate\Database\Seeder;

class PlotCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tipos de Suelo ────────────────────────────────────────────────────
        $soilTypes = [
            ['name' => 'Arcilloso',           'description' => 'Alta retención de agua, compacto y pesado. Común en zonas de llanura.'],
            ['name' => 'Arenoso',             'description' => 'Buena aireación y drenaje, bajo contenido en nutrientes. Sensible a la sequía.'],
            ['name' => 'Franco',              'description' => 'Equilibrio entre arena, limo y arcilla. Considerado el suelo ideal para viticultura.'],
            ['name' => 'Franco-arcilloso',    'description' => 'Predominio de arcilla con buena estructura. Retiene bien la humedad.'],
            ['name' => 'Franco-arenoso',      'description' => 'Predominio de arena con algo de arcilla. Buen drenaje y fácil laboreo.'],
            ['name' => 'Franco-limoso',       'description' => 'Suave al tacto, buena capacidad de retención hídrica y fertilidad media.'],
            ['name' => 'Limoso',              'description' => 'Textura fina y sedimentaria. Moderada retención de agua y nutrientes.'],
            ['name' => 'Pedregoso',           'description' => 'Alto contenido en piedras. Buen drenaje y acumulación de calor durante el día.'],
            ['name' => 'Calcáreo',            'description' => 'Rico en carbonato cálcico. Confiere acidez baja al mosto y aromas específicos.'],
            ['name' => 'Pizarroso',           'description' => 'Suelo de pizarra que calienta rápido. Típico de Priorat y zonas de montaña.'],
            ['name' => 'Granítico',           'description' => 'Derivado de granito, ácido y con buen drenaje. Común en Galicia y Bierzo.'],
            ['name' => 'Aluvial',             'description' => 'Sedimentos fluviales, fértil y profundo. Habitual en fondos de valle.'],
            ['name' => 'Arcillo-calcáreo',    'description' => 'Combinación de arcilla y caliza. Frecuente en La Rioja y Navarra.'],
        ];

        foreach ($soilTypes as $data) {
            SoilType::firstOrCreate(['name' => $data['name']], array_merge($data, ['active' => true]));
        }

        // ── Tipos de Riego ────────────────────────────────────────────────────
        $irrigationTypes = [
            ['name' => 'Secano',                  'description' => 'Sin aporte artificial de agua. Depende exclusivamente de la lluvia.'],
            ['name' => 'Goteo',                   'description' => 'Riego localizado en la zona radical. Máxima eficiencia hídrica.'],
            ['name' => 'Aspersión',               'description' => 'Simulación de lluvia mediante aspersores. Uso en heladas y altas temperaturas.'],
            ['name' => 'Microaspersión',          'description' => 'Aspersores de pequeño caudal para humedecer la zona de raíces.'],
            ['name' => 'Inundación / Manta',      'description' => 'Riego por gravedad inundando la parcela. Poco eficiente, en desuso.'],
            ['name' => 'Subterráneo',             'description' => 'Goteros enterrados bajo la superficie. Alta eficiencia y menor evaporación.'],
            ['name' => 'Riego de apoyo',          'description' => 'Riego eventual en situaciones de estrés hídrico severo.'],
        ];

        foreach ($irrigationTypes as $data) {
            IrrigationType::firstOrCreate(['name' => $data['name']], array_merge($data, ['active' => true]));
        }

        // ── Topografía ────────────────────────────────────────────────────────
        $topographies = [
            ['name' => 'Llano',               'description' => 'Pendiente inferior al 2 %. Fácil mecanización.'],
            ['name' => 'Suave pendiente',      'description' => 'Pendiente entre 2 y 8 %. Mecanización accesible.'],
            ['name' => 'Pendiente moderada',   'description' => 'Pendiente entre 8 y 20 %. Mecanización con tractores especiales.'],
            ['name' => 'Fuerte pendiente',     'description' => 'Pendiente superior al 20 %. Cultivo en terraza o manual.'],
            ['name' => 'Ladera',              'description' => 'Vertiente de colina o montaña. Exposición solar variable según orientación.'],
            ['name' => 'Terraza fluvial',      'description' => 'Bancal natural junto a un río. Suelos aluviales y buen drenaje lateral.'],
            ['name' => 'Terraza construida',   'description' => 'Bancal artificial con muros de contención. Viticultura heroica.'],
            ['name' => 'Meseta',              'description' => 'Superficie elevada y extensa con pendiente mínima.'],
            ['name' => 'Fondo de valle',       'description' => 'Parte baja entre colinas. Mayor humedad y riesgo de heladas tardías.'],
            ['name' => 'Cerro / Cima',        'description' => 'Parte alta de una elevación. Buena ventilación y exposición.'],
        ];

        foreach ($topographies as $data) {
            Topography::firstOrCreate(['name' => $data['name']], array_merge($data, ['active' => true]));
        }

        // ── Tipos de Propiedad ────────────────────────────────────────────────
        $propertyTypes = [
            ['name' => 'Propiedad'],
            ['name' => 'Arrendamiento'],
            ['name' => 'Aparcería'],
            ['name' => 'Cesión de uso'],
            ['name' => 'Usufructo'],
            ['name' => 'Comunal'],
            ['name' => 'Otros'],
        ];

        foreach ($propertyTypes as $data) {
            PropertyType::firstOrCreate(['name' => $data['name']], array_merge($data, ['active' => true]));
        }

        // ── Valles / Zonas vitícolas (principales DO españolas) ───────────────
        $valleys = [
            ['code' => 'RIO',  'name' => 'Rioja Alta',             'description_valley' => 'Zona occidental de La Rioja. Suelos arcillo-calcáreos y clima atlántico-continental.'],
            ['code' => 'ROC',  'name' => 'Rioja Alavesa',          'description_valley' => 'Vertiente sur de la Sierra Cantábrica. Terraza sobre el Ebro.'],
            ['code' => 'ROR',  'name' => 'Rioja Oriental',         'description_valley' => 'Zona más cálida y árida de La Rioja. Predominan variedades Garnacha y Mazuelo.'],
            ['code' => 'RDD',  'name' => 'Ribera del Duero',       'description_valley' => 'Altiplano sobre el río Duero. Temperaturas extremas y Tempranillo como variedad principal.'],
            ['code' => 'RGU',  'name' => 'Rías Baixas',            'description_valley' => 'Zona atlántica gallega. Albariño sobre suelos graníticos.'],
            ['code' => 'PRI',  'name' => 'Priorat',                'description_valley' => 'Suelos de pizarra (llicorella) en terraza. Garnacha y Cariñena.'],
            ['code' => 'PEN',  'name' => 'Penedès',                'description_valley' => 'Tres altitudes diferenciadas. Base del Cava y variedades internacionales.'],
            ['code' => 'DUE',  'name' => 'Rueda',                  'description_valley' => 'Meseta castellana sobre el Duero. Verdejo como variedad estrella.'],
            ['code' => 'TOK',  'name' => 'Toro',                   'description_valley' => 'Meseta semiárida occidental. Tinta de Toro (Tempranillo) sobre suelos arenosos.'],
            ['code' => 'NAV',  'name' => 'Navarra',                'description_valley' => 'Diversidad climática entre Pirineos y el Ebro. Garnacha y Tempranillo.'],
            ['code' => 'SOB',  'name' => 'Somontano',              'description_valley' => 'Zona prepirenaica oscense. Variedades locales e internacionales.'],
            ['code' => 'JER',  'name' => 'Jerez-Xérès-Sherry',    'description_valley' => 'Marco de Jerez sobre albarizas. Palomino Fino y sistema de criaderas.'],
            ['code' => 'MAN',  'name' => 'La Mancha',              'description_valley' => 'Mayor región vitícola del mundo. Meseta castellana con clima continental extremo.'],
            ['code' => 'VAL',  'name' => 'Valencia',               'description_valley' => 'Zonas de levante con influencia mediterránea.'],
            ['code' => 'JUM',  'name' => 'Jumilla',                'description_valley' => 'Altiplano murciano. Monastrell sobre suelos calizos en secano.'],
            ['code' => 'YEC',  'name' => 'Yecla',                  'description_valley' => 'Enclave montañoso murciano. Monastrell y Garnacha.'],
            ['code' => 'RIB',  'name' => 'Ribeiro',                'description_valley' => 'Confluencia de ríos gallegos. Treixadura, Torrontés sobre suelos graníticos.'],
            ['code' => 'MON',  'name' => 'Monterrei',              'description_valley' => 'Valle del Támega. Zona más cálida de Galicia.'],
            ['code' => 'BIE',  'name' => 'Bierzo',                 'description_valley' => 'Valle protegido leonés. Mencía sobre pizarras y pizarras calizas.'],
            ['code' => 'TXA',  'name' => 'Txakoli de Getaria',     'description_valley' => 'Costa vasca. Hondarrabi Zuri sobre suelos arcillosos.'],
            ['code' => 'MOR',  'name' => 'Moriles-Montilla',       'description_valley' => 'Sur de Córdoba. Pedro Ximénez sobre suelos albarizos.'],
            ['code' => 'MEN',  'name' => 'Menorca',                'description_valley' => 'Isla balear. Clima marítimo con merlot, cabernet y syrah.'],
            ['code' => 'MAL',  'name' => 'Mallorca (Binissalem)',   'description_valley' => 'Interior mallorquín. Manto Negro y Callet como variedades autóctonas.'],
            ['code' => 'CAN',  'name' => 'Canarias (Tacoronte)',    'description_valley' => 'Lavas volcánicas en Tenerife. Listán Negro y Listán Blanco.'],
        ];

        foreach ($valleys as $data) {
            Valley::firstOrCreate(['code' => $data['code']], array_merge($data, ['active' => true]));
        }

        // ── Orientaciones ─────────────────────────────────────────────────────
        $orientations = [
            ['name' => 'Norte',     'abbreviation' => 'N'],
            ['name' => 'Noreste',   'abbreviation' => 'NE'],
            ['name' => 'Este',      'abbreviation' => 'E'],
            ['name' => 'Sureste',   'abbreviation' => 'SE'],
            ['name' => 'Sur',       'abbreviation' => 'S'],
            ['name' => 'Suroeste',  'abbreviation' => 'SO'],
            ['name' => 'Oeste',     'abbreviation' => 'O'],
            ['name' => 'Noroeste',  'abbreviation' => 'NO'],
        ];

        foreach ($orientations as $data) {
            Orientation::firstOrCreate(['abbreviation' => $data['abbreviation']], array_merge($data, ['active' => true]));
        }

        $this->command->info('PlotCatalogSeeder completado: suelos, riegos, topografías, propiedades, valles/zonas y orientaciones.');
    }
}
