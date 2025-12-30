<?php

/**
 * Datos precisos para optimización de regiones vinícolas
 * Fuente: Consejos Reguladores oficiales
 */

return [
    'priorat' => [
        'name' => 'Priorat',
        'category' => 'DOQ',
        'badge_text' => 'DOQ Priorat - Denominación de Origen Calificada',
        'title' => 'Software para Viticultores en Priorat',
        'intro' => 'Gestiona tus viñedos en la <strong>DOQ Priorat</strong> con Agro365. Una de las dos únicas Denominaciones de Origen Calificadas de España. Viticultura heroica en <strong>pendientes extremas</strong> sobre suelo de llicorella.',
        'superficie' => '1.900 hectáreas',
        'bodegas' => '100+ bodegas',
        'viticultores' => '600+ viticultores',
        'produccion' => '5 millones de litros',
        'zonas' => 'Gratallops, Porrera, Poboleda, Torroja, La Morera, La Vilella, Bellmunt, El Lloar, Scala Dei, Masos de Falset, Solanes del Molar',
        'variedades_tintas' => [
            'Garnacha Tinta (40%) - Variedad principal',
            'Cariñena/Samsó (25%)',
            'Cabernet Sauvignon, Syrah, Merlot',
        ],
        'variedades_blancas' => [
            'Garnacha Blanca, Macabeo',
            'Pedro Ximénez, Chenin Blanc',
        ],
        'clima' => '<strong>Mediterráneo con influencia continental:</strong> Veranos muy cálidos y secos, inviernos suaves. Precipitación 400-500mm anuales. Gran amplitud térmica día-noche. Altitud 100-700m.',
        'rendimiento' => '6.000 kg/ha (muy bajo, máxima calidad)',
        'desafios' => [
            [
                'emoji' => '⛰️',
                'titulo' => 'Pendientes Extremas y Terrazas',
                'descripcion' => 'Viñedos en pendientes de hasta 60%. Viticultura heroica en terrazas (costers). Todo el trabajo es manual: poda, vendimia, tratamientos. Imposible mecanizar.',
                'soluciones' => [
                    'Planifica cuadrillas por parcela y pendiente',
                    'Calcula jornadas necesarias según dificultad',
                    'Registro de costes por parcela',
                    'Documentación fotográfica geolocalizada',
                ],
            ],
            [
                'emoji' => '🪨',
                'titulo' => 'Suelo de Llicorella (Pizarra Volcánica)',
                'descripcion' => 'Suelo único de pizarra volcánica que retiene calor y obliga a raíces profundas. Baja fertilidad natural. Requiere manejo específico de nutrición.',
                'soluciones' => [
                    'Registro de análisis de suelo por parcela',
                    'Control de fertilización específica',
                    'Seguimiento de vigor vegetativo',
                    'Historial de enmiendas y correcciones',
                ],
            ],
            [
                'emoji' => '☀️',
                'titulo' => 'Sequía y Estrés Hídrico',
                'descripcion' => 'Veranos muy secos con precipitación mínima. Estrés hídrico controlado para concentración. Riego de apoyo autorizado en casos extremos.',
                'soluciones' => [
                    'Registro de riegos de apoyo autorizados',
                    'Control de concesiones de agua',
                    'Seguimiento de estrés hídrico por parcela',
                    'Alertas meteorológicas de sequía',
                ],
            ],
        ],
    ],
    
    'rias-baixas' => [
        'name' => 'Rías Baixas',
        'category' => 'DO',
        'badge_text' => 'DO Rías Baixas',
        'title' => 'Software para Viticultores en Rías Baixas',
        'intro' => 'Gestiona tus viñedos en la <strong>DO Rías Baixas</strong> con Agro365. La denominación del <strong>Albariño</strong>, el vino blanco atlántico de máxima calidad. Control de humedad, mildiu y cumplimiento del Consejo Regulador.',
        'superficie' => '4.000 hectáreas',
        'bodegas' => '180+ bodegas',
        'viticultores' => '5.500+ viticultores',
        'produccion' => '35 millones de litros',
        'zonas' => 'Val do Salnés, Condado do Tea, O Rosal, Soutomaior, Ribeira do Ulla',
        'variedades_tintas' => [
            'Caiño Tinto, Espadeiro, Loureira Tinta (minoritarias)',
        ],
        'variedades_blancas' => [
            'Albariño (96%) - Variedad principal y emblemática',
            'Treixadura, Loureira Blanca, Caiño Blanco',
        ],
        'clima' => '<strong>Atlántico húmedo:</strong> Lluvias abundantes (1.200-1.600mm anuales). Temperaturas suaves todo el año. Alta humedad relativa. Influencia oceánica directa.',
        'rendimiento' => '10.000 kg/ha',
        'desafios' => [
            [
                'emoji' => '🌧️',
                'titulo' => 'Mildiu por Lluvia Constante',
                'descripcion' => 'La lluvia abundante y humedad alta favorecen el mildiu. Es la enfermedad más problemática. Requiere tratamientos preventivos constantes desde brotación hasta envero.',
                'soluciones' => [
                    'Calendario de tratamientos preventivos',
                    'Registro obligatorio de fitosanitarios (ROPO)',
                    'Alertas de riesgo de mildiu según lluvia',
                    'Control de plazo de seguridad',
                ],
            ],
            [
                'emoji' => '🍄',
                'titulo' => 'Oidio y Botritis',
                'descripcion' => 'Humedad favorece oidio y podredumbre gris (botritis). Especialmente peligroso en maduración. Requiere ventilación del racimo y tratamientos específicos.',
                'soluciones' => [
                    'Registro de deshojados y aclareos',
                    'Control de tratamientos anti-oidio',
                    'Seguimiento de estado sanitario',
                    'Planificación de vendimia según sanidad',
                ],
            ],
            [
                'emoji' => '🍇',
                'titulo' => 'Gestión de Emparrado (Parral)',
                'descripcion' => 'Sistema tradicional de emparrado en altura. Requiere poda específica, atado, y manejo diferente al espaldera. Mayor coste de mano de obra.',
                'soluciones' => [
                    'Registro de trabajos específicos de emparrado',
                    'Control de cuadrillas especializadas',
                    'Cálculo de costes por sistema de conducción',
                    'Planificación de poda y atado',
                ],
            ],
        ],
    ],
    
    'rueda' => [
        'name' => 'Rueda',
        'category' => 'DO',
        'badge_text' => 'DO Rueda',
        'title' => 'Software para Viticultores en Rueda',
        'intro' => 'Gestiona tus viñedos en la <strong>DO Rueda</strong> con Agro365. La denominación del <strong>Verdejo</strong>, el vino blanco de Castilla y León. Control de rendimientos, heladas y cumplimiento del Consejo Regulador.',
        'superficie' => '18.000 hectáreas',
        'bodegas' => '80+ bodegas',
        'viticultores' => '1.500+ viticultores',
        'produccion' => '85 millones de litros',
        'zonas' => 'Provincias de Valladolid, Segovia y Ávila. Municipios: Rueda, La Seca, Serrada, Medina del Campo',
        'variedades_tintas' => [
            'Tempranillo (minoritaria para rosados)',
        ],
        'variedades_blancas' => [
            'Verdejo (85%) - Variedad principal y autóctona',
            'Sauvignon Blanc (10%)',
            'Viura/Macabeo (5%)',
        ],
        'clima' => '<strong>Continental:</strong> Inviernos fríos con heladas, veranos cálidos y secos. Precipitación 300-500mm anuales. Gran amplitud térmica día-noche.',
        'rendimiento' => '12.000 kg/ha',
        'desafios' => [
            [
                'emoji' => '❄️',
                'titulo' => 'Heladas Primaverales',
                'descripcion' => 'Heladas de abril-mayo son el mayor riesgo. Pueden destruir brotes de Verdejo y reducir cosecha drásticamente. Clima continental con heladas tardías frecuentes.',
                'soluciones' => [
                    'Alertas meteorológicas automáticas',
                    'Registro de fechas de brotación',
                    'Planificación de sistemas antiheladas',
                    'Histórico de heladas por parcela SIGPAC',
                ],
            ],
            [
                'emoji' => '🦠',
                'titulo' => 'Mildiu en Primaveras Húmedas',
                'descripcion' => 'Aunque el clima es seco, primaveras húmedas favorecen mildiu. El Verdejo es sensible. Requiere vigilancia y tratamientos preventivos.',
                'soluciones' => [
                    'Base de datos de fitosanitarios autorizados',
                    'Registro obligatorio de tratamientos',
                    'Control de plazo de seguridad',
                    'Alertas de riesgo según lluvia',
                ],
            ],
            [
                'emoji' => '📊',
                'titulo' => 'Control de Rendimientos',
                'descripcion' => 'El Consejo Regulador exige rendimientos máximos de 12.000 kg/ha. Superar este límite descalifica la uva para la DO.',
                'soluciones' => [
                    'Cálculo automático de rendimiento por parcela',
                    'Alertas si te acercas al límite',
                    'Proyección de producción en tiempo real',
                    'Informes para el Consejo Regulador',
                ],
            ],
        ],
    ],
];
