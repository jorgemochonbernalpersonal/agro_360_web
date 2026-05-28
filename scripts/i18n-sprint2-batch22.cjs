#!/usr/bin/env node
// Batch 22 — W + X + Y + Z (21 keys)
const fs = require('fs');
const path = require('path');

const enPath = path.join(__dirname, '../resources/lang/en.json');
const en = JSON.parse(fs.readFileSync(enPath, 'utf8'));

const translations = {
  "Warehouse": "Warehouse",
  "Web": "Web",
  "Windows": "Windows",
  "Windows PC": "Windows PC",
  "WINE-": "WINE-",
  "XSRF-TOKEN": "XSRF-TOKEN",
  "Ya existe un usuario activo con este DNI.": "An active user with this ID number already exists.",
  "Ya existe un usuario con este DNI.": "A user with this ID number already exists.",
  "Ya existe un usuario con este email.": "A user with this email already exists.",
  "Ya existe una declaración para el año :input.": "A declaration for year :input already exists.",
  "Ya existe una solicitud activa o aprobada para este viticultor.": "An active or approved request for this grower already exists.",
  "Ya puedes acceder a este módulo desde tu panel.": "You can now access this module from your panel.",
  "Ya recibido (añada):": "Already received (vintage):",
  "Ya tienes una solicitud pendiente de respuesta.": "You already have a request pending reply.",
  "Ya tienes una suscripción activa.": "You already have an active subscription.",
  "Yecla": "Yecla",
  "Zaragoza, Aragón": "Zaragoza, Aragon",
  "Zonas con excelente desarrollo": "Areas with excellent development",
  "Zonas con problemas - Requieren atención": "Problem areas - Require attention",
  "Zonas normales": "Normal areas",
  "Zonas:": "Areas:",
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
