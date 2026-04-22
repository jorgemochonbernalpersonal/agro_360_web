<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 45 avisos de bodega a viticultores (winery_announcements).
 *   · 15 avisos de campaña (harvest_alert)
 *   · 20 avisos informativos (info)
 *   · 10 avisos de acción requerida (action_required)
 *
 * Depende de: winery_viticulturist (viticultores vinculados a bodega 1)
 */
class WineryAnnouncementsSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const HARVEST_ALERTS = [
        ['Inicio recepción de uva — campaña 2026', 'La bodega comunica que el inicio de la recepción de uva tinta (Listán Negro y Negramoll) queda fijado para el próximo lunes. Por favor, confirme disponibilidad y transporte.'],
        ['Aforo final actualizado — parcelas zona norte', 'Tras las mediciones de aforo realizadas la semana pasada, se actualiza la previsión de kg para las parcelas de la zona norte. Revise su cuadro de mando para los datos actualizados.'],
        ['Cambio de horario báscula — semana 32', 'Durante la semana 32, el horario de pesaje se amplía de 07:00 a 21:00 para evitar colas. Por favor, planifique sus entregas con antelación.'],
        ['Alerta calidad: botrytis detectada en parcelas costeras', 'Se han detectado focos de Botrytis cinerea en parcelas cercanas a la costa. Se recomienda adelantar la cosecha en dichas zonas y comunicar a bodega para ajustar la recepción.'],
        ['Cierre recepción uva blanca — campaña 2026', 'La bodega comunica que el próximo viernes se cerrará la recepción de variedades blancas. Los viticultores con uva pendiente deben coordinar entrega antes de esa fecha.'],
        ['Análisis de madurez — semana 30', 'Los resultados de los análisis de madurez de esta semana están disponibles en el portal. Consulte los valores de Brix, pH y acidez para su variedad y parcela.'],
        ['Previsión meteorológica adversa — protocolo lluvia', 'Se esperan precipitaciones significativas a partir del jueves. La bodega activa el protocolo de lluvia: se acepta uva con humedad hasta 14% sin descuento. Contacte con bodega si tiene dudas.'],
        ['Apertura campaña 2025 — documentación requerida', 'Para el inicio de la campaña 2025, todos los viticultores deben tener actualizada su ficha de parcela y carné ROPO en el sistema antes del 15 de agosto.'],
        ['Precios de compra definitivos — vendimia 2026', 'Se publican los precios definitivos de compra de uva para la campaña 2026 por variedad. Listán Negro: 0,90€/kg · Negramoll: 1,10€/kg · Malvasía: 1,40€/kg. Resto de variedades en portal.'],
        ['Convocatoria: jornada de formación en vendimia mecánica', 'La bodega organiza una jornada de formación sobre técnicas de vendimia mecanizada el próximo 20 de agosto a las 10:00 en la nave de recepción. Confirme asistencia antes del 15/08.'],
        ['Retraso recepción — avería báscula principal', 'La báscula principal ha sufrido una avería técnica. Se habilitará báscula auxiliar a partir de las 14:00. Lamentamos los inconvenientes. Se notificará cuando quede resuelta.'],
        ['Análisis foliares — resultados disponibles', 'Los análisis foliares solicitados en julio ya están disponibles en el módulo de análisis de calidad. Si detecta carencias nutricionales, contacte con el enólogo de bodega.'],
        ['Campaña 2024 — liquidaciones emitidas', 'Se han emitido todas las liquidaciones de compra de uva correspondientes a la campaña 2024. Revise su buzón y confirme recepción desde el portal de viticultor.'],
        ['Obligatoriedad registro fitosanitario — temporada 2026', 'Recordatorio: todos los tratamientos fitosanitarios aplicados desde el 1 de enero de 2026 deben estar registrados en el cuaderno de campo digital antes de la vendimia.'],
        ['Aviso climatológico: helada tardía prevista', 'Los modelos meteorológicos indican riesgo de helada tardía la próxima madrugada en cotas superiores a 600 m. Se recomienda tomar precauciones en parcelas de altura elevada.'],
    ];

    private const INFO_MESSAGES = [
        ['Nueva funcionalidad: seguimiento de entregas en tiempo real', 'La bodega ha habilitado el módulo de seguimiento de entregas en tiempo real. Desde ahora puede consultar el estado de su ticket de pesaje directamente desde el portal.'],
        ['Actualización certificación ecológica — documentación', 'Recordamos que el período de renovación de certificaciones ecológicas finaliza el 31 de octubre. Asegúrese de tener toda la documentación al día en el módulo de certificaciones.'],
        ['Guía de uso del cuaderno de campo digital', 'Hemos publicado una guía detallada de uso del cuaderno de campo digital. Acceda desde el apartado de documentos o descárguela en el portal de viticultores.'],
        ['Encuesta de satisfacción campaña 2025', 'Le invitamos a rellenar la encuesta de satisfacción sobre la campaña 2025. Su opinión es fundamental para mejorar nuestros procesos. Acceso disponible hasta el 30 de noviembre.'],
        ['Nuevos enólogos de referencia asignados para 2026', 'La bodega ha ampliado su equipo técnico. A partir de enero de 2026, cada viticultor tendrá asignado un enólogo de referencia para seguimiento personalizado de sus parcelas.'],
        ['Recordatorio: actualización de datos bancarios', 'Para garantizar el cobro correcto de las liquidaciones de uva, por favor revise y actualice sus datos bancarios antes del cierre de campaña.'],
        ['Jornada DO Gran Canaria — inscripciones abiertas', 'La Denominación de Origen Gran Canaria organiza su jornada anual técnica el 15 de marzo. La bodega dispone de plazas reservadas. Solicite su inscripción a través del portal.'],
        ['Manual de buenas prácticas fitosanitarias 2026', 'Se ha publicado el manual actualizado de buenas prácticas fitosanitarias para la temporada 2026 con las últimas modificaciones del reglamento europeo. Disponible en documentos.'],
        ['Recordatorio: inspección ITB equipos de aplicación', 'Todos los pulverizadores y equipos de aplicación de fitosanitarios deben superar la inspección ITB cada 3 años. Consulte la lista de centros de inspección habilitados en Gran Canaria.'],
        ['Apertura módulo AICA — contratos tipo', 'Ya está disponible en el portal el módulo AICA para la gestión de contratos de compraventa de uva. Todos los contratos de la campaña 2026 deberán formalizarse a través de este módulo.'],
        ['Nuevo precio de referencia aceite — INFOVI publicado', 'El MAPA ha publicado el nuevo INFOVI con precios de referencia actualizados. Consulte el módulo de cumplimiento para el detalle de precios por categoría.'],
        ['Subvenciones FEADER — plazo solicitud abierto', 'Se ha abierto el plazo de solicitud de ayudas FEADER para mejora de infraestructuras agrícolas. La bodega puede orientarle en la tramitación. Contacte antes del 28 de febrero.'],
        ['Revisión contratos marco 2026', 'Los contratos marco para la campaña 2026 están disponibles para revisión y firma digital. Por favor, acceda al portal antes del 15 de enero para completar el proceso.'],
        ['Actualización reglamento etiquetado UE — impacto en bodegas', 'El nuevo reglamento de etiquetado vitivinícola de la UE entra en vigor en mayo de 2026. La bodega le informará sobre los cambios que afectan a los vinos de su parcela.'],
        ['Seminario online: gestión del riego en viña', 'La bodega organiza un seminario online sobre gestión eficiente del riego en viña el próximo 8 de febrero a las 18:00. Inscripción gratuita — plazas limitadas.'],
        ['Cierre ejercicio 2025 — certificados de retención', 'Los certificados de retención del IRPF correspondientes al ejercicio 2025 estarán disponibles en el portal antes del 31 de enero de 2026.'],
        ['Comunicado: cambio de gestoría para liquidaciones', 'A partir de la campaña 2026, la bodega tramitará las liquidaciones de uva a través de una nueva gestoría. Los datos de contacto actualizados están disponibles en el apartado de configuración.'],
        ['Aviso: parcelas con déficit hídrico detectado', 'El análisis de teledetección de la semana pasada ha identificado parcelas con posible déficit hídrico. Consulte el mapa de entorno de parcelas para ver si su explotación está afectada.'],
        ['Protocolo COVID/gripe — campaña vendimia 2026', 'La bodega recuerda el protocolo sanitario vigente para el acceso a las instalaciones durante la campaña de vendimia. Consulte el documento adjunto para más detalles.'],
        ['Felicitación campaña 2025 — resultados excepcionales', 'La bodega quiere agradecer a todos los viticultores su esfuerzo durante la campaña 2025. La calidad de la uva recibida ha sido excepcional. Gracias por vuestra confianza y dedicación.'],
    ];

    private const ACTION_MESSAGES = [
        ['URGENTE: actualice su carné ROPO antes del 31 de marzo', 'El carné ROPO de algunos viticultores expira el 31 de marzo. Sin carné vigente no se podrán registrar tratamientos fitosanitarios en el cuaderno digital. Renueve a través del CAEB o centro homologado.'],
        ['Acción requerida: firma contrato campaña 2026', 'El contrato de compraventa de uva para la campaña 2026 está pendiente de firma. Acceda al portal y complete la firma digital antes del 20 de enero para garantizar su plaza de entrega.'],
        ['Pendiente: declaración PAC 2026', 'Según nuestros registros, su declaración de la PAC para 2026 aún no ha sido confirmada en el sistema. Contacte con su gestor o acceda directamente al FEGAgestión para regularizar su situación.'],
        ['Revisión urgente: discrepancia en peso última entrega', 'Se ha detectado una discrepancia en el peso registrado en su última entrega de uva. Por favor, acceda al módulo de disputas para revisar los detalles y responder en un plazo máximo de 72 horas.'],
        ['Confirmación requerida: datos bancarios para liquidación', 'Para proceder al pago de la liquidación de la campaña 2025, necesitamos confirmar sus datos bancarios. Acceda al portal y verifique que el IBAN registrado es correcto antes del 15 de enero.'],
        ['Acción requerida: actualización ficha explotación RGSEAA', 'Su ficha de explotación en el Registro General Sanitario de Empresas Alimentarias y Alimentos (RGSEAA) requiere actualización. Acceda al módulo de Explotación y revise los datos.'],
        ['Pendiente: entrega documentación ecológica para auditoría', 'La auditoría de certificación ecológica de su explotación está programada para el próximo mes. Por favor, asegúrese de tener disponibles todos los registros de campo y tratamientos del último año.'],
        ['Alerta: análisis de uva fuera de parámetros — revisión requerida', 'El análisis de calidad de su última entrega ha arrojado valores fuera de los parámetros acordados en contrato. La bodega le contactará para acordar un ajuste en precio. Revise el informe en el portal.'],
        ['Renovación seguro agrario — plazo cierre 15 de febrero', 'Le recordamos que el plazo para la contratación del seguro agrario de viña con cobertura de pedrisco y helada cierra el 15 de febrero. Contacte con su agente de seguros para formalizar la póliza.'],
        ['Acción requerida: confirme asistencia a reunión de campaña', 'La bodega convoca una reunión informativa sobre la campaña 2026 el próximo 10 de febrero. Su confirmación de asistencia es necesaria antes del 5 de febrero. Responda a este aviso o contacte directamente con bodega.'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        $now  = now();
        $rows = [];
        $pivotRows = [];

        // 15 harvest_alerts
        foreach (self::HARVEST_ALERTS as $idx => [$title, $body]) {
            $daysAgo     = 365 - (int)round($idx * 300 / 14);  // 365..65 días atrás
            $publishedAt = now()->subDays($daysAgo)->toDateTimeString();
            $expiresAt   = now()->subDays(max(0, $daysAgo - 60))->toDateTimeString();
            $isExpired   = $daysAgo > 60;

            $rows[] = [
                'winery_id'    => self::WINERY_USER_ID,
                'title'        => $title,
                'body'         => $body,
                'type'         => 'harvest_alert',
                'target'       => 'all',
                'published_at' => $publishedAt,
                'expires_at'   => $isExpired ? $expiresAt : now()->addDays(30)->toDateTimeString(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        // 20 info
        foreach (self::INFO_MESSAGES as $idx => [$title, $body]) {
            $daysAgo     = 400 - (int)round($idx * 380 / 19);
            $publishedAt = now()->subDays($daysAgo)->toDateTimeString();
            $isExpired   = $idx < 10;

            $rows[] = [
                'winery_id'    => self::WINERY_USER_ID,
                'title'        => $title,
                'body'         => $body,
                'type'         => 'info',
                'target'       => 'all',
                'published_at' => $publishedAt,
                'expires_at'   => $isExpired
                    ? now()->subDays($daysAgo - 30)->toDateTimeString()
                    : now()->addDays(60)->toDateTimeString(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        // 10 action_required — target 'specific' para los primeros 5, 'all' para los últimos 5
        foreach (self::ACTION_MESSAGES as $idx => [$title, $body]) {
            $daysAgo     = 180 - (int)round($idx * 160 / 9);
            $publishedAt = now()->subDays($daysAgo)->toDateTimeString();
            $target      = $idx < 5 ? 'specific' : 'all';

            $rows[] = [
                'winery_id'    => self::WINERY_USER_ID,
                'title'        => $title,
                'body'         => $body,
                'type'         => 'action_required',
                'target'       => $target,
                'published_at' => $publishedAt,
                'expires_at'   => now()->addDays(30)->toDateTimeString(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        // Insert announcements one by one to capture IDs for pivot
        $insertedIds = [];
        foreach ($rows as $row) {
            $insertedIds[] = DB::table('winery_announcements')->insertGetId($row);
        }

        // Pivot: para avisos 'specific' (los 5 action_required primeros), enlazar con 2–3 viticultores
        if (!empty($viticulturistIds)) {
            $specificOffset = 15 + 20; // harvest_alerts + info (indices 0-34 = 35 rows)
            // Los 5 'specific' son los primeros 5 de ACTION_MESSAGES → insertedIds[35..39]
            for ($i = 0; $i < 5; $i++) {
                $announcementId = $insertedIds[$specificOffset + $i];
                $vitCount       = 2 + ($i % 2); // 2 o 3 viticultores
                for ($v = 0; $v < $vitCount && $v < count($viticulturistIds); $v++) {
                    $vitId = $viticulturistIds[($i + $v) % count($viticulturistIds)];
                    $pivotRows[] = [
                        'announcement_id'  => $announcementId,
                        'viticulturist_id' => $vitId,
                        'read_at'          => ($v === 0) ? now()->subDays(2)->toDateTimeString() : null,
                    ];
                }
            }

            // Además, para los avisos 'all' más recientes (últimos 10), añadir algunos read_at
            $recentStart = count($insertedIds) - 10;
            foreach (array_slice($insertedIds, $recentStart) as $j => $annId) {
                if (!empty($viticulturistIds) && $j % 2 === 0) {
                    $vitId = $viticulturistIds[$j % count($viticulturistIds)];
                    // Verificar que no exista ya (por si cayó también en specific)
                    $pivotRows[] = [
                        'announcement_id'  => $annId,
                        'viticulturist_id' => $vitId,
                        'read_at'          => now()->subDays(1)->toDateTimeString(),
                    ];
                }
            }

            if (!empty($pivotRows)) {
                // Deduplicate by announcement_id + viticulturist_id
                $seen     = [];
                $uniquePivot = [];
                foreach ($pivotRows as $pr) {
                    $key = $pr['announcement_id'] . '_' . $pr['viticulturist_id'];
                    if (!isset($seen[$key])) {
                        $seen[$key]    = true;
                        $uniquePivot[] = $pr;
                    }
                }
                foreach (array_chunk($uniquePivot, 100) as $chunk) {
                    DB::table('winery_announcement_viticulturist')->insert($chunk);
                }
            }
        }

        $harvest  = count(self::HARVEST_ALERTS);
        $info     = count(self::INFO_MESSAGES);
        $action   = count(self::ACTION_MESSAGES);
        $this->command->info("✅ Avisos bodega: " . count($rows) . " registros ({$harvest} campaña, {$info} informativos, {$action} acción requerida)");
    }

    private function cleanup(): void
    {
        $ids = DB::table('winery_announcements')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('winery_announcement_viticulturist')
                ->whereIn('announcement_id', $ids)
                ->delete();
        }

        DB::table('winery_announcements')
            ->where('winery_id', self::WINERY_USER_ID)
            ->delete();
    }
}
