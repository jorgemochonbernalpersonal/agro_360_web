// Preview sprint 1: ONLY truly language-neutral keys
const fs = require('fs');
const path = require('path');

const langDir = path.join(__dirname, '..', 'resources', 'lang');
const es = JSON.parse(fs.readFileSync(path.join(langDir, 'es.json'), 'utf8'));
const en = JSON.parse(fs.readFileSync(path.join(langDir, 'en.json'), 'utf8'));

const missing = Object.keys(es).filter(k => !(k in en));

// Known language-neutral acronyms (same in ES/EN/CA/EU/GL)
const NEUTRAL_ACRONYMS = new Set([
    'PAC','SIGPAC','NIF','DO','CIF','DNI','IBAN','BOE','CCAA',
    'pH','LAI','NDVI','NDRE','SAVI','EVI','ET0','NPK',
    'SILICIE','INFOVI','ROPO','DOP','IGP','AOP',
    'ID','SKU','VAT','IVA',
]);

function isPureSymbol(k) {
    return /^[\d\s\u2013\u2014\-.,&%\u20AC\u00A3\u00A5$*()\\/\[\]{}#@!?+:;~_=<>\u00AB\u00BB\u00B0\u00B7\u00BA\u00AA\u2026"']+$/.test(k);
}

function isLatinScientific(k) {
    return /^[A-Z][a-z]+ [a-z]+$/.test(k);
}

function isNeutralAcronym(k) {
    return NEUTRAL_ACRONYMS.has(k.trim());
}

function isMeasurementUnit(k) {
    return /^[\d\s]*(kg|g|mg|L|ml|cl|hl|cm|mm|m|km|ha|pH|kW|kWh|bar|rpm|ppm)[\d\s/]*$/.test(k);
}

const groups = {
    pureSymbol:  missing.filter(k => isPureSymbol(k)),
    neutralAcro: missing.filter(k => isNeutralAcronym(k) && !isPureSymbol(k)),
    latinSci:    missing.filter(k => isLatinScientific(k)),
    measurement: missing.filter(k => isMeasurementUnit(k) && !isPureSymbol(k)),
};

const allNeutral = [...new Set([
    ...groups.pureSymbol,
    ...groups.neutralAcro,
    ...groups.latinSci,
    ...groups.measurement,
])];

console.log('=== SPRINT 1 - TRULY LANGUAGE-NEUTRAL KEYS ===');
console.log('Total missing EN:', missing.length);
console.log('');
console.log('Pure symbols/numbers:', groups.pureSymbol.length);
groups.pureSymbol.slice(0, 10).forEach(k => console.log('  ' + JSON.stringify(k)));
console.log('');
console.log('Neutral acronyms (PAC, SIGPAC, etc.):', groups.neutralAcro.length);
groups.neutralAcro.forEach(k => console.log('  ' + JSON.stringify(k)));
console.log('');
console.log('Latin/scientific names:', groups.latinSci.length);
groups.latinSci.slice(0, 10).forEach(k => console.log('  ' + JSON.stringify(k)));
console.log('');
console.log('Measurement units:', groups.measurement.length);
groups.measurement.slice(0, 10).forEach(k => console.log('  ' + JSON.stringify(k)));
console.log('');
console.log('TOTAL safe to auto-copy:', allNeutral.length);
console.log('Remaining after sprint 1:', missing.length - allNeutral.length);
console.log('');
console.log('--- NOTE ---');
console.log('The other', missing.length - allNeutral.length, 'keys are Spanish phrases needing real translation (sprint 2+).');
