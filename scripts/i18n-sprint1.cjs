// Apply sprint 1: copy truly language-neutral keys to all locales
const fs = require('fs');
const path = require('path');

const langDir = path.join(__dirname, '..', 'resources', 'lang');
const es = JSON.parse(fs.readFileSync(path.join(langDir, 'es.json'), 'utf8'));

function load(locale) {
    return JSON.parse(fs.readFileSync(path.join(langDir, locale + '.json'), 'utf8'));
}

function save(locale, data) {
    const sorted = Object.fromEntries(
        Object.entries(data).sort(([a], [b]) => a.localeCompare(b, 'es', { sensitivity: 'base' }))
    );
    fs.writeFileSync(path.join(langDir, locale + '.json'), JSON.stringify(sorted, null, 4), 'utf8');
}

// Known language-neutral acronyms (identical in all languages)
const NEUTRAL_ACRONYMS = new Set([
    'PAC','SIGPAC','NIF','DO','CIF','DNI','IBAN','BOE','CCAA',
    'pH','LAI','NDVI','NDRE','SAVI','EVI','ET0','NPK',
    'SILICIE','INFOVI','ROPO','DOP','IGP','AOP',
    'ID','SKU','VAT','IVA',
]);

// Pure symbols / punctuation / numbers — no translatable words at all
function isPureSymbol(k) {
    return /^[\d\s\u2013\u2014\-.,&%\u20AC\u00A3\u00A5$*()\\/\[\]{}#@!?+:;~_=<>\u00AB\u00BB\u00B0\u00B7\u00BA\u00AA\u2026"']+$/.test(k);
}

// Bare measurement units (cm, kg, pH — no surrounding text)
function isBareUnit(k) {
    return /^(cm|kg|g|mg|cl|ml|hl|mm|km|ha|kW|kWh|bar|rpm|ppm)$/.test(k.trim());
}

function isNeutral(k) {
    return isPureSymbol(k) || NEUTRAL_ACRONYMS.has(k.trim()) || isBareUnit(k);
}

const locales = ['en', 'ca', 'eu', 'gl'];
const results = {};

for (const locale of locales) {
    const data = load(locale);
    const missing = Object.keys(es).filter(k => !(k in data));
    const toCopy = missing.filter(isNeutral);

    for (const k of toCopy) {
        data[k] = es[k]; // value is the key itself (language-neutral)
    }

    save(locale, data);
    results[locale] = { missing: missing.length, copied: toCopy.length, keys: toCopy };
    console.log(locale + ': copied ' + toCopy.length + ' neutral keys (was missing ' + missing.length + ')');
}

console.log('\nKeys copied to all locales:');
results['en'].keys.forEach(k => console.log('  ' + JSON.stringify(k)));
