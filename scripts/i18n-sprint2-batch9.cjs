// Sprint 2 batch 9 — J keys (16 translations)
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
    "Jerez": "Jerez",
    "Job eliminado.": "Job deleted.",
    "Job no encontrado.": "Job not found.",
    "Job reencolado correctamente.": "Job requeued successfully.",
    "Jobs fallidos": "Failed jobs",
    "Jobs Fallidos - Admin - Agro365": "Failed Jobs - Admin - Agro365",
    "Jobs pendientes": "Pending jobs",
    "Juan García López": "Juan García López",
    "Juan García...": "Juan García...",
    "Juan Pérez": "Juan Pérez",
    "Jul": "Jul",
    "Julio — Envero": "July — Veraison",
    "Jumilla": "Jumilla",
    "Jun": "Jun",
    "Junio — Floración y cuajado": "June — Flowering and fruit set",
    "Justificación": "Justification",
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
console.log('Batch 9 done. Added', added, 'J-key translations to en.json');
