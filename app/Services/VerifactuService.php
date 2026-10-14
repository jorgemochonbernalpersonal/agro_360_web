<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoicingSetting;
use App\Models\SifRecord;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Verifactu (VERI*FACTU) — AEAT Sistema Informático de Facturación
 *
 * Regulation: RD 1007/2023 + Orden HAC/1177/2024
 * Schema:     tikeV1.0/cont/ws/SuministroInformacion.xsd
 * Endpoint:   /TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP
 */
class VerifactuService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Send an invoice to AEAT Verifactu and optionally email the client.
     */
    public function send(Invoice $invoice, bool $sendEmail = true): array
    {
        try {
            // 1. Fetch chaining data (previous record's huella)
            $chain = $this->resolveChain($invoice->user->dni ?? '');

            // 2. Generate compliant XML
            $xmlResult = $this->generateXml($invoice, $chain);
            if (isset($xmlResult['errors'])) {
                return ['success' => false, 'errors' => $xmlResult['errors']];
            }

            ['xml' => $xml, 'huella' => $huella, 'fechaHoraGen' => $fechaHoraGen] = $xmlResult;

            // 3. Sign (skipped in test mode without cert)
            $signedXml = $this->signXml($xml, $invoice->user);

            // 4. Send to AEAT (simulated in test mode)
            $response = $this->sendToAeat($signedXml, $invoice);

            // 5. Parse response
            $linea = $response->RespuestaLinea[0] ?? null;
            $csv = $linea->CSV ?? null;
            $errorCode = $linea->CodigoErrorRegistro ?? null;
            $errorDesc = $linea->DescripcionErrorRegistro ?? null;
            $isOk = ($response->EstadoEnvio ?? '') === 'Correcto' && ! $errorCode;

            // 6. Log in sif_records (store huella for chaining)
            SifRecord::create([
                'invoice_id' => $invoice->id,
                'tipo_registro' => 'ALTA',
                'csv' => $csv,
                'huella' => $huella,
                'request_xml' => $xml,
                'response_xml' => $this->responseToXml($response),
                'status' => $isOk ? 'OK' : 'ER',
                'error_message' => $errorCode ? "[{$errorCode}] {$errorDesc}" : null,
            ]);

            // 7. Update invoice
            $invoice->update([
                'sif_status' => $isOk ? 'aceptado' : 'error',
                'is_verified_aet' => $isOk,
                'sif_uuid' => $csv,
                'sif_hash' => $huella,
                'sif_sent_at' => now(),
                'sif_response' => $response->EstadoEnvio,
            ]);

            // 8. Email after successful verification
            if ($isOk && $sendEmail) {
                $this->sendVerifiedEmail($invoice);
            }

            return [
                'success' => $isOk,
                'csv' => $csv,
                'huella' => $huella,
                'errors' => $isOk ? [] : ["[{$errorCode}] {$errorDesc}"],
            ];

        } catch (\Throwable $e) {
            // \Throwable (no solo \Exception): un \Error real de PHP (p.ej. acceso a
            // una propiedad inexistente de un stdClass al parsear la respuesta SOAP)
            // no debe tumbar la petición sin dejar rastro — se registra como error.
            Log::error('VerifactuService::send', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);

            SifRecord::create([
                'invoice_id' => $invoice->id,
                'tipo_registro' => 'ALTA',
                'status' => 'ER',
                'error_message' => $e->getMessage(),
            ]);

            $invoice->update(['sif_status' => 'error']);

            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Cancel (RegistroAnulacion) a previously verified invoice.
     * Sends the cancellation XML to AEAT and logs the result.
     */
    public function cancel(Invoice $invoice, string $reason = ''): array
    {
        try {
            $chain = $this->resolveChain($invoice->user->dni ?? '');
            $fechaHoraGen = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');
            $huella = $this->buildAnulacionHuella($invoice, $chain, $fechaHoraGen);
            $xml = $this->buildAnulacionXml($invoice, $chain, $huella, $fechaHoraGen);
            $signedXml = $this->signXml($xml, $invoice->user);
            $response = $this->sendToAeat($signedXml, $invoice);

            $linea = $response->RespuestaLinea[0] ?? null;
            $csv = $linea->CSV ?? null;
            $errorCode = $linea->CodigoErrorRegistro ?? null;
            $errorDesc = $linea->DescripcionErrorRegistro ?? null;
            $isOk = ($response->EstadoEnvio ?? '') === 'Correcto' && ! $errorCode;

            SifRecord::create([
                'invoice_id' => $invoice->id,
                'tipo_registro' => 'ANULACION',
                'huella' => $huella,
                'request_xml' => $xml,
                'response_xml' => $this->responseToXml($response),
                'status' => $isOk ? 'OK' : 'ER',
                'error_message' => $errorCode ? "[{$errorCode}] {$errorDesc}" : null,
            ]);

            if ($isOk) {
                $invoice->update([
                    'sif_status' => 'pendiente',
                    'is_verified_aet' => false,
                    'sif_uuid' => null,
                    'sif_sent_at' => null,
                ]);
            }

            return [
                'success' => $isOk,
                'errors' => $isOk ? [] : ["[{$errorCode}] {$errorDesc}"],
            ];

        } catch (\Throwable $e) {
            // \Throwable, misma razón que en send(): un \Error de parseo de la
            // respuesta SOAP no debe crashear sin dejar registro.
            Log::error('VerifactuService::cancel', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);

            SifRecord::create([
                'invoice_id' => $invoice->id,
                'tipo_registro' => 'ANULACION',
                'status' => 'ER',
                'error_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Retry a failed invoice submission.
     */
    public function retry(Invoice $invoice): array
    {
        $invoice->update(['sif_status' => 'pendiente', 'sif_sent_at' => null]);

        return $this->send($invoice);
    }

    /**
     * Generate fully compliant Verifactu XML (RegistroAlta).
     *
     * @return array{xml: string, huella: string, fechaHoraGen: string}|array{errors: string[]}
     */
    public function generateXml(Invoice $invoice, array $chain = []): array
    {
        // ── Validation ──────────────────────────────────────────────────────
        $errors = [];

        $numSerie = $invoice->invoice_number ?? null;
        $fecha = $invoice->invoice_date ?? null;
        $issuerNif = trim($invoice->user->dni ?? '');
        $invSettings = InvoicingSetting::forUser($invoice->user->id)->first();
        $issuerName = trim($invSettings->issuer_legal_name ?? $invoice->user->name ?? '');

        if (empty($numSerie)) {
            $errors[] = 'El número de factura es obligatorio para Verifactu.';
        }
        if (empty($fecha)) {
            $errors[] = 'La fecha de factura es obligatoria para Verifactu.';
        }
        if (empty($issuerNif)) {
            $errors[] = 'Tu NIF/DNI no está configurado. Ve a Ajustes y añádelo.';
        }

        // Las rectificativas (R1, "por diferencias") declaran legítimamente
        // importes negativos ante la AEAT; las facturas ordinarias no.
        if ($invoice->corrective) {
            if ((float) $invoice->total_amount === 0.0) {
                $errors[] = 'El importe total de una rectificativa no puede ser cero.';
            }
        } elseif ((float) $invoice->total_amount <= 0) {
            $errors[] = 'El importe total debe ser mayor que cero.';
        }

        // grape_purchase (liquidación de vendimia) y harvest_sale (venta de vendimia
        // del viticultor) llevan una retención IRPF en `tax_rate`/`tax_amount`, no una
        // cuota de IVA. Declararlas tal cual ante Verifactu enviaría una retención
        // como si fuera IVA repercutido (ver auditoría de facturación — fix #4/#13).
        // Hasta que exista un modelo fiscal correcto para estos tipos (facturación
        // por cuenta de terceros / retención), quedan excluidos de Verifactu.
        if (in_array($invoice->invoice_type, ['grape_purchase', 'harvest_sale'], true)) {
            $errors[] = 'Las liquidaciones de vendimia con retención IRPF (compra de uva / venta de vendimia) no se declaran ante Verifactu: la retención no es IVA y este tipo de documento no se modela como F1 emitida por la bodega/viticultor.';
        }

        if (! empty($errors)) {
            return ['errors' => $errors];
        }

        // ── Derived values ──────────────────────────────────────────────────
        $fechaAEAT = $fecha->format('d-m-Y');
        $tipoFactura = $invoice->corrective ? 'R1' : 'F1';
        $cuotaTotal = number_format((float) $invoice->tax_amount, 2, '.', '');
        $importeTotal = number_format((float) $invoice->total_amount, 2, '.', '');
        $descOp = $invoice->invoice_type === 'grape_purchase'
            ? 'Liquidación de vendimia'
            : 'Venta de productos';

        // FechaHoraHusoGenRegistro — ISO 8601 with timezone offset (mandatory)
        $fechaHoraGen = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');

        // ── Huella (hash) ────────────────────────────────────────────────────
        // AEAT VERI*FACTU spec: SHA-256 of the literal, field-labelled string
        // "IDEmisorFactura=...&NumSerieFactura=...&FechaExpedicionFactura=...&
        //  TipoFactura=...&CuotaTotal=...&ImporteTotal=...&Huella=...&
        //  FechaHoraHusoGenRegistro=...", uppercase hex result.
        $huellaAnterior = $chain['huella'] ?? '';
        $hashInput =
            'IDEmisorFactura='.$issuerNif.
            '&NumSerieFactura='.$numSerie.
            '&FechaExpedicionFactura='.$fechaAEAT.
            '&TipoFactura='.$tipoFactura.
            '&CuotaTotal='.$cuotaTotal.
            '&ImporteTotal='.$importeTotal.
            '&Huella='.$huellaAnterior.
            '&FechaHoraHusoGenRegistro='.$fechaHoraGen;
        $huella = strtoupper(hash('sha256', $hashInput));

        // ── Recipient ────────────────────────────────────────────────────────
        // En liquidaciones de vendimia (grape_purchase) no hay Client: el
        // destinatario es el viticultor, así que caemos a su NIF/nombre.
        $recipientNif = $invoice->billing_company_document
            ?: $invoice->client?->company_document
            ?: $invoice->client?->particular_document
            ?: $invoice->viticulturist?->dni;
        $recipientName = $invoice->billing_company_name
            ?: trim(($invoice->billing_first_name ?? '').' '.($invoice->billing_last_name ?? ''))
            ?: $invoice->viticulturist?->name;

        // ── Software block (SistemaInformatico) ─────────────────────────────
        $swVendorName = config('services.sif_software.vendor_name', 'Agro365');
        $swVendorNif = config('services.sif_software.vendor_nif', '');
        $swName = config('services.sif_software.name', 'Agro365');
        $swId = config('services.sif_software.id', 'A3');           // max 2 chars
        $swVersion = config('services.sif_software.version', '1.0.0');
        $swInstall = 'AGR-'.$invoice->user->id; // una instalación por usuario (max 100)

        // ── Build XML ────────────────────────────────────────────────────────
        $ns = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroLR.xsd';
        $nsT = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroInformacion.xsd';

        $x = '<?xml version="1.0" encoding="UTF-8"?>';
        $x .= "<RegFactuSistemaFacturacion xmlns=\"{$ns}\" xmlns:T=\"{$nsT}\">";

        // Cabecera
        $x .= '<Cabecera>';
        $x .= '<IDVersionSuministro>1.0</IDVersionSuministro>';
        $x .= '<Titular>';
        $x .= '<T:NombreRazon>'.$this->e($issuerName).'</T:NombreRazon>';
        $x .= '<T:NIF>'.$this->e($issuerNif).'</T:NIF>';
        $x .= '</Titular>';
        $x .= '<TipoComunicacion>A0</TipoComunicacion>';
        $x .= '</Cabecera>';

        // RegistroFactura > RegistroAlta
        $x .= '<RegistroFactura>';
        $x .= '<T:RegistroAlta>';
        $x .= '<T:IDVersion>1.0</T:IDVersion>';

        // IDFactura (corrected field names vs SII)
        $x .= '<T:IDFactura>';
        $x .= '<T:IDEmisorFactura>'.$this->e($issuerNif).'</T:IDEmisorFactura>';
        $x .= '<T:NumSerieFactura>'.$this->e($numSerie).'</T:NumSerieFactura>';
        $x .= '<T:FechaExpedicionFactura>'.$this->e($fechaAEAT).'</T:FechaExpedicionFactura>';
        $x .= '</T:IDFactura>';

        $x .= '<T:NombreRazonEmisor>'.$this->e($issuerName).'</T:NombreRazonEmisor>';
        $x .= '<T:TipoFactura>'.$tipoFactura.'</T:TipoFactura>';

        // Rectificativa fields
        if ($invoice->corrective && $invoice->correctedInvoice) {
            $x .= '<T:TipoRectificativa>I</T:TipoRectificativa>';
            $x .= '<T:FacturasRectificadas>';
            $x .= '<T:IDFacturaRectificada>';
            $x .= '<T:IDEmisorFactura>'.$this->e($issuerNif).'</T:IDEmisorFactura>';
            $x .= '<T:NumSerieFactura>'.$this->e($invoice->correctedInvoice->invoice_number).'</T:NumSerieFactura>';
            $x .= '<T:FechaExpedicionFactura>'.$this->e($invoice->correctedInvoice->invoice_date->format('d-m-Y')).'</T:FechaExpedicionFactura>';
            $x .= '</T:IDFacturaRectificada>';
            $x .= '</T:FacturasRectificadas>';
        }

        $x .= '<T:DescripcionOperacion>'.$this->e($descOp).'</T:DescripcionOperacion>';

        // Desglose: UNA DetalleDesglose por CADA tipo de IVA distinto presente en
        // las líneas (nunca un único bloque calculado desde $invoice->tax_rate,
        // que puede estar vacío en ventas exentas o promediado en facturas con
        // varios tipos). grape_purchase/harvest_sale ya están excluidas arriba,
        // así que aquí tax_rate/tax_base/tax_amount de las líneas son SIEMPRE
        // IVA real, nunca retención IRPF.
        $taxGroups = $invoice->items
            ->groupBy(fn ($item) => number_format((float) $item->tax_rate, 2, '.', ''))
            ->map(fn ($group) => [
                'rate' => (float) $group->first()->tax_rate,
                'base' => (float) $group->sum(fn ($item) => (float) $item->tax_base),
                'cuota' => (float) $group->sum(fn ($item) => (float) $item->tax_amount),
            ])
            ->values();

        if ($taxGroups->isEmpty()) {
            // Fallback defensivo si por lo que sea no hay líneas cargadas: usa los
            // totales de cabecera para no dejar el Desglose vacío (no debería
            // ocurrir en el flujo normal, las facturas siempre tienen items).
            $taxGroups = collect([[
                'rate' => (float) $invoice->tax_rate,
                'base' => (float) $invoice->tax_base,
                'cuota' => (float) $invoice->tax_amount,
            ]]);
        }

        $x .= '<T:Desglose>';
        foreach ($taxGroups as $group) {
            $x .= '<T:DetalleDesglose>';
            // CalificacionOperacion: S1=sujeto y no exento, S2=ISP; OperacionExenta: E1–E8
            if ($group['rate'] > 0) {
                $x .= '<T:CalificacionOperacion>S1</T:CalificacionOperacion>';
                $x .= '<T:TipoImpositivo>'.number_format($group['rate'], 2, '.', '').'</T:TipoImpositivo>';
                $x .= '<T:BaseImponibleOimporteNoSujeto>'.number_format($group['base'], 2, '.', '').'</T:BaseImponibleOimporteNoSujeto>';
                $x .= '<T:CuotaRepercutida>'.number_format($group['cuota'], 2, '.', '').'</T:CuotaRepercutida>';
            } else {
                // Exento (IVA 0%)
                $x .= '<T:OperacionExenta>E1</T:OperacionExenta>';
                $x .= '<T:BaseImponibleOimporteNoSujeto>'.number_format($group['base'], 2, '.', '').'</T:BaseImponibleOimporteNoSujeto>';
            }
            $x .= '</T:DetalleDesglose>';
        }
        $x .= '</T:Desglose>';

        // Totales a nivel de factura
        $x .= '<T:CuotaTotal>'.$cuotaTotal.'</T:CuotaTotal>';
        $x .= '<T:ImporteTotal>'.$importeTotal.'</T:ImporteTotal>';

        // Destinatarios (optional per schema, but expected for F1)
        if ($tipoFactura === 'F1' && ($recipientNif || $recipientName)) {
            $x .= '<T:Destinatarios>';
            $x .= '<T:IDDestinatario>';
            $x .= '<T:NombreRazon>'.$this->e($recipientName ?: 'Destinatario').'</T:NombreRazon>';
            if ($recipientNif) {
                $x .= '<T:NIF>'.$this->e($recipientNif).'</T:NIF>';
            }
            $x .= '</T:IDDestinatario>';
            $x .= '</T:Destinatarios>';
        }

        // Encadenamiento (mandatory — chaining)
        $x .= '<T:Encadenamiento>';
        if (empty($huellaAnterior)) {
            $x .= '<T:PrimerRegistro>S</T:PrimerRegistro>';
        } else {
            $x .= '<T:RegistroAnterior>';
            $x .= '<T:IDEmisorFactura>'.$this->e($chain['issuerNif']).'</T:IDEmisorFactura>';
            $x .= '<T:NumSerieFactura>'.$this->e($chain['numSerie']).'</T:NumSerieFactura>';
            $x .= '<T:FechaExpedicionFactura>'.$this->e($chain['fecha']).'</T:FechaExpedicionFactura>';
            $x .= '<T:Huella>'.$this->e($huellaAnterior).'</T:Huella>';
            $x .= '</T:RegistroAnterior>';
        }
        $x .= '</T:Encadenamiento>';

        // SistemaInformatico (mandatory — identifies the software)
        $x .= '<T:SistemaInformatico>';
        $x .= '<T:NombreRazon>'.$this->e($swVendorName).'</T:NombreRazon>';
        if ($swVendorNif) {
            $x .= '<T:NIF>'.$this->e($swVendorNif).'</T:NIF>';
        }
        $x .= '<T:NombreSistemaInformatico>'.$this->e($swName).'</T:NombreSistemaInformatico>';
        $x .= '<T:IdSistemaInformatico>'.$this->e(substr($swId, 0, 2)).'</T:IdSistemaInformatico>';
        $x .= '<T:Version>'.$this->e($swVersion).'</T:Version>';
        $x .= '<T:NumeroInstalacion>'.$this->e($swInstall).'</T:NumeroInstalacion>';
        $x .= '<T:TipoUsoPosibleSoloVerifactu>S</T:TipoUsoPosibleSoloVerifactu>';
        $x .= '<T:TipoUsoPosibleMultiOT>N</T:TipoUsoPosibleMultiOT>';
        $x .= '<T:IndicadorMultiplesOT>N</T:IndicadorMultiplesOT>';
        $x .= '</T:SistemaInformatico>';

        // Timestamp with timezone (mandatory)
        $x .= '<T:FechaHoraHusoGenRegistro>'.$this->e($fechaHoraGen).'</T:FechaHoraHusoGenRegistro>';

        // Huella
        $x .= '<T:TipoHuella>01</T:TipoHuella>';
        $x .= '<T:Huella>'.$huella.'</T:Huella>';
        $x .= '<T:GeneradoPor>E</T:GeneradoPor>';

        $x .= '</T:RegistroAlta>';
        $x .= '</RegistroFactura>';
        $x .= '</RegFactuSistemaFacturacion>';

        return ['xml' => $x, 'huella' => $huella, 'fechaHoraGen' => $fechaHoraGen];
    }

    /**
     * Generate an inline SVG QR code for embedding in DomPDF.
     * Uses the pure-PHP SVG backend — no GD or Imagick required.
     * Returns the raw SVG string, ready to embed directly in Blade.
     */
    public function buildQrSvg(Invoice $invoice): string
    {
        $url = $this->buildQrUrl($invoice);

        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(85),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd
            );
            $writer = new \BaconQrCode\Writer($renderer);

            return $writer->writeString($url);
        } catch (\Throwable $e) {
            Log::warning('VerifactuService: QR SVG generation failed', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Build the verification URL for the QR code (mandatory on invoice PDFs).
     */
    public function buildQrUrl(Invoice $invoice): string
    {
        $nif = urlencode($invoice->user->dni ?? '');
        $numSerie = urlencode($invoice->invoice_number ?? '');
        $fecha = urlencode($invoice->invoice_date?->format('d-m-Y') ?? '');
        $importe = urlencode(number_format((float) $invoice->total_amount, 2, '.', ''));

        return 'https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR'
            ."?nif={$nif}&numserie={$numSerie}&fecha={$fecha}&importe={$importe}";
    }

    /**
     * Sign XML with the ISSUER'S OWN PKCS#12 certificate (XMLDSig RSA-SHA256).
     * Cada usuario firma con su propio certificado (ver VerifactuCertificateService) —
     * no existe un certificado único de plataforma. En modo testing, sin
     * certificado configurado, devuelve el XML sin firmar.
     */
    public function signXml(string $xmlString, User $issuer): string
    {
        $environment = config('services.sif_aeat.environment', 'testing');
        $certs = $this->loadCertificate($issuer);

        if ($certs !== null && $issuer->sif_cert_expires_at && $issuer->sif_cert_expires_at->isPast()) {
            throw new \Exception(__('Tu certificado VeriFactu ha caducado. Sube uno nuevo en Ajustes → Datos Fiscales.'));
        }

        if ($certs === null) {
            if ($environment === 'production') {
                throw new \Exception(__('No tienes un certificado VeriFactu configurado. Súbelo en Ajustes → Datos Fiscales antes de enviar facturas en producción.'));
            }

            return $xmlString;
        }

        $dom = new \DOMDocument;
        $dom->loadXML($xmlString);
        $canonical = $dom->C14N();
        $digestValue = base64_encode(hash('sha256', $canonical, true));

        $signedInfo =
            '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            .'<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'
            .'<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>'
            .'<ds:Reference URI="">'
            .'<ds:Transforms>'
            .'<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>'
            .'<ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'
            .'</ds:Transforms>'
            .'<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            .'<ds:DigestValue>'.$digestValue.'</ds:DigestValue>'
            .'</ds:Reference>'
            .'</ds:SignedInfo>';

        $signedInfoDom = new \DOMDocument;
        $signedInfoDom->loadXML($signedInfo);
        $signedInfoCanonical = $signedInfoDom->C14N();

        $signature = null;
        openssl_sign($signedInfoCanonical, $signature, $certs['pkey'], OPENSSL_ALGO_SHA256);

        $certClean = str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"],
            '', $certs['cert']
        );

        $dsSignature =
            '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            .$signedInfo
            .'<ds:SignatureValue>'.base64_encode($signature).'</ds:SignatureValue>'
            .'<ds:KeyInfo><ds:X509Data><ds:X509Certificate>'.$certClean.'</ds:X509Certificate></ds:X509Data></ds:KeyInfo>'
            .'</ds:Signature>';

        return str_replace(
            '</RegFactuSistemaFacturacion>',
            $dsSignature.'</RegFactuSistemaFacturacion>',
            $xmlString
        );
    }

    /**
     * Carga y descifra el certificado PKCS#12 del emisor. El .p12 se guarda
     * cifrado en reposo (Crypt) en el disco privado; la clave nunca queda en
     * claro en disco. Devuelve ['cert' => ..., 'pkey' => ...] o null si el
     * usuario no tiene certificado configurado.
     *
     * @return array{cert: string, pkey: string}|null
     */
    private function loadCertificate(User $issuer): ?array
    {
        $path = $issuer->sif_cert_path;
        if (empty($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $encrypted = Storage::disk('local')->get($path);
        $pkcs12 = Crypt::decryptString($encrypted);
        $password = (string) ($issuer->sif_cert_password ?? '');

        $certs = [];
        if (! openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \Exception(__('No se pudo abrir tu certificado VeriFactu. Verifica la contraseña en Ajustes → Datos Fiscales.'));
        }

        return ['cert' => $certs['cert'], 'pkey' => $certs['pkey']];
    }

    /**
     * Return current environment configuration status for the given issuer.
     * El entorno/WSDL/endpoint son de plataforma; el certificado es del usuario.
     */
    public function getConfig(?User $user = null): array
    {
        $wsdlPath = config('services.sif_aeat.wsdl');
        $wsdlConfigured = ! empty($wsdlPath) && file_exists($wsdlPath);
        $certConfigured = $user
            ? app(VerifactuCertificateService::class)->hasCertificate($user)
            : false;

        return [
            'environment' => config('services.sif_aeat.environment', 'testing'),
            'endpoint' => config('services.sif_aeat.endpoint'),
            'cert_configured' => $certConfigured,
            'cert_uploaded_at' => $certConfigured ? $user->sif_cert_uploaded_at : null,
            'cert_expires_at' => $certConfigured ? $user->sif_cert_expires_at : null,
            'wsdl_path' => $wsdlPath,
            'wsdl_configured' => $wsdlConfigured,
            'is_production' => config('services.sif_aeat.environment') === 'production',
            // Todo lo necesario para declarar de verdad ante la AEAT.
            'production_ready' => $certConfigured && $wsdlConfigured,
        ];
    }

    /**
     * Send verified invoice PDF by email to the client.
     */
    public function sendVerifiedEmail(Invoice $invoice): void
    {
        $email = $invoice->billing_email ?? $invoice->client->email ?? null;

        if (! $email) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\InvoiceVerifiedMail($invoice));
        } catch (\Exception $e) {
            Log::warning('VerifactuService: could not send verified email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Resolve chaining data: fetch the last OK SifRecord for this issuer NIF.
     */
    private function resolveChain(string $issuerNif): array
    {
        if (empty($issuerNif)) {
            return [];
        }

        $last = SifRecord::whereHas('invoice', fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('dni', $issuerNif)))
            ->where('status', 'OK')
            ->whereNotNull('huella')
            ->latest()
            ->first();

        if (! $last) {
            return [];
        }

        $inv = $last->invoice;

        return [
            'huella' => $last->huella,
            'issuerNif' => $issuerNif,
            'numSerie' => $inv->invoice_number,
            'fecha' => $inv->invoice_date?->format('d-m-Y') ?? '',
        ];
    }

    /**
     * Build Huella for a cancellation record (RegistroAnulacion).
     *
     * IMPORTANT: RegistroAnulacion has its OWN AEAT spec field list — it does NOT
     * reuse the RegistroAlta formula (a cancellation record carries no
     * TipoFactura/CuotaTotal/ImporteTotal). Per the AEAT VERI*FACTU spec, the
     * huella input is the literal, field-labelled string:
     * "IDEmisorFacturaAnulada=...&NumSerieFacturaAnulada=...&
     *  FechaExpedicionFacturaAnulada=...&Huella=...&FechaHoraHusoGenRegistro=...".
     */
    private function buildAnulacionHuella(Invoice $invoice, array $chain, string $fechaHoraGen): string
    {
        $issuerNif = $invoice->user->dni ?? '';
        $numSerie = $invoice->invoice_number ?? '';
        $fechaAEAT = $invoice->invoice_date?->format('d-m-Y') ?? '';
        $huellaAnterior = $chain['huella'] ?? '';

        $hashInput =
            'IDEmisorFacturaAnulada='.$issuerNif.
            '&NumSerieFacturaAnulada='.$numSerie.
            '&FechaExpedicionFacturaAnulada='.$fechaAEAT.
            '&Huella='.$huellaAnterior.
            '&FechaHoraHusoGenRegistro='.$fechaHoraGen;

        return strtoupper(hash('sha256', $hashInput));
    }

    /**
     * Build the RegistroAnulacion XML.
     */
    private function buildAnulacionXml(Invoice $invoice, array $chain, string $huella, string $fechaHoraGen): string
    {
        $issuerNif = $invoice->user->dni ?? '';
        $invSettings = InvoicingSetting::forUser($invoice->user->id)->first();
        $issuerName = trim($invSettings->issuer_legal_name ?? $invoice->user->name ?? '');
        $numSerie = $invoice->invoice_number ?? '';
        $fechaAEAT = $invoice->invoice_date?->format('d-m-Y') ?? '';
        $huellaAnterior = $chain['huella'] ?? '';

        $swVendorName = config('services.sif_software.vendor_name', 'Agro365');
        $swVendorNif = config('services.sif_software.vendor_nif', '');
        $swName = config('services.sif_software.name', 'Agro365');
        $swId = config('services.sif_software.id', 'A3');
        $swVersion = config('services.sif_software.version', '1.0.0');
        $swInstall = 'AGR-'.$invoice->user->id;

        $ns = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroLR.xsd';
        $nsT = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroInformacion.xsd';

        $x = '<?xml version="1.0" encoding="UTF-8"?>';
        $x .= "<RegFactuSistemaFacturacion xmlns=\"{$ns}\" xmlns:T=\"{$nsT}\">";

        $x .= '<Cabecera>';
        $x .= '<IDVersionSuministro>1.0</IDVersionSuministro>';
        $x .= '<Titular>';
        $x .= '<T:NombreRazon>'.$this->e($issuerName).'</T:NombreRazon>';
        $x .= '<T:NIF>'.$this->e($issuerNif).'</T:NIF>';
        $x .= '</Titular>';
        $x .= '<TipoComunicacion>A1</TipoComunicacion>';
        $x .= '</Cabecera>';

        $x .= '<RegistroFactura>';
        $x .= '<T:RegistroAnulacion>';
        $x .= '<T:IDVersion>1.0</T:IDVersion>';

        $x .= '<T:IDFactura>';
        $x .= '<T:IDEmisorFactura>'.$this->e($issuerNif).'</T:IDEmisorFactura>';
        $x .= '<T:NumSerieFactura>'.$this->e($numSerie).'</T:NumSerieFactura>';
        $x .= '<T:FechaExpedicionFactura>'.$this->e($fechaAEAT).'</T:FechaExpedicionFactura>';
        $x .= '</T:IDFactura>';

        $x .= '<T:NombreRazonEmisor>'.$this->e($issuerName).'</T:NombreRazonEmisor>';

        $x .= '<T:Encadenamiento>';
        if (empty($huellaAnterior)) {
            $x .= '<T:PrimerRegistro>S</T:PrimerRegistro>';
        } else {
            $x .= '<T:RegistroAnterior>';
            $x .= '<T:IDEmisorFactura>'.$this->e($chain['issuerNif']).'</T:IDEmisorFactura>';
            $x .= '<T:NumSerieFactura>'.$this->e($chain['numSerie']).'</T:NumSerieFactura>';
            $x .= '<T:FechaExpedicionFactura>'.$this->e($chain['fecha']).'</T:FechaExpedicionFactura>';
            $x .= '<T:Huella>'.$this->e($huellaAnterior).'</T:Huella>';
            $x .= '</T:RegistroAnterior>';
        }
        $x .= '</T:Encadenamiento>';

        $x .= '<T:SistemaInformatico>';
        $x .= '<T:NombreRazon>'.$this->e($swVendorName).'</T:NombreRazon>';
        if ($swVendorNif) {
            $x .= '<T:NIF>'.$this->e($swVendorNif).'</T:NIF>';
        }
        $x .= '<T:NombreSistemaInformatico>'.$this->e($swName).'</T:NombreSistemaInformatico>';
        $x .= '<T:IdSistemaInformatico>'.$this->e(substr($swId, 0, 2)).'</T:IdSistemaInformatico>';
        $x .= '<T:Version>'.$this->e($swVersion).'</T:Version>';
        $x .= '<T:NumeroInstalacion>'.$this->e($swInstall).'</T:NumeroInstalacion>';
        $x .= '<T:TipoUsoPosibleSoloVerifactu>S</T:TipoUsoPosibleSoloVerifactu>';
        $x .= '<T:TipoUsoPosibleMultiOT>N</T:TipoUsoPosibleMultiOT>';
        $x .= '<T:IndicadorMultiplesOT>N</T:IndicadorMultiplesOT>';
        $x .= '</T:SistemaInformatico>';

        $x .= '<T:FechaHoraHusoGenRegistro>'.$this->e($fechaHoraGen).'</T:FechaHoraHusoGenRegistro>';
        $x .= '<T:TipoHuella>01</T:TipoHuella>';
        $x .= '<T:Huella>'.$huella.'</T:Huella>';
        $x .= '<T:GeneradoPor>E</T:GeneradoPor>';

        $x .= '</T:RegistroAnulacion>';
        $x .= '</RegistroFactura>';
        $x .= '</RegFactuSistemaFacturacion>';

        return $x;
    }

    /**
     * Send signed XML to AEAT via SOAP (production) or simulate (testing).
     */
    private function sendToAeat(string $signedXml, Invoice $invoice): object
    {
        $sifEnvironment = config('services.sif_aeat.environment');

        if ($sifEnvironment === 'production') {
            return $this->sendToAeatSoap($signedXml, $invoice);
        }

        // El modo simulado (que declara la factura "aceptada por la AEAT" sin
        // enviarla de verdad) SOLO está permitido fuera del entorno de
        // producción de la app. Si la app corre en producción pero
        // SIF_ENVIRONMENT no está puesto a "production", es una
        // configuración incorrecta: fallar alto en vez de fabricar una
        // aceptación falsa que marcaría facturas reales como verificadas.
        if (app()->isProduction()) {
            Log::critical('VerifactuService: SIF_ENVIRONMENT no está en "production" en un entorno de producción — se rechaza simular la aceptación de la AEAT.', [
                'invoice_id' => $invoice->id,
                'sif_environment' => $sifEnvironment,
            ]);

            throw new \Exception(__('Configuración de Verifactu inválida: en producción, SIF_ENVIRONMENT debe ser "production". No se puede simular la aceptación de la AEAT.'));
        }

        // Simulated response (solo entornos no productivos: local/testing/staging)
        return (object) [
            'EstadoEnvio' => __('Correcto'),
            'RespuestaLinea' => [
                (object) [
                    'EstadoRegistro' => __('Correcta'),
                    'CodigoErrorRegistro' => null,
                    'DescripcionErrorRegistro' => null,
                    'CSV' => 'VFT'.strtoupper(substr(hash('sha256', ($invoice->invoice_number ?? '').now()), 0, 16)),
                ],
            ],
        ];
    }

    /**
     * Perform the real AEAT SOAP call, hardened with mTLS (client cert),
     * connection/read timeouts and single-element-array normalization.
     */
    private function sendToAeatSoap(string $signedXml, Invoice $invoice): object
    {
        $wsdl = config('services.sif_aeat.wsdl');
        $endpoint = config('services.sif_aeat.endpoint');

        if (! file_exists($wsdl)) {
            throw new \Exception(__('Fichero WSDL no encontrado: :path', ['path' => $wsdl]));
        }

        $certs = $this->loadCertificate($invoice->user);
        $certFile = null;
        $previousSocketTimeout = ini_get('default_socket_timeout');

        try {
            $options = [
                'location' => $endpoint,
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 15,
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            ];

            if ($certs !== null) {
                // El SoapClient de PHP exige el certificado en un fichero PEM
                // (cert + clave privada); lo volcamos a un temporal con permisos
                // restrictivos y lo borramos siempre al terminar la llamada.
                $certFile = tempnam(sys_get_temp_dir(), 'vf_mtls_');
                file_put_contents($certFile, $certs['cert']."\n".$certs['pkey']);
                chmod($certFile, 0600);

                $options['local_cert'] = $certFile;
                $options['passphrase'] = (string) ($invoice->user->sif_cert_password ?? '');
            }

            // Timeout de lectura del socket, acotado a esta llamada y restaurado
            // siempre después (evita dejar el proceso colgado indefinidamente
            // si la AEAT no responde).
            ini_set('default_socket_timeout', 15);

            $client = new \SoapClient($wsdl, $options);

            // Operation name from SistemaFacturacion.wsdl
            return $client->__soapCall('RegFactuSistemaFacturacion', [
                'RegFactuSistemaFacturacion' => ['xmlRegistros' => $signedXml],
            ]);
        } finally {
            if ($certFile !== null && file_exists($certFile)) {
                @unlink($certFile);
            }
            ini_set('default_socket_timeout', $previousSocketTimeout !== false ? $previousSocketTimeout : 60);
        }
    }

    private function responseToXml(object $response): string
    {
        $xml = new \SimpleXMLElement('<RespuestaRegFactuSistemaFacturacion/>');
        $xml->addChild('EstadoEnvio', $response->EstadoEnvio ?? '');

        if (! empty($response->RespuestaLinea)) {
            $lineas = $xml->addChild('RespuestaLinea');
            foreach ($response->RespuestaLinea as $linea) {
                $item = $lineas->addChild('Linea');
                $item->addChild('EstadoRegistro', $linea->EstadoRegistro ?? '');
                $item->addChild('CodigoErrorRegistro', $linea->CodigoErrorRegistro ?? '');
                $item->addChild('DescripcionErrorRegistro', $linea->DescripcionErrorRegistro ?? '');
                $item->addChild('CSV', $linea->CSV ?? '');
            }
        }

        return $xml->asXML();
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
