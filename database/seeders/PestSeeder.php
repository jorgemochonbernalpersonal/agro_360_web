<?php

namespace Database\Seeders;

use App\Models\Pest;
use Illuminate\Database\Seeder;

class PestSeeder extends Seeder
{
    public function run(): void
    {
        $pests = [
            // PLAGAS
            [
                'type' => 'pest',
                'name' => 'Polilla del Racimo',
                'scientific_name' => 'Lobesia botrana',
                'description' => 'Lepidóptero que ataca principalmente los racimos de uva. Es una de las plagas más importantes del viñedo.',
                'symptoms' => 'Racimos con telarañas, bayas perforadas y ennegrecidas, presencia de larvas en racimos.',
                'lifecycle' => 'Tres generaciones al año: 1ª (abril-mayo) sobre flores, 2ª (junio-julio) sobre racimos verdes, 3ª (agosto-septiembre) sobre racimos maduros.',
                'risk_months' => [4, 5, 6, 7, 8, 9],
                'threshold' => '5% de racimos afectados o captura de 10 adultos/trampa/semana',
                'prevention_methods' => 'Confusión sexual, trampas de feromonas, eliminación de restos de poda.',
                'active' => true,
            ],
            [
                'type' => 'pest',
                'name' => 'Araña Roja',
                'scientific_name' => 'Tetranychus urticae',
                'description' => 'Ácaro que se alimenta de la savia de las hojas causando decoloraciones y debilitamiento de la planta.',
                'symptoms' => 'Hojas con punteaduras amarillentas, decoloración bronceada, telarañas finas en el envés.',
                'lifecycle' => 'Múltiples generaciones al año, especialmente activa en condiciones cálidas y secas.',
                'risk_months' => [6, 7, 8, 9],
                'threshold' => '50% de hojas con presencia o 5-10 ácaros/hoja',
                'prevention_methods' => 'Mantener humedad adecuada, favorecer fauna auxiliar, evitar polvo en hojas.',
                'active' => true,
            ],
            [
                'type' => 'pest',
                'name' => 'Filoxera',
                'scientific_name' => 'Daktulosphaira vitifoliae',
                'description' => 'Insecto que ataca las raíces y hojas de la vid. Históricamente devastador para el viñedo europeo.',
                'symptoms' => 'Agallas en hojas, nodosidades en raíces, debilitamiento general de la planta.',
                'lifecycle' => 'Varias generaciones al año, tanto en raíces como en hojas.',
                'risk_months' => [5, 6, 7, 8, 9],
                'threshold' => 'Cualquier presencia requiere acción inmediata',
                'prevention_methods' => 'Uso de portainjertos resistentes (obligatorio en la mayoría de zonas).',
                'active' => true,
            ],
            
            // ENFERMEDADES
            [
                'type' => 'disease',
                'name' => 'Mildiu',
                'scientific_name' => 'Plasmopara viticola',
                'description' => 'Enfermedad fúngica que afecta a todos los órganos verdes de la vid. Muy destructiva en condiciones húmedas.',
                'symptoms' => 'Manchas de aceite en hojas, moho blanco en envés, racimos secos (rot gris), necrosis de brotes.',
                'lifecycle' => 'Requiere agua libre y temperaturas entre 13-25°C. Ciclos de infección de 7-14 días.',
                'risk_months' => [4, 5, 6, 7, 8, 9],
                'threshold' => 'Modelo de riesgo: >10mm lluvia + >10°C durante 24h',
                'prevention_methods' => 'Tratamientos preventivos, drenaje adecuado, poda para ventilación, variedades resistentes.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Oídio',
                'scientific_name' => 'Erysiphe necator',
                'description' => 'Hongo que forma un polvo blanquecino sobre hojas, brotes y racimos. No requiere agua libre para infectar.',
                'symptoms' => 'Polvo blanco-grisáceo en hojas y racimos, deformación de hojas, rajado de bayas.',
                'lifecycle' => 'Activo entre 6-32°C, óptimo 20-27°C. No necesita lluvia, solo humedad relativa alta.',
                'risk_months' => [5, 6, 7, 8, 9],
                'threshold' => '1% de órganos afectados en floración',
                'prevention_methods' => 'Azufre preventivo, poda para aireación, eliminación de órganos afectados.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Botritis',
                'scientific_name' => 'Botrytis cinerea',
                'description' => 'Hongo que causa podredumbre gris en racimos. Puede ser beneficioso (podredumbre noble) o perjudicial.',
                'symptoms' => 'Moho gris en racimos, bayas blandas y acuosas, olor a moho.',
                'lifecycle' => 'Favorecido por humedad alta y temperaturas moderadas (15-20°C).',
                'risk_months' => [8, 9, 10],
                'threshold' => 'Cualquier presencia en pre-vendimia',
                'prevention_methods' => 'Deshojado, aclareo de racimos, ventilación, evitar daños mecánicos.',
                'active' => true,
            ],
            [
                'type' => 'disease',
                'name' => 'Black Rot',
                'scientific_name' => 'Guignardia bidwellii',
                'description' => 'Enfermedad fúngica que causa momificación de racimos. Especialmente grave en climas húmedos.',
                'symptoms' => 'Manchas necróticas en hojas con borde oscuro, racimos momificados de color negro.',
                'lifecycle' => 'Requiere temperaturas >9°C y lluvia. Período de incubación 8-25 días.',
                'risk_months' => [5, 6, 7, 8],
                'threshold' => '1% de racimos afectados',
                'prevention_methods' => 'Eliminación de momias, tratamientos preventivos, poda sanitaria.',
                'active' => true,
            ],
        ];

        foreach ($pests as $pest) {
            Pest::create($pest);
        }
    }
}
