// Sprint 2 batch 14 — O keys (108 translations)
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
    "o 130€/año": "or €130/year",
    "o 130€/año · Onboarding incluido + migración gratuita": "or €130/year · Onboarding included + free migration",
    "o 130€/año — acceso completo": "or €130/year — full access",
    "o 175€/año — ~14,5€/mes": "or €175/year — ~€14.5/month",
    "o 85€/año": "or €85/year",
    "o 85€/año — SIGPAC, PAC, teledetección...": "or €85/year — SIGPAC, CAP, remote sensing...",
    "O escribe el nombre": "Or type the name",
    "o rellena campo a campo": "or fill in field by field",
    "Obliga a incluir mayúscula, número y símbolo": "Requires uppercase, number and symbol",
    "obligatoriedad del cuaderno digital desde 2027": "mandatory digital notebook from 2027",
    "OBLIGATORIO DESDE 2027": "MANDATORY FROM 2027",
    "Obligatorio desde 2027.": "Mandatory from 2027.",
    "Obligatorio desde 2027. Registra todas las actividades por parcela SIGPAC: laboreo, tratamientos, riego, vendimia. Cumple con PAC y Consejo Regulador.": "Mandatory from 2027. Record all activities per SIGPAC plot: tillage, treatments, irrigation, harvest. Comply with CAP and Regulatory Council.",
    "Obligatorio el registro digital de tratamientos fitosanitarios. Agro365 te cubre desde hoy.": "Digital registration of phytosanitary treatments is mandatory. Agro365 covers you from today.",
    "Obligatorio registrar:": "Mandatory to record:",
    "Obs.": "Obs.",
    "Observaciones (opcional)": "Observations (optional)",
    "Observaciones del aforo...": "Yield estimate observations...",
    "Observaciones del análisis...": "Analysis observations...",
    "Observaciones internas sobre este viticultor...": "Internal observations about this viticulturist...",
    "Observaciones para la factura...": "Invoice observations...",
    "Observaciones para la liquidación...": "Settlement observations...",
    "Observaciones sobre el estado de la fermentación...": "Observations on fermentation status...",
    "Observaciones sobre esta recepción...": "Observations about this reception...",
    "Observaciones sobre este contenedor...": "Observations about this container...",
    "Observaciones sobre este producto...": "Observations about this product...",
    "Observaciones sobre la partida...": "Observations about the lot...",
    "Observaciones, acuerdos, incidencias…": "Observations, agreements, incidents…",
    "Observaciones, condiciones, resultados...": "Observations, conditions, results...",
    "Observaciones...": "Observations...",
    "Observación actualizada correctamente.": "Observation updated successfully.",
    "Observación eliminada.": "Observation deleted.",
    "Observación en campo": "Field observation",
    "Observación fenológica": "Phenological observation",
    "Observación fenológica actualizada correctamente.": "Phenological observation updated successfully.",
    "Observación fenológica guardada correctamente.": "Phenological observation saved successfully.",
    "Observación fenológica registrada correctamente.": "Phenological observation recorded successfully.",
    "Observación registrada correctamente.": "Observation recorded successfully.",
    "Obteniendo datos del satélite": "Retrieving satellite data",
    "Oct": "Oct",
    "Octubre — Post-vendimia": "October — Post-harvest",
    "Ocupación global": "Overall occupancy",
    "Oidio": "Powdery mildew",
    "Oidio leve": "Mild powdery mildew",
    "Oidio moderado": "Moderate powdery mildew",
    "OK": "OK",
    "Olfativo": "Olfactory",
    "Onboarding personalizado incluido + migracion gratuita": "Personalised onboarding included + free migration",
    "Onboarding reiniciado. Recarga la página para ver el tour de nuevo.": "Onboarding reset. Reload the page to see the tour again.",
    "Onboarding saltado. Puedes reactivarlo desde Configuración.": "Onboarding skipped. You can reactivate it from Settings.",
    "Onboarding saltado. Puedes reactivarlo desde el dashboard.": "Onboarding skipped. You can reactivate it from the dashboard.",
    "Opcional": "Optional",
    "Opción A: Desde la aplicación móvil": "Option A: From the mobile application",
    "Opción B: Por correo electrónico": "Option B: By email",
    "Open-Meteo": "Open-Meteo",
    "Open-Meteo Soil Model": "Open-Meteo Soil Model",
    "Opera": "Opera",
    "Operaciones enológicas": "Oenological operations",
    "Operación": "Operation",
    "Operación actualizada correctamente.": "Operation updated successfully.",
    "Operación completada.": "Operation completed.",
    "Operación de bodega registrada correctamente.": "Winery operation recorded successfully.",
    "Operación de proceso vinculada": "Linked process operation",
    "Operación de vinificación actualizada correctamente.": "Winemaking operation updated successfully.",
    "Operación de vinificación registrada correctamente.": "Winemaking operation recorded successfully.",
    "Operación desconocida": "Unknown operation",
    "Operación eliminada correctamente.": "Operation deleted successfully.",
    "Operación eliminada.": "Operation deleted.",
    "Oposición:": "Objection:",
    "Optimiza tu asesoramiento profesional": "Optimise your professional advisory",
    "Optimización de la variedad Bobal, tardía en maduración y sensible al mildiu, en parcelas de alta altitud con ciclos térmicos día-noche muy marcados.": "Optimisation of the Bobal variety, late ripening and susceptible to downy mildew, on high-altitude plots with marked day-night thermal cycles.",
    "Optimización de Procesos": "Process Optimisation",
    "Optimizada para móvil:": "Optimised for mobile:",
    "Optimo": "Optimum",
    "Orden": "Order",
    "Ordenar A–Z": "Sort A–Z",
    "Organiza el trabajo de vendimia, poda y laboreo. Asigna cuadrillas a parcelas, controla jornadas y genera partes de trabajo.": "Organise harvest, pruning and tillage work. Assign crews to plots, control working days and generate work orders.",
    "Organiza tu trabajo por campañas": "Organise your work by campaigns",
    "Organiza tus equipos de trabajo": "Organise your work teams",
    "Organizaciones": "Organisations",
    "Organizaciones - Agro365": "Organisations - Agro365",
    "Organización": "Organisation",
    "Organización activa": "Active organisation",
    "Organización actualizada correctamente.": "Organisation updated successfully.",
    "Organización Anual": "Annual Organisation",
    "Organización creada correctamente.": "Organisation created successfully.",
    "Organización eliminada.": "Organisation deleted.",
    "Origen": "Origin",
    "Origen (vacío = externo)": "Origin (empty = external)",
    "Orujo": "Pomace",
    "Orujo / Hollejo": "Pomace / Marc",
    "Orígenes": "Origins",
    "Otra": "Other",
    "Otra certificación": "Other certification",
    "Otra operación": "Other operation",
    "Otras Obligaciones del Consejo Regulador": "Other Regulatory Council Obligations",
    "Otras Soluciones": "Other Solutions",
    "Otro (especificar)": "Other (specify)",
    "Otro cultivo": "Other crop",
    "Otro destino": "Other destination",
    "Otro formato": "Other format",
    "Otro sistema": "Other system",
    "Otro subproducto": "Other by-product",
    "Otros costes": "Other costs",
    "Otros servicios": "Other services",
    "Otros vinos": "Other wines",
    "Ourense, Galicia": "Ourense, Galicia",
    "Oídio (Erysiphe necator)": "Powdery mildew (Erysiphe necator)",
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
console.log('Batch 14 done. Added', added, 'O-key translations to en.json');
