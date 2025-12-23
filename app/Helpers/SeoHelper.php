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

        $descriptions = [
            '/' => 'Software de gestión agrícola profesional para viticultores y bodegas. Cuaderno de campo digital, SIGPAC, control de parcelas. 6 meses gratis.',
            '/faqs' => 'Respuestas a preguntas frecuentes sobre Agro365: informes oficiales, SIGPAC, cuaderno digital, precios y más. Todo lo que necesitas saber.',
            '/quienes-somos' => 'Conoce más sobre Agro365, la plataforma de gestión agrícola profesional para viticultores y bodegas en España.',
            '/blog' => 'Artículos, noticias y guías sobre gestión agrícola, viticultura, SIGPAC y digitalización del campo.',
            '/tutoriales' => 'Tutoriales paso a paso para usar Agro365: cuaderno digital, SIGPAC, informes oficiales y más.',
            '/privacidad' => 'Política de privacidad de Agro365. Información sobre cómo protegemos tus datos personales y cumplimos con el RGPD.',
            '/terminos' => 'Términos y condiciones de uso de Agro365. Condiciones legales para el uso de nuestra plataforma de gestión agrícola.',
            '/cookies' => 'Política de cookies de Agro365. Información sobre el uso de cookies en nuestra plataforma.',
            '/aviso-legal' => 'Aviso legal de Agro365. Información legal sobre nuestra plataforma de gestión agrícola.',
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
}

