# Declaración responsable del sistema informático de facturación — borrador

> **Estado: BORRADOR, no vinculante.** Los campos marcados `[PENDIENTE]` requieren la
> entidad legal (CIF) que operará Agro365 (fase 2 del plan de acción VeriFactu: alta
> fiscal + certificado). No usar este documento como declaración oficial hasta:
> 1. Rellenar los datos del productor con la entidad ya constituida.
> 2. Revisarlo con una gestoría/asesoría fiscal.
> 3. Firmarlo con fecha y lugar reales.
>
> Base normativa: art. 13.4 del Reglamento (RD 1007/2023) y art. 15 de la Orden
> HAC/1177/2024. Estructura y orden de apartados según el modelo publicado por la AEAT
> (Sede electrónica → Sistemas Informáticos de Facturación y VERI\*FACTU).

---

## DECLARACIÓN RESPONSABLE DEL SISTEMA INFORMÁTICO DE FACTURACIÓN

### 1. Datos del sistema informático

| Campo | Valor |
|---|---|
| Nombre del producto | Agro365 |
| Código identificador único (2 caracteres) | `A3` |
| Versión completa declarada | `1.0.0` |
| Tipología | Sistema de facturación en la nube (SaaS), multiusuario, de uso por bodegas, productores y viticultores |
| Composición | Aplicación web (Laravel/Livewire) + módulo de facturación electrónica VeriFactu (`App\Services\VerifactuService`) |
| Funcionalidades principales | Emisión de facturas y liquidaciones de compra de uva, generación de registros de facturación encadenados, firma electrónica, remisión automática a la AEAT, generación de código QR de verificación, consulta y anulación de registros |
| Características de instalación | Servicio alojado (no instalable localmente); cada obligado tributario (bodega/productor/viticultor) opera bajo un número de instalación propio |
| Uso mono/multiusuario | **Multiusuario.** Un mismo sistema presta servicio a múltiples obligados tributarios distintos, cada uno identificado con su propio NIF y su propio número de instalación (`NumeroInstalacion = AGR-{id_usuario}`) |
| ¿Funciona exclusivamente como VERI\*FACTU? | **Sí.** El sistema no ofrece un modo de funcionamiento "no VERI\*FACTU"; todo registro se genera y remite bajo la modalidad VERI\*FACTU (`TipoUsoPosibleSoloVerifactu = S`) |

### 2. Datos técnicos específicos

| Campo | Valor |
|---|---|
| Remisión automática a la AEAT | Sí, mediante servicio web SOAP contra el endpoint `SistemaFacturacion/VerifactuSOAP` de la AEAT, con envío inmediato tras la generación de cada registro |
| Encadenamiento de registros | Huella SHA-256 sobre 8 campos (NIF emisor, número de serie, fecha, tipo de factura, cuota total, importe total, huella del registro anterior, fecha-hora de generación), encadenada por NIF de emisor |
| Firma electrónica | XAdES / XMLDSig con algoritmo RSA-SHA256, mediante certificado en formato PKCS#12 |
| Código QR de verificación | Sí, generado e incluido en el PDF de cada factura verificada, enlazando al validador de la AEAT |
| Trazabilidad e inalterabilidad | Cada registro de alta y de anulación queda almacenado de forma inmutable (`sif_records`), con su XML de solicitud, XML de respuesta, huella y resultado, sin posibilidad de modificación posterior desde la aplicación |
| Posibilidad de uso fuera de VERI\*FACTU | No contemplada en esta versión |

### 3. Datos de la persona o entidad productora

| Campo | Valor |
|---|---|
| Razón social / nombre | `[PENDIENTE — entidad legal titular de Agro365]` |
| NIF/CIF | `[PENDIENTE]` |
| Domicilio completo | `[PENDIENTE]` |
| NIF del vendor declarado en el XML (`SIF_VENDOR_NIF`) | `[PENDIENTE — debe coincidir con el CIF anterior]` |

### 4. Certificación de cumplimiento

> El sistema informático de facturación **Agro365**, en su versión `1.0.0`, cumple con lo
> dispuesto en el artículo 29.2.j) de la Ley 58/2003, General Tributaria, en el Real
> Decreto 1007/2023, de 5 de diciembre, y en las especificaciones técnicas, funcionales
> y de contenido establecidas en la Orden HAC/1177/2024, de 17 de octubre, en materia de
> integridad, conservación, accesibilidad, legibilidad, trazabilidad e inalterabilidad
> de los registros de facturación.

### 5. Fecha y lugar de emisión

`[PENDIENTE — se cumplimenta en el momento de la firma formal]`

### 6. Datos de contacto

| Campo | Valor |
|---|---|
| Teléfono | `[PENDIENTE]` |
| Correo electrónico | `[PENDIENTE]` |
| Sitio web | `[PENDIENTE]` |
| Enlace a información del producto | `[PENDIENTE]` |
| Enlace a declaraciones responsables históricas (versiones anteriores) | `[PENDIENTE — a crear cuando exista más de una versión declarada]` |

---

## Notas de implementación (no forman parte de la declaración)

- **Publicación obligatoria en dos sitios** (art. 13.4 RRSIF):
  1. **Dentro de la propia aplicación** — falta añadir una sección tipo "Acerca de / Cumplimiento normativo" accesible desde el menú, con el texto de esta declaración ya firmada.
  2. **Fuera de la aplicación** — como PDF o texto descargable, accesible para clientes y comercializadores en el momento de la contratación (p. ej. en la web pública o en el flujo de alta de un nuevo cliente).
- **Cada versión del software que afecte a la facturación requiere una declaración propia** — cuando cambie el número de versión (`sif_software.version` en `config/services.php`), hay que reemitir y conservar la declaración anterior (de ahí el enlace a "declaraciones históricas").
- El código identificador (`A3`) y la versión (`1.0.0`) están tomados literalmente de `config/services.php → sif_software`; si se cambian ahí, hay que sincronizar este documento.
- Conservación: tanto el productor como cualquier comercializador deben guardar copia de todas las declaraciones emitidas por cada versión.
