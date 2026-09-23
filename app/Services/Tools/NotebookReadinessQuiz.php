<?php

namespace App\Services\Tools;

/**
 * Autodiagnóstico "¿Estás listo para el cuaderno digital 2027?".
 *
 * Los plazos citados siguen la guía /cuaderno-campo-digital-2027
 * (RD 1054/2022, modificado por el RD 34/2025): fertilización desde el
 * 1/1/2026 y tratamientos fitosanitarios en formato electrónico desde el 1/1/2027.
 */
class NotebookReadinessQuiz
{
    /**
     * @return array<string, array{question: string, tip: string}>
     */
    public function questions(): array
    {
        return [
            'digital' => [
                'question' => '¿Llevas el cuaderno de explotación en una aplicación digital (no en papel ni en un Excel suelto)?',
                'tip' => 'Desde el 1 de enero de 2027 los tratamientos fitosanitarios deben registrarse en formato electrónico interoperable. El papel deja de valer.',
            ],
            'treatments' => [
                'question' => '¿Anotas cada tratamiento con producto, nº de registro, dosis, parcela y fecha?',
                'tip' => 'Son los datos mínimos de cada tratamiento. Anótalos el mismo día: reconstruirlos al final de campaña es donde aparecen los errores.',
            ],
            'fertilization' => [
                'question' => '¿Registras también las fertilizaciones (abonos, dosis y fechas)?',
                'tip' => 'La fertilización es obligatoria en el cuaderno desde el 1 de enero de 2026. Si todavía no la anotas, ya vas tarde.',
            ],
            'sigpac' => [
                'question' => '¿Tienes todas tus parcelas identificadas con su referencia SIGPAC?',
                'tip' => 'El cuaderno digital se organiza por recinto SIGPAC. Tenlas localizadas antes de empezar para no atascarte el primer día.',
            ],
            'applicator' => [
                'question' => '¿Quien aplica los fitosanitarios tiene el carné de aplicador en vigor?',
                'tip' => 'Cada tratamiento debe identificar al aplicador y su carné. Revisa fechas de caducidad y renovaciones pendientes.',
            ],
            'equipment' => [
                'question' => '¿Tu equipo de tratamientos tiene la inspección técnica (ITEAF) en vigor?',
                'tip' => 'Los equipos de aplicación deben pasar inspección periódica. Guarda el certificado y su fecha junto al cuaderno.',
            ],
            'purchases' => [
                'question' => '¿Guardas las facturas o albaranes de compra de los fitosanitarios?',
                'tip' => 'Son la prueba de trazabilidad del producto que aplicaste. Guárdalos ordenados por campaña.',
            ],
            'withdrawal' => [
                'question' => '¿Controlas el plazo de seguridad de cada producto antes de vendimiar?',
                'tip' => 'Vendimiar dentro del plazo de seguridad puede suponer residuos por encima del límite y el rechazo de la uva en bodega.',
            ],
        ];
    }

    /**
     * @param array<string, bool|null> $answers clave de pregunta => sí/no
     *
     * @return array{score: int, level: string, label: string, pending: array<int, array{question: string, tip: string}>}
     */
    public function evaluate(array $answers): array
    {
        $questions = $this->questions();
        $yes = 0;
        $pending = [];

        foreach ($questions as $key => $question) {
            if (($answers[$key] ?? false) === true) {
                $yes++;
            } else {
                $pending[] = $question;
            }
        }

        $score = (int) round($yes / count($questions) * 100);

        [$level, $label] = match (true) {
            $score >= 85 => ['ready', 'Estás preparado'],
            $score >= 50 => ['partial', 'Vas por buen camino, pero te faltan cosas'],
            default => ['risk', 'Riesgo alto: tal y como estás, no llegas a 2027'],
        };

        return ['score' => $score, 'level' => $level, 'label' => $label, 'pending' => $pending];
    }
}
