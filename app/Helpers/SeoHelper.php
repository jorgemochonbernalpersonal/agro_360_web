<?php

namespace App\Helpers;

class SeoHelper
{
    /**
     * Generar schema JSON-LD para BreadcrumbList
     * 
     * @param array $items Array de items con 'name' y 'url'
     * @return string JSON encoded schema
     */
    public static function breadcrumbSchema(array $items): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];
        
        $position = 1;
        foreach ($items as $item) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'item' => $item['url']
            ];
        }
        
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Obtener meta description específica por ruta
     * 
     * @param string $path Ruta actual
     * @param string|null $customDescription Descripción personalizada (opcional)
     * @return string Meta description
     */
    public static function getMetaDescription(string $path, ?string $customDescription = null): string
    {
        if ($customDescription) {
            return $customDescription;
        }

        // ✅ SEO: Meta descriptions optimizadas (150-160 caracteres ideal)
        $descriptions = [
            '/' => 'Software de gestión agrícola profesional para viticultores y bodegas. Cuaderno de campo digital obligatorio 2027, SIGPAC, control de parcelas, informes oficiales. 6 meses gratis.',
            '/faqs' => 'Respuestas a preguntas frecuentes sobre Agro365: informes oficiales, SIGPAC, cuaderno digital, precios y más. Todo lo que necesitas saber sobre nuestro software agrícola.',
            '/que-es-sigpac' => 'Guía completa sobre SIGPAC: qué es, cómo funciona y cómo gestionar parcelas agrícolas con códigos SIGPAC. Integración completa con Agro365 para viticultores.',
            '/cuaderno-campo-digital-2027' => 'Cuaderno de campo digital obligatorio desde 2027. Normativa europea, requisitos legales y cómo cumplir con Agro365. Guía completa para viticultores en España.',
            '/normativa-pac' => 'Normativa PAC 2027: cambios, requisitos y cumplimiento. Cómo Agro365 te ayuda a cumplir con todas las normativas PAC automáticamente. Software para viticultores.',
            '/digitalizar-viñedo' => 'Guía completa para digitalizar tu viñedo: pasos, beneficios y herramientas. Software de gestión agrícola para viticultores profesionales. Prueba gratis 6 meses.',
            '/comparativa-software-agricola' => 'Comparativa de software agrícola para viñedos. Compara Agro365 con otras soluciones. Cuaderno digital, SIGPAC, informes oficiales y más. Análisis completo.',
            '/software-para-viticultores' => 'Software profesional para viticultores en España. Gestión completa de viñedos, cuaderno digital, SIGPAC, control de vendimia y cumplimiento normativo. Prueba gratis.',
            '/app-agricultura' => 'App de agricultura digital para gestionar explotaciones agrícolas. Cuaderno de campo digital, SIGPAC, control de parcelas y cumplimiento normativo. Prueba gratis 6 meses.',
            '/cuaderno-digital-viticultores' => 'Cuaderno de campo digital para viticultores obligatorio desde 2027. Gestión de tratamientos, SIGPAC, cumplimiento normativo y informes oficiales. Prueba gratis.',
            '/gestion-vendimia' => 'Gestión de vendimia digital para viticultores. Control de cosechas, contenedores, rendimientos y trazabilidad completa. Software profesional para bodegas.',
            '/registro-fitosanitarios' => 'Registro de productos fitosanitarios para viticultores. Gestión completa de tratamientos, dosis, plazos de seguridad y cumplimiento normativo.',
            '/subvenciones-pac-2024' => 'Subvenciones PAC 2024: guía completa para viticultores. Requisitos, plazos y cómo cumplir con Agro365. Software de gestión agrícola profesional.',
            '/control-plagas-viñedo' => 'Control de plagas en viñedo: gestión digital de tratamientos fitosanitarios, calendario de aplicaciones y cumplimiento normativo. Software para viticultores.',
            '/facturacion-agricola' => 'Facturación agrícola integrada para viticultores. Gestión de clientes, facturas, albaranes y control de stock. Software completo de gestión agrícola.',
            '/quienes-somos' => 'Conoce más sobre Agro365, la plataforma de gestión agrícola profesional para viticultores y bodegas en España. Software de cuaderno digital y SIGPAC.',
            '/blog' => 'Artículos, noticias y guías sobre gestión agrícola, viticultura, SIGPAC y digitalización del campo. Blog de Agro365 para viticultores profesionales.',
            '/tutoriales' => 'Tutoriales paso a paso para usar Agro365: cuaderno digital, SIGPAC, informes oficiales y más. Guías completas para viticultores.',
            '/privacidad' => 'Política de privacidad de Agro365. Información sobre cómo protegemos tus datos personales y cumplimos con el RGPD. Transparencia y seguridad.',
            '/terminos' => 'Términos y condiciones de uso de Agro365. Condiciones legales para el uso de nuestra plataforma de gestión agrícola para viticultores.',
            '/cookies' => 'Política de cookies de Agro365. Información sobre el uso de cookies en nuestra plataforma de gestión agrícola. Transparencia y privacidad.',
            '/aviso-legal' => 'Aviso legal de Agro365. Información legal sobre nuestra plataforma de gestión agrícola para viticultores y bodegas en España.',
        ];

        return $descriptions[$path] ?? 'Software de gestión agrícola profesional para viticultores y bodegas. Cuaderno de campo digital, control de parcelas SIGPAC, gestión de actividades y cumplimiento normativo.';
    }

    /**
     * Generar schema JSON-LD para FAQPage
     * 
     * @param array $faqs Array de FAQs con 'question' y 'answer'
     * @return string JSON encoded schema
     */
    public static function faqSchema(array $faqs): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => []
        ];
        
        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }
        
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * ✅ SEO: Obtener enlaces relacionados para una página
     * Mejora el link juice interno y ayuda a Google a entender la estructura
     * 
     * @param string $currentPath Ruta actual
     * @return array Array de enlaces relacionados con 'url', 'title', 'description'
     */
    public static function getRelatedLinks(string $currentPath): array
    {
        $relatedLinks = [
            '/software-para-viticultores' => [
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital para Viticultores', 'description' => 'Guía completa sobre el cuaderno de campo digital obligatorio desde 2027.'],
                ['url' => route('content.sigpac'), 'title' => '¿Qué es SIGPAC?', 'description' => 'Todo lo que necesitas saber sobre SIGPAC y cómo gestionarlo con Agro365.'],
                ['url' => url('/gestion-vendimia'), 'title' => 'Gestión de Vendimia Digital', 'description' => 'Control completo de cosechas, contenedores y rendimientos.'],
                ['url' => route('content.normativa-pac'), 'title' => 'Normativa PAC 2027', 'description' => 'Cambios y requisitos de la normativa PAC y cómo cumplirlos.'],
            ],
            '/cuaderno-digital-viticultores' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Solución completa de gestión agrícola para viticultores profesionales.'],
                ['url' => route('content.sigpac'), 'title' => 'Gestión SIGPAC', 'description' => 'Integración completa con SIGPAC para gestión de parcelas.'],
                ['url' => route('content.normativa-pac'), 'title' => 'Normativa PAC', 'description' => 'Cumplimiento normativo automático con Agro365.'],
                ['url' => url('/informes-oficiales-agricultura'), 'title' => 'Informes Oficiales', 'description' => 'Genera informes oficiales con firma electrónica.'],
            ],
            '/que-es-sigpac' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Gestión completa de viñedos con SIGPAC integrado.'],
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Registro digital de actividades agrícolas.'],
                ['url' => url('/digitalizar-viñedo'), 'title' => 'Digitalizar Viñedo', 'description' => 'Guía paso a paso para digitalizar tu explotación.'],
                ['url' => route('content.normativa-pac'), 'title' => 'Normativa PAC', 'description' => 'Cumplimiento normativo con SIGPAC.'],
            ],
            '/gestion-vendimia' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Solución completa de gestión agrícola.'],
                ['url' => url('/rendimientos-cosecha-viñedo'), 'title' => 'Rendimientos de Cosecha', 'description' => 'Análisis de rendimientos y productividad.'],
                ['url' => url('/trazabilidad-vino-origen'), 'title' => 'Trazabilidad del Vino', 'description' => 'Control de origen y trazabilidad completa.'],
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Registro de actividades agrícolas.'],
            ],
            '/normativa-pac' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Cumplimiento normativo automático.'],
                ['url' => url('/subvenciones-pac-2024'), 'title' => 'Subvenciones PAC 2024', 'description' => 'Guía completa sobre subvenciones PAC.'],
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Obligatorio desde 2027.'],
                ['url' => url('/informes-oficiales-agricultura'), 'title' => 'Informes Oficiales', 'description' => 'Genera informes para cumplimiento PAC.'],
            ],
            '/digitalizar-viñedo' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Herramienta completa de digitalización.'],
                ['url' => route('content.sigpac'), 'title' => 'Gestión SIGPAC', 'description' => 'Digitaliza tus parcelas con SIGPAC.'],
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Registro digital de actividades.'],
                ['url' => route('content.comparativa'), 'title' => 'Comparativa Software', 'description' => 'Compara soluciones de digitalización.'],
            ],
            '/registro-fitosanitarios' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Gestión completa de fitosanitarios.'],
                ['url' => url('/control-plagas-viñedo'), 'title' => 'Control de Plagas', 'description' => 'Gestión de plagas y enfermedades.'],
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Registro de tratamientos fitosanitarios.'],
                ['url' => url('/calendario-viticola'), 'title' => 'Calendario Vitícola', 'description' => 'Planificación de tratamientos.'],
            ],
            '/facturacion-agricola' => [
                ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Facturación integrada.'],
                ['url' => url('/gestion-vendimia'), 'title' => 'Gestión de Vendimia', 'description' => 'Control de cosechas y facturación.'],
                ['url' => url('/trazabilidad-vino-origen'), 'title' => 'Trazabilidad', 'description' => 'Control de origen y facturación.'],
                ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Registro completo de actividades.'],
            ],
        ];

        return $relatedLinks[$currentPath] ?? [
            ['url' => route('content.software-viticultores'), 'title' => 'Software para Viticultores', 'description' => 'Solución completa de gestión agrícola.'],
            ['url' => route('content.cuaderno-digital-viticultores'), 'title' => 'Cuaderno Digital', 'description' => 'Cuaderno de campo digital obligatorio.'],
            ['url' => route('content.sigpac'), 'title' => 'Gestión SIGPAC', 'description' => 'Todo sobre SIGPAC y su gestión.'],
            ['url' => route('faqs'), 'title' => 'Preguntas Frecuentes', 'description' => 'Respuestas a tus dudas sobre Agro365.'],
        ];
    }
}

