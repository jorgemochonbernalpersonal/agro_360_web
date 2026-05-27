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
                'type'            => 'pest',
                'name'            => ['es' => 'Polilla del Racimo',    'en' => 'Grape Berry Moth',  'ca' => 'Corc del raïm'],
                'scientific_name' => 'Lobesia botrana',
                'description'     => [
                    'es' => 'Lepidóptero que ataca principalmente los racimos de uva. Es una de las plagas más importantes del viñedo.',
                    'en' => 'Lepidopteran that primarily attacks grape clusters. One of the most important vineyard pests.',
                    'ca' => 'Lepidòpter que ataca principalment els raïms de raïm. És una de les plagues més importants de la vinya.',
                ],
                'symptoms'        => [
                    'es' => 'Racimos con telarañas, bayas perforadas y ennegrecidas, presencia de larvas en racimos.',
                    'en' => 'Clusters with webbing, perforated and blackened berries, larvae present in clusters.',
                    'ca' => 'Raïms amb teranyines, baies perforades i ennegrides, presència de larves als raïms.',
                ],
                'lifecycle'       => [
                    'es' => 'Tres generaciones al año: 1ª (abril-mayo) sobre flores, 2ª (junio-julio) sobre racimos verdes, 3ª (agosto-septiembre) sobre racimos maduros.',
                    'en' => 'Three generations per year: 1st (April–May) on flowers, 2nd (June–July) on green clusters, 3rd (August–September) on ripe clusters.',
                    'ca' => 'Tres generacions a l\'any: 1a (abril-maig) sobre flors, 2a (juny-juliol) sobre raïms verds, 3a (agost-setembre) sobre raïms madurs.',
                ],
                'risk_months'        => [4, 5, 6, 7, 8, 9],
                'threshold'          => '5% de racimos afectados o captura de 10 adultos/trampa/semana',
                'prevention_methods' => [
                    'es' => 'Confusión sexual, trampas de feromonas, eliminación de restos de poda.',
                    'en' => 'Mating disruption, pheromone traps, removal of pruning residues.',
                    'ca' => 'Confusió sexual, trampes de feromones, eliminació de restes de poda.',
                ],
                'control_methods' => ['biologico', 'cultural', 'quimico'],
                'active'          => true,
            ],
            [
                'type'            => 'pest',
                'name'            => ['es' => 'Araña Roja', 'en' => 'Red Spider Mite', 'ca' => 'Aranya roja'],
                'scientific_name' => 'Tetranychus urticae',
                'description'     => [
                    'es' => 'Ácaro que se alimenta de la savia de las hojas causando decoloraciones y debilitamiento de la planta.',
                    'en' => 'Mite that feeds on leaf sap, causing discolouration and weakening of the plant.',
                    'ca' => 'Àcar que s\'alimenta de la sàvia de les fulles causant decoloracions i debilitament de la planta.',
                ],
                'symptoms'        => [
                    'es' => 'Hojas con punteaduras amarillentas, decoloración bronceada, telarañas finas en el envés.',
                    'en' => 'Leaves with yellowish stippling, bronze discolouration, fine webbing on the underside.',
                    'ca' => 'Fulles amb puntejats groguencs, decoloració bronzejada, teranyines fines al revers.',
                ],
                'lifecycle'       => [
                    'es' => 'Múltiples generaciones al año, especialmente activa en condiciones cálidas y secas.',
                    'en' => 'Multiple generations per year, especially active in warm and dry conditions.',
                    'ca' => 'Múltiples generacions a l\'any, especialment activa en condicions càlides i seques.',
                ],
                'risk_months'        => [6, 7, 8, 9],
                'threshold'          => '50% de hojas con presencia o 5-10 ácaros/hoja',
                'prevention_methods' => [
                    'es' => 'Mantener humedad adecuada, favorecer fauna auxiliar, evitar polvo en hojas.',
                    'en' => 'Maintain adequate humidity, encourage natural predators, avoid dust on leaves.',
                    'ca' => 'Mantenir humitat adequada, afavorir fauna auxiliar, evitar pols a les fulles.',
                ],
                'control_methods' => ['biologico', 'cultural', 'quimico'],
                'active'          => true,
            ],
            [
                'type'            => 'pest',
                'name'            => ['es' => 'Filoxera', 'en' => 'Phylloxera', 'ca' => 'Fil·loxera'],
                'scientific_name' => 'Daktulosphaira vitifoliae',
                'description'     => [
                    'es' => 'Insecto que ataca las raíces y hojas de la vid. Históricamente devastador para el viñedo europeo.',
                    'en' => 'Insect that attacks the roots and leaves of the vine. Historically devastating for European vineyards.',
                    'ca' => 'Insecte que ataca les arrels i les fulles de la vinya. Històricament devastador per al vinyer europeu.',
                ],
                'symptoms'        => [
                    'es' => 'Agallas en hojas, nodosidades en raíces, debilitamiento general de la planta.',
                    'en' => 'Leaf galls, root nodosities, general weakening of the plant.',
                    'ca' => 'Gales a les fulles, nodositats a les arrels, debilitament general de la planta.',
                ],
                'lifecycle'       => [
                    'es' => 'Varias generaciones al año, tanto en raíces como en hojas.',
                    'en' => 'Several generations per year, both in roots and leaves.',
                    'ca' => 'Diverses generacions a l\'any, tant a les arrels com a les fulles.',
                ],
                'risk_months'        => [5, 6, 7, 8, 9],
                'threshold'          => 'Cualquier presencia requiere acción inmediata',
                'prevention_methods' => [
                    'es' => 'Uso de portainjertos resistentes (obligatorio en la mayoría de zonas).',
                    'en' => 'Use of resistant rootstocks (mandatory in most areas).',
                    'ca' => 'Ús de portaempelts resistents (obligatori a la majoria de zones).',
                ],
                'control_methods' => ['cultural'],
                'active'          => true,
            ],

            // ENFERMEDADES
            [
                'type'            => 'disease',
                'name'            => ['es' => 'Mildiu', 'en' => 'Downy Mildew', 'ca' => 'Míldiu'],
                'scientific_name' => 'Plasmopara viticola',
                'description'     => [
                    'es' => 'Enfermedad fúngica que afecta a todos los órganos verdes de la vid. Muy destructiva en condiciones húmedas.',
                    'en' => 'Fungal disease affecting all green organs of the vine. Very destructive in humid conditions.',
                    'ca' => 'Malaltia fúngica que afecta tots els òrgans verds de la vinya. Molt destructiva en condicions humides.',
                ],
                'symptoms'        => [
                    'es' => 'Manchas de aceite en hojas, moho blanco en envés, racimos secos (rot gris), necrosis de brotes.',
                    'en' => 'Oil spots on leaves, white mould on underside, dried clusters (grey rot), shoot necrosis.',
                    'ca' => 'Taques d\'oli a les fulles, floridura blanca al revers, raïms secs (podridura grisa), necrosi de brots.',
                ],
                'lifecycle'       => [
                    'es' => 'Requiere agua libre y temperaturas entre 13-25°C. Ciclos de infección de 7-14 días.',
                    'en' => 'Requires free water and temperatures between 13–25°C. Infection cycles of 7–14 days.',
                    'ca' => 'Requereix aigua lliure i temperatures entre 13-25°C. Cicles d\'infecció de 7-14 dies.',
                ],
                'risk_months'        => [4, 5, 6, 7, 8, 9],
                'threshold'          => 'Modelo de riesgo: >10mm lluvia + >10°C durante 24h',
                'prevention_methods' => [
                    'es' => 'Tratamientos preventivos, drenaje adecuado, poda para ventilación, variedades resistentes.',
                    'en' => 'Preventive treatments, adequate drainage, pruning for ventilation, resistant varieties.',
                    'ca' => 'Tractaments preventius, drenatge adequat, poda per a ventilació, varietats resistents.',
                ],
                'control_methods' => ['cultural', 'quimico'],
                'active'          => true,
            ],
            [
                'type'            => 'disease',
                'name'            => ['es' => 'Oídio', 'en' => 'Powdery Mildew', 'ca' => 'Oïdi'],
                'scientific_name' => 'Erysiphe necator',
                'description'     => [
                    'es' => 'Hongo que forma un polvo blanquecino sobre hojas, brotes y racimos. No requiere agua libre para infectar.',
                    'en' => 'Fungus that forms a whitish powder on leaves, shoots and clusters. Does not require free water to infect.',
                    'ca' => 'Fong que forma una pols blanquinosa sobre fulles, brots i raïms. No requereix aigua lliure per infectar.',
                ],
                'symptoms'        => [
                    'es' => 'Polvo blanco-grisáceo en hojas y racimos, deformación de hojas, rajado de bayas.',
                    'en' => 'White-grey powder on leaves and clusters, leaf distortion, berry cracking.',
                    'ca' => 'Pols blanc-grisosa a fulles i raïms, deformació de fulles, esquerdes a les baies.',
                ],
                'lifecycle'       => [
                    'es' => 'Activo entre 6-32°C, óptimo 20-27°C. No necesita lluvia, solo humedad relativa alta.',
                    'en' => 'Active between 6–32°C, optimum 20–27°C. Does not need rain, only high relative humidity.',
                    'ca' => 'Actiu entre 6-32°C, òptim 20-27°C. No necessita pluja, només humitat relativa alta.',
                ],
                'risk_months'        => [5, 6, 7, 8, 9],
                'threshold'          => '1% de órganos afectados en floración',
                'prevention_methods' => [
                    'es' => 'Azufre preventivo, poda para aireación, eliminación de órganos afectados.',
                    'en' => 'Preventive sulphur, pruning for aeration, removal of affected organs.',
                    'ca' => 'Sofre preventiu, poda per a aireació, eliminació d\'òrgans afectats.',
                ],
                'control_methods' => ['cultural', 'quimico'],
                'active'          => true,
            ],
            [
                'type'            => 'disease',
                'name'            => ['es' => 'Botritis', 'en' => 'Grey Mould', 'ca' => 'Podridura grisa'],
                'scientific_name' => 'Botrytis cinerea',
                'description'     => [
                    'es' => 'Hongo que causa podredumbre gris en racimos. Puede ser beneficioso (podredumbre noble) o perjudicial.',
                    'en' => 'Fungus causing grey rot in clusters. Can be beneficial (noble rot) or harmful.',
                    'ca' => 'Fong que causa podridura grisa als raïms. Pot ser beneficiós (podridura noble) o perjudicial.',
                ],
                'symptoms'        => [
                    'es' => 'Moho gris en racimos, bayas blandas y acuosas, olor a moho.',
                    'en' => 'Grey mould on clusters, soft and watery berries, musty smell.',
                    'ca' => 'Floridura grisa als raïms, baies toves i aquoses, olor de floridura.',
                ],
                'lifecycle'       => [
                    'es' => 'Favorecido por humedad alta y temperaturas moderadas (15-20°C).',
                    'en' => 'Favoured by high humidity and moderate temperatures (15–20°C).',
                    'ca' => 'Afavorit per humitat alta i temperatures moderades (15-20°C).',
                ],
                'risk_months'        => [8, 9, 10],
                'threshold'          => 'Cualquier presencia en pre-vendimia',
                'prevention_methods' => [
                    'es' => 'Deshojado, aclareo de racimos, ventilación, evitar daños mecánicos.',
                    'en' => 'Leaf removal, cluster thinning, ventilation, avoid mechanical damage.',
                    'ca' => 'Esfullar, aclarida de raïms, ventilació, evitar danys mecànics.',
                ],
                'control_methods' => ['cultural', 'quimico'],
                'active'          => true,
            ],
            [
                'type'            => 'disease',
                'name'            => ['es' => 'Black Rot', 'en' => 'Black Rot', 'ca' => 'Podridura negra'],
                'scientific_name' => 'Guignardia bidwellii',
                'description'     => [
                    'es' => 'Enfermedad fúngica que causa momificación de racimos. Especialmente grave en climas húmedos.',
                    'en' => 'Fungal disease causing cluster mummification. Particularly severe in humid climates.',
                    'ca' => 'Malaltia fúngica que causa momificació de raïms. Especialment greu en climes humits.',
                ],
                'symptoms'        => [
                    'es' => 'Manchas necróticas en hojas con borde oscuro, racimos momificados de color negro.',
                    'en' => 'Necrotic spots on leaves with dark border, mummified clusters turning black.',
                    'ca' => 'Taques necròtiques a les fulles amb vora fosca, raïms momificats de color negre.',
                ],
                'lifecycle'       => [
                    'es' => 'Requiere temperaturas >9°C y lluvia. Período de incubación 8-25 días.',
                    'en' => 'Requires temperatures >9°C and rain. Incubation period 8–25 days.',
                    'ca' => 'Requereix temperatures >9°C i pluja. Període d\'incubació 8-25 dies.',
                ],
                'risk_months'        => [5, 6, 7, 8],
                'threshold'          => '1% de racimos afectados',
                'prevention_methods' => [
                    'es' => 'Eliminación de momias, tratamientos preventivos, poda sanitaria.',
                    'en' => 'Removal of mummies, preventive treatments, sanitation pruning.',
                    'ca' => 'Eliminació de mòmies, tractaments preventius, poda sanitària.',
                ],
                'control_methods' => ['cultural', 'quimico'],
                'active'          => true,
            ],
        ];

        foreach ($pests as $pest) {
            Pest::withoutEvents(function () use ($pest) {
                $existing = Pest::where('scientific_name', $pest['scientific_name'])->first();
                if ($existing) {
                    $existing->update($pest);
                } else {
                    Pest::create($pest);
                }
            });
        }
    }
}
