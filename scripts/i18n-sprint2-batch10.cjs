// Sprint 2 batch 10 — K keys (18 translations)
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
    "K (kg/ha)": "K (kg/ha)",
    "Kg": "Kg",
    "Kg (A)": "Kg (A)",
    "Kg (B)": "Kg (B)",
    "Kg *": "Kg *",
    "Kg / ha": "Kg / ha",
    "Kg confirmados por bodega": "Kg confirmed by winery",
    "Kg disponibles": "Kg available",
    "Kg estimados *": "Estimated Kg *",
    "kg libre": "free kg",
    "Kg recibidos *": "Kg received *",
    "Kg total en stock": "Total Kg in stock",
    "kg/ha": "kg/ha",
    "Kg/ha": "Kg/ha",
    "Kilogramos (kg) — uva / mosto": "Kilograms (kg) — grape / must",
    "KPIs y tendencias": "KPIs and trends",
    "KPIs, tendencias y comparativas económicas de tu bodega": "KPIs, trends and economic comparisons for your winery",
    "KPIs, tendencias y comparativas económicas de tu bodega.": "KPIs, trends and economic comparisons for your winery.",
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
console.log('Batch 10 done. Added', added, 'K-key translations to en.json');
