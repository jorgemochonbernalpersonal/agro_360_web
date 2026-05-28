#!/usr/bin/env node
// Batch 20 — U + Ú (62 keys)
const fs = require('fs');
const path = require('path');

const enPath = path.join(__dirname, '../resources/lang/en.json');
const en = JSON.parse(fs.readFileSync(enPath, 'utf8'));

const translations = {
  "Ubicación GPS": "GPS Location",
  "Ubicación:": "Location:",
  "Ultima actividad": "Last activity",
  "Ultima Revision": "Last Review",
  "Ultimas 24 horas": "Last 24 hours",
  "Un cuaderno digital para viticultores incluye funcionalidades específicas como gestión de variedades de uva, sistemas de conducción, control de vendimia y características específicas del sector vitícola.": "A digital notebook for growers includes specific functionalities such as grape variety management, training systems, harvest control and characteristics specific to the viticulture sector.",
  "Un ecosistema conectado. Cada rol ve lo que necesita, cuando lo necesita.": "A connected ecosystem. Each role sees what it needs, when it needs it.",
  "Un saludo de": "Best regards from",
  "Un software de facturación genérico no entiende estas particularidades. Agro365 está diseñado específicamente para viticultores.": "Generic billing software does not understand these specificities. Agro365 is designed specifically for growers.",
  "Un software para viticultores está especializado en las necesidades específicas de la viticultura: gestión de variedades de uva, control de vendimia, sistemas de conducción, densidad de plantación y normativas específicas del sector vitícola.": "Software for growers is specialised in the specific needs of viticulture: grape variety management, harvest control, training systems, planting density and regulations specific to the viticulture sector.",
  "Un solo panel · Una sola cuenta · Un precio bundle": "One single panel · One single account · One bundle price",
  "Un tratamiento debe incluir: producto, dosis, superficie, justificación, número ROPO del aplicador y plazo de seguridad. La omisión de cualquiera es sancionable.": "A treatment must include: product, dose, area, justification, applicator ROPO number and safety period. Omission of any of these is punishable.",
  "Una bodega puede gestionar múltiples viticultores. Un viticultor puede trabajar con varias bodegas.": "A winery can manage multiple growers. A grower can work with several wineries.",
  "Una de las enfermedades más devastadoras del viñedo. Afecta hojas, brotes y racimos.": "One of the most devastating vineyard diseases. Affects leaves, shoots and bunches.",
  "Una rectificativa no puede rectificarse a sí misma.": "A corrective invoice cannot correct itself.",
  "Una sola plataforma. Cuatro roles. Todo conectado.": "One single platform. Four roles. Everything connected.",
  "Una vez que cambies la contraseña, tu email será verificado automáticamente": "Once you change the password, your email will be automatically verified",
  "Unidad *": "Unit *",
  "Unidad de capacidad": "Capacity unit",
  "Unidad de medida": "Unit of measure",
  "Unidad no válida.": "Invalid unit.",
  "Unidad...": "Unit...",
  "Unidades totales": "Total units",
  "Unit of measurement": "Unit of measurement",
  "Unknown": "Unknown",
  "Usa los botones de acción para registrar controles, trasvases, mermas o análisis.": "Use the action buttons to record controls, transfers, losses or analyses.",
  "Usadas": "Used",
  "Usar NASA ET - más preciso para viñedo": "Use NASA ET - more accurate for vineyard",
  "Uso Móvil Optimizado": "Optimised Mobile Use",
  "Uso móvil:": "Mobile use:",
  "Uso propio": "Own use",
  "Usos": "Uses",
  "Usos de la Parcela": "Plot Uses",
  "Usted es responsable de mantener copias de seguridad de sus datos críticos.": "You are responsible for maintaining backups of your critical data.",
  "Usted se compromete a:": "You agree to:",
  "Usuario": "User",
  "Usuario actualizado correctamente.": "User updated successfully.",
  "Usuario creado": "User created",
  "Usuario creado correctamente. Se ha enviado un email de verificación.": "User created successfully. A verification email has been sent.",
  "Usuario editado": "User edited",
  "Usuario eliminado": "User deleted",
  "Usuario principal (propietario)": "Main user (owner)",
  "Usuarios con email verificado pendientes de activación manual": "Users with verified email pending manual activation",
  "Usuarios Totales": "Total Users",
  "Usuarios verificados pendientes de activación manual": "Verified users pending manual activation",
  "Usufructo": "Usufruct",
  "Utiel-Requena": "Utiel-Requena",
  "Utilizamos tu información exclusivamente para:": "We use your information exclusively for:",
  "Utilizar el Servicio únicamente para fines legales y agrícolas": "Use the Service only for legal and agricultural purposes",
  "Uva": "Grapes",
  "Uva (kg)": "Grapes (kg)",
  "Uva (vino)": "Grapes (wine)",
  "Uva / cosecha (kg)": "Grapes / harvest (kg)",
  "Uva / Mosto / Vino Externo": "Grapes / Must / External Wine",
  "Uva comprada": "Purchased grapes",
  "Uva comprada - ": "Purchased grapes - ",
  "Uva descartada / no apta para vinificación": "Discarded grapes / not suitable for winemaking",
  "Uva descartada.": "Grapes discarded.",
  "Uva en depósitos": "Grapes in tanks",
  "Uva fresca - vendimia propia": "Fresh grapes - own harvest",
  "Uva propia vendimiada": "Own harvested grapes",
  "Uva recibida": "Grapes received",
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
