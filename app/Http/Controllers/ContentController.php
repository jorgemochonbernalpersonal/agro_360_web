<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ContentController extends Controller
{
    /**
     * Configuración de páginas de contenido SEO
     * Mapeo de slug -> vista
     */
    protected const CONTENT_PAGES = [
        // Páginas informativas
        'que-es-sigpac' => 'content.que-es-sigpac',
        'cuaderno-campo-digital-2027' => 'content.cuaderno-campo-digital-2027',
        'normativa-pac' => 'content.normativa-pac-2027',
        'digitalizar-viñedo' => 'content.digitalizar-viñedo',
        'comparativa-software-agricola' => 'content.comparativa-software-agricola',
        'software-para-viticultores' => 'content.software-para-viticultores',
        'app-agricultura' => 'content.app-agricultura',
        'cuaderno-digital-viticultores' => 'content.cuaderno-digital-viticultores',

        // Pricing page
        'precios' => 'content.precios',

        // Páginas comerciales (alta prioridad SEO)
        'software-gestion-agricola' => 'content.software-gestion-agricola',
        'software-viticultura' => 'content.software-viticultura',
        'software-bodegas' => 'content.software-bodegas',
        'cuaderno-digital' => 'content.cuaderno-digital',
        'trazabilidad-agricola' => 'content.trazabilidad-agricola',

        // Páginas por sector
        'viticultores' => 'content.viticultores',
        'bodegas' => 'content.bodegas',
        'cooperativas' => 'content.cooperativas',
        'ingenieros-agronomos' => 'content.ingenieros-agronomos',

        // Funcionalidades específicas
        'gestion-vendimia' => 'content.gestion-vendimia',
        'registro-fitosanitarios' => 'content.registro-fitosanitarios',
        'subvenciones-pac' => 'content.subvenciones-pac-2024',
        'control-plagas-viñedo' => 'content.control-plagas-viñedo',
        'facturacion-agricola' => 'content.facturacion-agricola',

        // Media prioridad
        'gestion-cuadrillas-agricolas' => 'content.gestion-cuadrillas-agricolas',
        'maquinaria-agricola-registro' => 'content.maquinaria-agricola-registro',
        'plantaciones-viñedo-variedades' => 'content.plantaciones-viñedo-variedades',
        'rendimientos-cosecha-viñedo' => 'content.rendimientos-cosecha-viñedo',
        'informes-oficiales-agricultura' => 'content.informes-oficiales-agricultura',

        // Contenido técnico
        'ndvi-viñedo-teledeteccion' => 'content.ndvi-viñedo-teledeteccion',
        'calendario-viticola' => 'content.calendario-viticola',
        'trazabilidad-vino-origen' => 'content.trazabilidad-vino-origen',
        'firma-digital-agricultura' => 'content.firma-digital-agricultura',
        'gestion-campañas-agricolas' => 'content.gestion-campañas-agricolas',

        // Páginas regionales por DO — archivos propios (legado)
        'software-viticultores-rioja'        => 'content.software-viticultores-rioja',
        'software-viticultores-ribera-duero' => 'content.software-viticultores-ribera-duero',
        'software-viticultores-rueda'        => 'content.software-viticultores-rueda',
        'software-viticultores-penedes'      => 'content.software-viticultores-penedes',
        'software-viticultores-la-mancha'    => 'content.software-viticultores-la-mancha',
        'software-viticultores-priorat'      => 'content.software-viticultores-priorat',
        'software-viticultores-rias-baixas'  => 'content.software-viticultores-rias-baixas',
        'software-viticultores-toro'         => 'content.software-viticultores-toro',
        'software-viticultores-jumilla'      => 'content.software-viticultores-jumilla',

        // Páginas regionales por DO — vista compartida con datos inyectados
        'software-viticultores-jerez'           => 'content.software-viticultores-region',
        'software-viticultores-cava'            => 'content.software-viticultores-region',
        'software-viticultores-valdepenas'      => 'content.software-viticultores-region',
        'software-viticultores-navarra'         => 'content.software-viticultores-region',
        'software-viticultores-somontano'       => 'content.software-viticultores-region',
        'software-viticultores-utiel-requena'   => 'content.software-viticultores-region',
        'software-viticultores-yecla'           => 'content.software-viticultores-region',
        'software-viticultores-bullas'          => 'content.software-viticultores-region',
        'software-viticultores-monterrei'       => 'content.software-viticultores-region',
        'software-viticultores-bierzo'          => 'content.software-viticultores-region',
        'software-viticultores-ribeiro'         => 'content.software-viticultores-region',
        'software-viticultores-cigales'         => 'content.software-viticultores-region',
        'software-viticultores-calatayud'       => 'content.software-viticultores-region',
        'software-viticultores-campo-de-borja'  => 'content.software-viticultores-region',
        'software-viticultores-carinena'        => 'content.software-viticultores-region',
        'software-viticultores-malaga'          => 'content.software-viticultores-region',
        'software-viticultores-montilla-moriles'=> 'content.software-viticultores-region',
        'software-viticultores-terra-alta'      => 'content.software-viticultores-region',
        'software-viticultores-costers-del-segre' => 'content.software-viticultores-region',
    ];

    /**
     * Datos específicos por DO para la vista compartida de regiones
     */
    protected const REGION_DATA = [
        'software-viticultores-jerez' => [
            'name'        => 'DO Jerez-Xérès-Sherry',
            'short'       => 'Jerez',
            'do_type'     => 'DO',
            'province'    => 'Cádiz, Andalucía',
            'badge'       => 'amber',
            'hectares'    => '10.000',
            'viticultores'=> '2.500+',
            'bodegas'     => '40+',
            'varieties'   => ['Palomino Fino (95%)', 'Pedro Ximénez', 'Moscatel'],
            'climate'     => 'Atlántico-mediterráneo. Veranos muy cálidos y secos. El poniente seco y el levante húmedo marcan el ciclo del viñedo. Albarizas, arenas y barros como suelos principales.',
            'challenge'   => 'Control de rendimientos para producción de vinos generosos (Fino, Amontillado, Oloroso) y cumplimiento del sistema de envejecimiento biológico en soleras y criaderas.',
            'rendimiento' => '80 hl/ha',
            'keywords'    => 'software viticultores jerez, cuaderno campo jerez, palomino fino jerez, vinos generosos DO jerez',
            'description' => 'Software para viticultores de DO Jerez. Gestión de Palomino Fino, Pedro Ximénez y Moscatel. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento del Consejo Regulador de Jerez.',
        ],
        'software-viticultores-cava' => [
            'name'        => 'DO Cava',
            'short'       => 'Cava',
            'do_type'     => 'DO',
            'province'    => 'Cataluña, Aragón, Rioja, País Vasco, Extremadura, Valencia',
            'badge'       => 'yellow',
            'hectares'    => '37.000',
            'viticultores'=> '6.000+',
            'bodegas'     => '300+',
            'varieties'   => ['Macabeo', 'Xarel·lo', 'Parellada', 'Chardonnay', 'Pinot Noir', 'Garnacha'],
            'climate'     => 'Variable según zona. El Penedès concentra el 95% de la producción: mediterráneo suave, veranos cálidos con influencia marina y brisas frescas nocturnas.',
            'challenge'   => 'Trazabilidad de la segunda fermentación en botella y cumplimiento de los tiempos mínimos de crianza sobre lías (9 meses joven, 18 meses reserva, 30 meses gran reserva).',
            'rendimiento' => '12.000 kg/ha',
            'keywords'    => 'software viticultores cava, cuaderno campo cava, macabeo xarelo parellada, DO cava penedes',
            'description' => 'Software para viticultores de DO Cava. Gestión de Macabeo, Xarel·lo y Parellada. Cuaderno digital, teledetección NDVI, Verifactu y trazabilidad de la segunda fermentación en botella.',
        ],
        'software-viticultores-valdepenas' => [
            'name'        => 'DO Valdepeñas',
            'short'       => 'Valdepeñas',
            'do_type'     => 'DO',
            'province'    => 'Ciudad Real, Castilla-La Mancha',
            'badge'       => 'red',
            'hectares'    => '23.000',
            'viticultores'=> '3.500+',
            'bodegas'     => '30+',
            'varieties'   => ['Cencibel / Tempranillo (85%)', 'Airén', 'Cabernet Sauvignon', 'Merlot'],
            'climate'     => 'Continental extremo. Inviernos muy fríos con heladas frecuentes hasta mayo. Veranos muy calurosos con máximas superiores a 40°C. Escasa pluviometría (350 mm/año).',
            'challenge'   => 'Gestión de heladas tardías de primavera sobre Cencibel y control del estrés hídrico en verano, con parcelas mayoritariamente en secano en suelos arcillo-calcáreos.',
            'rendimiento' => '10.000 kg/ha tintas · 12.000 kg/ha blancas',
            'keywords'    => 'software viticultores valdepeñas, cuaderno campo valdepeñas, cencibel valdepeñas, DO valdepeñas ciudad real',
            'description' => 'Software para viticultores de DO Valdepeñas. Gestión de Cencibel y Airén en Ciudad Real. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento normativo.',
        ],
        'software-viticultores-navarra' => [
            'name'        => 'DO Navarra',
            'short'       => 'Navarra',
            'do_type'     => 'DO',
            'province'    => 'Navarra',
            'badge'       => 'red',
            'hectares'    => '9.500',
            'viticultores'=> '2.000+',
            'bodegas'     => '70+',
            'varieties'   => ['Garnacha (35%)', 'Tempranillo (30%)', 'Cabernet Sauvignon', 'Merlot', 'Chardonnay', 'Moscatel de Grano Menudo'],
            'climate'     => 'Transición entre atlántico y mediterráneo según la zona. Ribera (sur) muy seca y calurosa; Tierra Estella y Valdizarbe más húmedas con riesgo de heladas y granizo.',
            'challenge'   => 'Enorme diversidad de zonas (Baja Montaña, Valdizarbe, Tierra Estella, Ribera Alta, Ribera Baja) con regímenes climáticos distintos que requieren gestión diferenciada por parcela.',
            'rendimiento' => '10.000 kg/ha tintas · 12.000 kg/ha blancas',
            'keywords'    => 'software viticultores navarra, cuaderno campo navarra, garnacha navarra, DO navarra',
            'description' => 'Software para viticultores de DO Navarra. Gestión de Garnacha, Tempranillo y Moscatel. Cuaderno digital, teledetección NDVI, Verifactu y gestión adaptada a las cinco zonas navarras.',
        ],
        'software-viticultores-somontano' => [
            'name'        => 'DO Somontano',
            'short'       => 'Somontano',
            'do_type'     => 'DO',
            'province'    => 'Huesca, Aragón',
            'badge'       => 'orange',
            'hectares'    => '4.700',
            'viticultores'=> '500+',
            'bodegas'     => '30+',
            'varieties'   => ['Cabernet Sauvignon', 'Merlot', 'Tempranillo', 'Syrah', 'Chardonnay', 'Gewurztraminer', 'Moristel', 'Parraleta'],
            'climate'     => 'Prepirenaico. Veranos cálidos con noches frescas por altitud (350-700 m). Inviernos fríos. Cierzo como viento dominante. Riesgo de heladas en primavera tardía.',
            'challenge'   => 'Gestión de la diversidad varietal (variedades internacionales + autóctonas Moristel y Parraleta) y adaptación a los cambios de temperatura altitudinales entre parcelas.',
            'rendimiento' => '9.000 kg/ha tintas · 10.000 kg/ha blancas',
            'keywords'    => 'software viticultores somontano, cuaderno campo somontano, cabernet somontano, DO somontano huesca',
            'description' => 'Software para viticultores de DO Somontano. Gestión de variedades internacionales y autóctonas en Huesca. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento normativo.',
        ],
        'software-viticultores-utiel-requena' => [
            'name'        => 'DO Utiel-Requena',
            'short'       => 'Utiel-Requena',
            'do_type'     => 'DO',
            'province'    => 'Valencia',
            'badge'       => 'blue',
            'hectares'    => '35.000',
            'viticultores'=> '5.000+',
            'bodegas'     => '80+',
            'varieties'   => ['Bobal (80%)', 'Tempranillo', 'Garnacha', 'Cabernet Sauvignon', 'Merseguera', 'Planta Nova'],
            'climate'     => 'Continental con influencia mediterránea. Altitud media 750-900 m. Inviernos fríos, veranos calurosos pero con noches frescas. Precipitación de 400-450 mm.',
            'challenge'   => 'Optimización de la variedad Bobal, tardía en maduración y sensible al mildiu, en parcelas de alta altitud con ciclos térmicos día-noche muy marcados.',
            'rendimiento' => '10.000 kg/ha',
            'keywords'    => 'software viticultores utiel requena, cuaderno campo utiel requena, bobal utiel, DO utiel-requena valencia',
            'description' => 'Software para viticultores de DO Utiel-Requena. Especializado en Bobal, la variedad estrella valenciana. Cuaderno digital, teledetección NDVI, Verifactu y gestión PAC.',
        ],
        'software-viticultores-yecla' => [
            'name'        => 'DO Yecla',
            'short'       => 'Yecla',
            'do_type'     => 'DO',
            'province'    => 'Murcia',
            'badge'       => 'red',
            'hectares'    => '6.500',
            'viticultores'=> '800+',
            'bodegas'     => '10+',
            'varieties'   => ['Monastrell (85%)', 'Garnacha Tintorera', 'Tempranillo', 'Cabernet Sauvignon', 'Verdejo', 'Macabeo'],
            'climate'     => 'Semiárido continental. Veranos muy calurosos (>40°C), inviernos fríos. Precipitación inferior a 300 mm. Altitud 600-900 m. Suelos pardos calizos con escasa materia orgánica.',
            'challenge'   => 'Gestión del Monastrell en condiciones de extrema aridez y temperaturas elevadas, con control riguroso de tratamientos en el marco de prácticas de agricultura sostenible.',
            'rendimiento' => '7.000 kg/ha tintas · 10.000 kg/ha blancas',
            'keywords'    => 'software viticultores yecla, cuaderno campo yecla, monastrell yecla, DO yecla murcia',
            'description' => 'Software para viticultores de DO Yecla. Especializado en Monastrell en Murcia. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento del Consejo Regulador de Yecla.',
        ],
        'software-viticultores-bullas' => [
            'name'        => 'DO Bullas',
            'short'       => 'Bullas',
            'do_type'     => 'DO',
            'province'    => 'Murcia',
            'badge'       => 'red',
            'hectares'    => '2.800',
            'viticultores'=> '600+',
            'bodegas'     => '12+',
            'varieties'   => ['Monastrell (90%)', 'Tempranillo', 'Cabernet Sauvignon', 'Syrah', 'Macabeo', 'Airén'],
            'climate'     => 'Semiárido de alta altitud (600-1.000 m). La altitud modera las temperaturas extremas. Precipitación entre 300-400 mm. Suelos calizos y pedregosos bien drenados.',
            'challenge'   => 'La variedad Monastrell requiere control estricto de los tratamientos fitosanitarios y del estrés hídrico. La altitud alarga el ciclo vegetativo, retrasando la vendimia.',
            'rendimiento' => '7.000 kg/ha',
            'keywords'    => 'software viticultores bullas, cuaderno campo bullas, monastrell bullas, DO bullas murcia',
            'description' => 'Software para viticultores de DO Bullas. Gestión de Monastrell en alta altitud en Murcia. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento normativo DO Bullas.',
        ],
        'software-viticultores-monterrei' => [
            'name'        => 'DO Monterrei',
            'short'       => 'Monterrei',
            'do_type'     => 'DO',
            'province'    => 'Ourense, Galicia',
            'badge'       => 'green',
            'hectares'    => '800',
            'viticultores'=> '400+',
            'bodegas'     => '20+',
            'varieties'   => ['Mencía', 'Bastardo', 'Arauxa (Tempranillo)', 'Godello', 'Treixadura', 'Doña Blanca'],
            'climate'     => 'Continental con influencia atlántica moderada. Valle del Támega encajonado que crea un microclima más seco y cálido que el resto de Galicia. Precipitación 700-900 mm.',
            'challenge'   => 'La humedad atlántica en primavera favorece el mildiu y la botritis. La gestión preventiva de fitosanitarios es crítica, especialmente en las variedades tintas.',
            'rendimiento' => '10.000 kg/ha blancas · 8.000 kg/ha tintas',
            'keywords'    => 'software viticultores monterrei, cuaderno campo monterrei, mencía monterrei, godello ourense, DO monterrei',
            'description' => 'Software para viticultores de DO Monterrei. Gestión de Mencía y Godello en Ourense. Cuaderno digital, teledetección NDVI, Verifactu y control fitosanitario para el clima atlántico gallego.',
        ],
        'software-viticultores-bierzo' => [
            'name'        => 'DO Bierzo',
            'short'       => 'Bierzo',
            'do_type'     => 'DO',
            'province'    => 'León, Castilla y León',
            'badge'       => 'green',
            'hectares'    => '3.000',
            'viticultores'=> '1.200+',
            'bodegas'     => '70+',
            'varieties'   => ['Mencía (70%)', 'Garnacha Tintorera', 'Godello', 'Doña Blanca', 'Palomino'],
            'climate'     => 'Atlántico-continental de transición. Protegido por los Montes Aquilanos y Ancares. Más húmedo y fresco que Castilla. Suelos de pizarras en laderas y aluviales en el fondo de valle.',
            'challenge'   => 'Gestión de viñas viejas de Mencía en ladera (vendimia manual, baja mecanización) y control de la humedad en zonas bajas para evitar botritis en racimos.',
            'rendimiento' => '8.000 kg/ha tintas · 10.500 kg/ha blancas',
            'keywords'    => 'software viticultores bierzo, cuaderno campo bierzo, mencía bierzo, godello bierzo, DO bierzo',
            'description' => 'Software para viticultores de DO Bierzo. Especializado en Mencía y Godello en León. Cuaderno digital, teledetección NDVI, Verifactu y gestión de parcelas SIGPAC en ladera.',
        ],
        'software-viticultores-ribeiro' => [
            'name'        => 'DO Ribeiro',
            'short'       => 'Ribeiro',
            'do_type'     => 'DO',
            'province'    => 'Ourense, Galicia',
            'badge'       => 'green',
            'hectares'    => '2.700',
            'viticultores'=> '5.500+',
            'bodegas'     => '100+',
            'varieties'   => ['Treixadura (40%)', 'Torrontés', 'Godello', 'Loureira', 'Sousón', 'Caiño Tinto', 'Brancellao'],
            'climate'     => 'Atlántico con influencia del Miño. Inviernos suaves, veranos cálidos y secos en el interior del valle. Precipitación 800-1.000 mm concentrada en otoño-invierno.',
            'challenge'   => 'Minifundismo extremo (parcelas muy pequeñas) y alta humedad primaveral que favorece el mildiu y la podredumbre. Gestión de muchas parcelas dispersas con cuaderno digital.',
            'rendimiento' => '11.000 kg/ha blancas · 9.000 kg/ha tintas',
            'keywords'    => 'software viticultores ribeiro, cuaderno campo ribeiro, treixadura ribeiro, DO ribeiro ourense',
            'description' => 'Software para viticultores de DO Ribeiro. Gestión de Treixadura, Torrontés y Godello en Ourense. Cuaderno digital, teledetección NDVI, Verifactu y gestión de minifundios dispersos.',
        ],
        'software-viticultores-cigales' => [
            'name'        => 'DO Cigales',
            'short'       => 'Cigales',
            'do_type'     => 'DO',
            'province'    => 'Valladolid y Palencia, Castilla y León',
            'badge'       => 'red',
            'hectares'    => '2.500',
            'viticultores'=> '600+',
            'bodegas'     => '40+',
            'varieties'   => ['Tinto Fino / Tempranillo (80%)', 'Garnacha', 'Albillo', 'Verdejo', 'Viura'],
            'climate'     => 'Continental severo. Altitud media 750-800 m sobre la meseta castellana. Inviernos muy fríos con heladas tardías frecuentes. Veranos cortos y calurosos. Escasas lluvias (350-450 mm).',
            'challenge'   => 'Las heladas tardías de mayo en la meseta castellana son el principal riesgo para el Tinto Fino. Control de los rendimientos para mantener la calidad característica de los rosados de Cigales.',
            'rendimiento' => '9.000 kg/ha',
            'keywords'    => 'software viticultores cigales, cuaderno campo cigales, tinto fino cigales, rosados cigales, DO cigales valladolid',
            'description' => 'Software para viticultores de DO Cigales. Gestión de Tinto Fino en Valladolid y Palencia. Cuaderno digital, teledetección NDVI, Verifactu y control de rendimientos.',
        ],
        'software-viticultores-calatayud' => [
            'name'        => 'DO Calatayud',
            'short'       => 'Calatayud',
            'do_type'     => 'DO',
            'province'    => 'Zaragoza, Aragón',
            'badge'       => 'purple',
            'hectares'    => '6.000',
            'viticultores'=> '1.000+',
            'bodegas'     => '20+',
            'varieties'   => ['Garnacha (85%)', 'Tempranillo', 'Mazuela', 'Cabernet Sauvignon', 'Monastrell', 'Macabeo', 'Chardonnay', 'Malvasía'],
            'climate'     => 'Continental árido. Altitud entre 550-900 m en el valle del Jalón. Cierzo intenso. Inviernos muy fríos, veranos calurosos. Precipitación inferior a 350 mm. Suelos pedregosos calizos.',
            'challenge'   => 'Viñas viejas de Garnacha (muchas superiores a 50 años) con rendimientos muy bajos. El Cierzo puede provocar daños mecánicos en los brotes jóvenes durante la primavera.',
            'rendimiento' => '8.000 kg/ha tintas · 10.000 kg/ha blancas',
            'keywords'    => 'software viticultores calatayud, cuaderno campo calatayud, garnacha calatayud, DO calatayud zaragoza',
            'description' => 'Software para viticultores de DO Calatayud. Gestión de Garnacha en viñas viejas de Zaragoza. Cuaderno digital, teledetección NDVI, Verifactu y control de rendimientos.',
        ],
        'software-viticultores-campo-de-borja' => [
            'name'        => 'DO Campo de Borja',
            'short'       => 'Campo de Borja',
            'do_type'     => 'DO',
            'province'    => 'Zaragoza, Aragón',
            'badge'       => 'purple',
            'hectares'    => '7.000',
            'viticultores'=> '800+',
            'bodegas'     => '16+',
            'varieties'   => ['Garnacha (70%)', 'Tempranillo', 'Mazuela', 'Cabernet Sauvignon', 'Merlot', 'Macabeo', 'Chardonnay'],
            'climate'     => 'Continental semiárido. Depresión del Ebro con el Moncayo como referencia orográfica. El Cierzo barre la zona con frecuencia, reduciendo humedad. Precipitación 350-450 mm.',
            'challenge'   => 'El Cierzo puede dañar los brotes en brotación y provocar desecación de racimos en maduración. Gestión preventiva clave para proteger la Garnacha en sus diferentes altitudes.',
            'rendimiento' => '9.000 kg/ha tintas · 11.000 kg/ha blancas',
            'keywords'    => 'software viticultores campo de borja, cuaderno campo borja, garnacha campo borja, DO campo de borja zaragoza',
            'description' => 'Software para viticultores de DO Campo de Borja. Gestión de Garnacha en Zaragoza. Cuaderno digital, teledetección NDVI, Verifactu y control normativo del Consejo Regulador.',
        ],
        'software-viticultores-carinena' => [
            'name'        => 'DO Cariñena',
            'short'       => 'Cariñena',
            'do_type'     => 'DO',
            'province'    => 'Zaragoza, Aragón',
            'badge'       => 'purple',
            'hectares'    => '14.500',
            'viticultores'=> '1.800+',
            'bodegas'     => '40+',
            'varieties'   => ['Garnacha (40%)', 'Tempranillo', 'Cariñena / Mazuela', 'Cabernet Sauvignon', 'Syrah', 'Macabeo', 'Chardonnay', 'Moscatel Romano'],
            'climate'     => 'Continental árido. La comarca del Campo de Cariñena en la depresión del Ebro. Cierzo muy frecuente e intenso. Precipitación 300-400 mm. Veranos tórridos, inviernos fríos.',
            'challenge'   => 'Recuperación y conservación de la variedad autóctona Cariñena (Mazuela), muy sensible al oidio, junto con la gestión de altas temperaturas estivales que pueden acelerar la maduración.',
            'rendimiento' => '10.000 kg/ha tintas · 12.000 kg/ha blancas',
            'keywords'    => 'software viticultores cariñena, cuaderno campo cariñena, garnacha cariñena, mazuela zaragoza, DO cariñena',
            'description' => 'Software para viticultores de DO Cariñena. Gestión de Garnacha y Cariñena en Zaragoza. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento del Consejo Regulador.',
        ],
        'software-viticultores-malaga' => [
            'name'        => 'DO Málaga',
            'short'       => 'Málaga',
            'do_type'     => 'DO',
            'province'    => 'Málaga, Andalucía',
            'badge'       => 'amber',
            'hectares'    => '1.200',
            'viticultores'=> '700+',
            'bodegas'     => '30+',
            'varieties'   => ['Pedro Ximénez (65%)', 'Moscatel de Alejandría (30%)', 'Romé', 'Doradilla'],
            'climate'     => 'Mediterráneo cálido. Veranos muy secos y calurosos. Inviernos suaves. La Axarquía presenta condiciones semiáridas con elevada insolación. Precipitación inferior a 500 mm.',
            'challenge'   => 'La producción de pasas (uva sobremadurada para Pedro Ximénez) requiere control preciso del ciclo de maduración y gestión del soleo. Las parcelas en ladera dificultan la mecanización.',
            'rendimiento' => '8.000 kg/ha',
            'keywords'    => 'software viticultores málaga, cuaderno campo málaga, pedro ximénez málaga, moscatel málaga, DO málaga axarquía',
            'description' => 'Software para viticultores de DO Málaga. Gestión de Pedro Ximénez y Moscatel en la Axarquía malagueña. Cuaderno digital, teledetección NDVI, Verifactu y trazabilidad para vinos generosos.',
        ],
        'software-viticultores-montilla-moriles' => [
            'name'        => 'DO Montilla-Moriles',
            'short'       => 'Montilla-Moriles',
            'do_type'     => 'DO',
            'province'    => 'Córdoba, Andalucía',
            'badge'       => 'amber',
            'hectares'    => '6.500',
            'viticultores'=> '2.500+',
            'bodegas'     => '70+',
            'varieties'   => ['Pedro Ximénez (70%)', 'Airén', 'Baladí Verdejo', 'Moscatel', 'Torrontés'],
            'climate'     => 'Mediterráneo continental. Veranos extremos con temperaturas superiores a 40°C frecuentes. Precipitación inferior a 400 mm. Albarizas en la zona del Superior, tierras en zonas bajas.',
            'challenge'   => 'El Pedro Ximénez acumula azúcares muy rápidamente en verano. El timing de vendimia es crítico: días de diferencia pueden cambiar el destino de la uva (vino joven vs. generoso).',
            'rendimiento' => '11.000 kg/ha',
            'keywords'    => 'software viticultores montilla moriles, cuaderno campo montilla, pedro ximénez montilla, DO montilla-moriles córdoba',
            'description' => 'Software para viticultores de DO Montilla-Moriles. Gestión de Pedro Ximénez en Córdoba. Cuaderno digital, teledetección NDVI, Verifactu y control de vendimia para vinos generosos.',
        ],
        'software-viticultores-terra-alta' => [
            'name'        => 'DO Terra Alta',
            'short'       => 'Terra Alta',
            'do_type'     => 'DO',
            'province'    => 'Tarragona, Cataluña',
            'badge'       => 'green',
            'hectares'    => '5.700',
            'viticultores'=> '1.200+',
            'bodegas'     => '45+',
            'varieties'   => ['Garnacha Blanca (35%)', 'Garnacha Tinta', 'Macabeo', 'Cariñena', 'Syrah', 'Cabernet Sauvignon', 'Parellada'],
            'climate'     => 'Continental árido con influencia mediterránea. Altitud 400-700 m. El Cierzo y el Serè son los vientos dominantes. Veranos secos, inviernos fríos. Precipitación 400-500 mm.',
            'challenge'   => 'La Garnacha Blanca, variedad insignia de la DO, es sensible a la oxidación. Gestión de la maduración precisa y control de temperatura en depósito son claves para su calidad.',
            'rendimiento' => '10.000 kg/ha blancas · 8.000 kg/ha tintas',
            'keywords'    => 'software viticultores terra alta, cuaderno campo terra alta, garnacha blanca terra alta, DO terra alta tarragona',
            'description' => 'Software para viticultores de DO Terra Alta. Gestión de Garnacha Blanca y Garnacha Tinta en Tarragona. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento normativo.',
        ],
        'software-viticultores-costers-del-segre' => [
            'name'        => 'DO Costers del Segre',
            'short'       => 'Costers del Segre',
            'do_type'     => 'DO',
            'province'    => 'Lleida, Cataluña',
            'badge'       => 'green',
            'hectares'    => '4.600',
            'viticultores'=> '600+',
            'bodegas'     => '25+',
            'varieties'   => ['Tempranillo', 'Cabernet Sauvignon', 'Merlot', 'Garnacha', 'Monastrell', 'Chardonnay', 'Macabeo', 'Riesling', 'Gewurztraminer'],
            'climate'     => 'Continental árido. La zona más seca de Cataluña (250-400 mm). Veranos muy calurosos, inviernos fríos. Alta variabilidad altitudinal (200-900 m) con microclimas muy distintos.',
            'challenge'   => 'La escasez hídrica es el principal limitante. La mayoría de viñedos requieren riego de apoyo con concesiones controladas. Gestión eficiente del agua y registro obligatorio de riegos.',
            'rendimiento' => '10.000 kg/ha',
            'keywords'    => 'software viticultores costers del segre, cuaderno campo lleida, cabernet lleida, DO costers del segre',
            'description' => 'Software para viticultores de DO Costers del Segre. Gestión de variedades en Lleida. Cuaderno digital, teledetección NDVI, Verifactu y control de riego en la DO más árida de Cataluña.',
        ],
    ];

    /**
     * Mostrar página de contenido
     */
    public function show(string $slug)
    {
        if (!isset(self::CONTENT_PAGES[$slug])) {
            abort(404);
        }

        $view = self::CONTENT_PAGES[$slug];

        if (!View::exists($view)) {
            abort(404);
        }

        if ($view === 'content.software-viticultores-region') {
            $region = self::REGION_DATA[$slug] ?? null;
            if (!$region) abort(404);
            return view($view, ['region' => $region, 'slug' => $slug]);
        }

        return view($view);
    }

    /**
     * Obtener todos los slugs válidos (útil para generar rutas)
     */
    public static function getAllSlugs(): array
    {
        return array_keys(self::CONTENT_PAGES);
    }

    /**
     * Verificar si un slug es válido
     */
    public static function isValidSlug(string $slug): bool
    {
        return isset(self::CONTENT_PAGES[$slug]);
    }
}
