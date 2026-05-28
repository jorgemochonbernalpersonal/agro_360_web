// Sprint 2 batch 7 — H keys (63 translations)
const fs = require('fs');
const path = require('path');

const langDir = path.join(__dirname, '..', 'resources', 'lang');

function load(locale) {
    return JSON.parse(fs.readFileSync(path.join(langDir, locale + '.json'), 'utf8'));
}

function save(locale, data) {
    const sorted = Object.fromEntries(
        Object.entries(data).sort(([a], [b]) => a.localeCompare(b, 'es', { sensitivity: 'base' }))
    );
    fs.writeFileSync(path.join(langDir, locale + '.json'), JSON.stringify(sorted, null, 4), 'utf8');
}

const translations = {
    "Ha": "Ha",
    "ha ecológicas": "organic ha",
    "Hacia contenedor *": "To container *",
    "Hallazgos": "Findings",
    "HARV-": "HARV-",
    "Harvest - Listo para cosecha": "Harvest - Ready for harvest",
    "HARVEST-": "HARVEST-",
    "Has recibido este email porque tienes una cuenta en Agro365.": "You have received this email because you have an account on Agro365.",
    "Has sido asignado a la bodega ": "You have been assigned to the winery ",
    "Has vuelto a tu sesión de administrador.": "You have returned to your administrator session.",
    "Hash de firma:": "Signature hash:",
    "Hash SHA-256": "SHA-256 Hash",
    "Hash:": "Hash:",
    "Hasta 25": "Up to 25",
    "hectolitros (HL)": "hectolitres (HL)",
    "Hectolitros (HL)": "Hectolitres (HL)",
    "hectáreas exactas": "exact hectares",
    "Hectáreas totales": "Total hectares",
    "Helada confirmada": "Confirmed frost",
    "Heladas": "Frosts",
    "Hemos recibido tu pago correctamente.": "We have received your payment successfully.",
    "Herramienta completa de digitalización.": "Complete digitalisation tool.",
    "Herramientas Necesarias para Digitalizar": "Tools Needed to Digitalise",
    "Historial completo de uso por parcela": "Complete usage history per plot",
    "Historial completo:": "Complete history:",
    "Historial de actividad": "Activity history",
    "Historial de albaranes y facturas de este lote": "Delivery notes and invoices history for this lot",
    "Historial de envíos": "Submission history",
    "Historial de eventos de seguridad del sistema": "System security event history",
    "Historial de movimientos": "Movement history",
    "Historial de Movimientos": "Movement History",
    "Historial de Movimientos - Agro365": "Movement History - Agro365",
    "Historial de notificaciones eliminado.": "Notification history deleted.",
    "Historial de operaciones": "Operations history",
    "Historial y programación de mantenimientos del contenedor.": "Container maintenance history and scheduling.",
    "Historial, riego, GDD, espectral y comparación entre parcelas": "History, irrigation, GDD, spectral and plot comparison",
    "Histórico de evolución:": "Evolution history:",
    "Histórico de vendimias": "Harvest history",
    "Histórico y Análisis": "History and Analysis",
    "Hito campaña": "Campaign milestone",
    "Hojas de vid": "Vine leaves",
    "Hola": "Hello",
    "Hola ": "Hello ",
    "Hola, ": "Hello, ",
    "Honorarios enólogo": "Oenologist fees",
    "Hora": "Time",
    "Hora de descarga": "Unloading time",
    "Host": "Host",
    "Host no configurado": "Host not configured",
    "Hotel de Insectos": "Insect Hotel",
    "Hoy →": "Today →",
    "Hubo un error al eliminar el equipo. Por favor, inténtalo de nuevo.": "There was an error deleting the team. Please try again.",
    "Hubo un error al eliminar la cuadrilla. Por favor, inténtalo de nuevo.": "There was an error deleting the crew. Please try again.",
    "Huesca, Aragón": "Huesca, Aragon",
    "Humedad alta, vigilar lluvias": "High humidity, monitor rainfall",
    "Humedad baja (": "Low humidity (",
    "Humedad del Suelo": "Soil Moisture",
    "Humedad del suelo baja (": "Low soil moisture (",
    "Humedad ideal": "Ideal humidity",
    "Humedad relativa objetivo (%)": "Target relative humidity (%)",
    "Humedad Suelo (%)": "Soil Moisture (%)",
    "Huérfanos": "Orphans",
    "Húmedo": "Humid",
};

const en = load('en');
let added = 0;

for (const [key, value] of Object.entries(translations)) {
    if (!(key in en)) {
        en[key] = value;
        added++;
    }
}

save('en', en);
console.log('Batch 7 done. Added', added, 'H-key translations to en.json');
