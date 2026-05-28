#!/usr/bin/env node
// Batch 16 — Q + Ú (23 keys)
const fs = require('fs');
const path = require('path');

const enPath = path.join(__dirname, '../resources/lang/en.json');
const en = JSON.parse(fs.readFileSync(enPath, 'utf8'));

const translations = {
  "Quantity available when registering the supply": "Quantity available when registering the supply",
  "Que es la PAC?": "What is the CAP?",
  "Queda prohibido el uso del Sitio Web con fines ilícitos o lesivos.": "The use of the Website for unlawful or harmful purposes is prohibited.",
  "Quema (cuando permitido)": "Burning (when permitted)",
  "Quemaduras foliares": "Leaf burns",
  "Question": "Question",
  "Quién realizó la poda": "Who performed the pruning",
  "Qué es SIGPAC": "What is SIGPAC",
  "Última": "Last",
  "Última actividad": "Last activity",
  "Última actualización: 09/03/2026": "Last update: 09/03/2026",
  "Última actualización: abril 2026": "Last update: April 2026",
  "Última conexión": "Last connection",
  "Última Conexión": "Last Connection",
  "Última edición": "Last edit",
  "Última Imagen": "Last Image",
  "últimas 24h": "last 24h",
  "Último movimiento": "Last movement",
  "Últimos 10": "Last 10",
  "Últimos 12 meses — % de suscriptores que siguen activos": "Last 12 months — % of subscribers still active",
  "Últimos 20": "Last 20",
  "Últimos 7 días · brix > 2": "Last 7 days · brix > 2",
  "Últimos controles": "Last inspections",
};

let added = 0;
for (const [key, value] of Object.entries(translations)) {
  if (!(key in en)) {
    en[key] = value;
    added++;
  }
}

const sorted = Object.fromEntries(Object.entries(en).sort(([a], [b]) => a.localeCompare(b)));
fs.writeFileSync(enPath, JSON.stringify(sorted, null, 2) + '\n');
console.log(`Added: ${added} keys`);
