<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoicingSetting;
use App\Models\SifRecord;
use Illuminate\Support\Facades\Log;

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
            $signedXml = $this->signXml($xml);

            // 4. Send to AEAT (simulated in test mode)
            $response = $this->sendToAeat($signedXml, $invoice);

            // 5. Parse response
            $csv       = $response->RespuestaLinea[0]->CSV ?? null;
            $errorCode = $response->RespuestaLinea[0]->CodigoErrorRegistro ?? null;
            $errorDesc = $response->RespuestaLinea[0]->DescripcionErrorRegistro ?? null;
            $isOk      = $response->EstadoEnvio === 'Correcto' && !$errorCode;

            // 6. Log in sif_records (store huella for chaining)
            SifRecord::create([
                'invoice_id'    => $invoice->id,
                'tipo_registro' => 'ALTA',
                'csv'           => $csv,
                'huella'        => $huella,
                'request_xml'   => $xml,
                'response_xml'  => $this->responseToXml($response),
                'status'        => $isOk ? 'OK' : 'ER',
                'error_message' => $errorCode ? "[{$errorCode}] {$errorDesc}" : null,
            ]);

            // 7. Update invoice
            $invoice->update([
                'sif_status'      => $isOk ? 'aceptado' : 'error',
                'is_verified_aet' => $isOk,
                'sif_uuid'        => $csv,
                'sif_hash'        => $huella,
                'sif_sent_at'     => now(),
                'sif_response'    => $response->EstadoEnvio,
            ]);

            // 8. Email after successful verification
            if ($isOk && $sendEmail) {
                $this->sendVerifiedEmail($invoice);
            }

            return [
                'success' => $isOk,
                'csv'     => $csv,
                'huella'  => $huella,
                'errors'  => $isOk ? [] : ["[{$errorCode}] {$errorDesc}"],
            ];

        } catch (\Exception $e) {
            Log::error('VerifactuService::send', [
                'invoice_id' => $invoice->id,
                'message'    => $e->getMessage(),
            ]);

            SifRecord::create([
                'invoice_id'    => $invoice->id,
                'tipo_registro' => 'ALTA',
                'status'        => 'ER',
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
            $chain        = $this->resolveChain($invoice->user->dni ?? '');
            $fechaHoraGen = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');
            $huella       = $this->buildAnulacionHuella($invoice, $chain, $fechaHoraGen);
            $xml          = $this->buildAnulacionXml($invoice, $chain, $huella, $fechaHoraGen);
            $signedXml    = $this->signXml($xml);
            $response     = $this->sendToAeat($signedXml, $invoice);

            $csv       = $response->RespuestaLinea[0]->CSV ?? null;
            $errorCode = $response->RespuestaLinea[0]->CodigoErrorRegistro ?? null;
            $errorDesc = $response->RespuestaLinea[0]->DescripcionErrorRegistro ?? null;
            $isOk      = $response->EstadoEnvio === 'Correcto' && !$errorCode;

            SifRecord::create([
                'invoice_id'    => $invoice->id,
                'tipo_registro' => 'ANULACION',
                'huella'        => $huella,
                'request_xml'   => $xml,
                'response_xml'  => $this->responseToXml($response),
                'status'        => $isOk ? 'OK' : 'ER',
                'error_message' => $errorCode ? "[{$errorCode}] {$errorDesc}" : null,
            ]);

            if ($isOk) {
                $invoice->update([
                    'sif_status'      => 'pendiente',
                    'is_verified_aet' => false,
                    'sif_uuid'        => null,
                    'sif_sent_at'     => null,
                ]);
            }

            return [
                'success' => $isOk,
                'errors'  => $isOk ? [] : ["[{$errorCode}] {$errorDesc}"],
            ];

        } catch (\Exception $e) {
            Log::error('VerifactuService::cancel', [
                'invoice_id' => $invoice->id,
                'message'    => $e->getMessage(),
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

        $numSerie   = $invoice->invoice_number ?? null;
        $fecha      = $invoice->invoice_date ?? null;
        $issuerNif  = trim($invoice->user->dni ?? '');
        $invSettings = InvoicingSetting::forUser($invoice->user->id)->first();
        $issuerName  = trim($invSettings?->issuer_legal_name ?? $invoice->user->name ?? '');

        if (empty($numSerie)) {
            $errors[] = 'El número de factura es obligatorio para Verifactu.';
        }
        if (empty($fecha)) {
            $errors[] = 'La fecha de factura es obligatoria para Verifactu.';
        }
        if (empty($issuerNif)) {
            $errors[] = 'Tu NIF/DNI no está configurado. Ve a Ajustes y añádelo.';
        }
        if ((float) $invoice->total_amount <= 0) {
            $errors[] = 'El importe total debe ser mayor que cero.';
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // ── Derived values ──────────────────────────────────────────────────
        $fechaAEAT    = $fecha->format('d-m-Y');
        $tipoFactura  = $invoice->corrective ? 'R1' : 'F1';
        $cuotaTotal   = number_format((float) $invoice->tax_amount, 2, '.', '');
        $importeTotal = number_format((float) $invoice->total_amount, 2, '.', '');
        $descOp       = $invoice->invoice_type === 'grape_purchase'
            ? 'Liquidación de vendimia'
            : 'Venta de productos';

        // FechaHoraHusoGenRegistro — ISO 8601 with timezone offset (mandatory)
        $fechaHoraGen = now()->setTimezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');

        // ── Huella (hash) ────────────────────────────────────────────────────
        // SHA-256 of: NIF + NumSerie + Fecha + TipoFactura + CuotaTotal +
        //             ImporteTotal + HuellaAnterior + FechaHoraGen
        // All values concatenated WITHOUT separator, uppercase hex result.
        $huellaAnterior = $chain['huella'] ?? '';
        $hashInput =
            $issuerNif .
            $numSerie .
            $fechaAEAT .
            $tipoFactura .
            $cuotaTotal .
            $importeTotal .
            $huellaAnterior .
            $fechaHoraGen;
        $huella = strtoupper(hash('sha256', $hashInput));

        // ── Recipient ────────────────────────────────────────────────────────
        $recipientNif  = $invoice->billing_company_document
            ?? $invoice->client?->company_document
            ?? $invoice->client?->particular_document
            ?? null;
        $recipientName = $invoice->billing_company_name
            ?? trim(($invoice->billing_first_name ?? '') . ' ' . ($invoice->billing_last_name ?? ''))
            ?: null;

        // ── Software block (SistemaInformatico) ─────────────────────────────
        $swVendorName  = config('services.sif_software.vendor_name', 'Agro365');
        $swVendorNif   = config('services.sif_software.vendor_nif', '');
        $swName        = config('services.sif_software.name', 'Agro365');
        $swId          = config('services.sif_software.id', 'A3');           // max 2 chars
        $swVersion     = config('services.sif_software.version', '1.0.0');
        $swInstall     = 'AGR-' . $invoice->user->id; // una instalación por usuario (max 100)

        // ── Build XML ────────────────────────────────────────────────────────
        $ns   = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroLR.xsd';
        $nsT  = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroInformacion.xsd';

        $x  = '<?xml version="1.0" encoding="UTF-8"?>';
        $x .= "<RegFactuSistemaFacturacion xmlns=\"{$ns}\" xmlns:T=\"{$nsT}\">";

        // Cabecera
        $x .= '<Cabecera>';
        $x .= '<IDVersionSuministro>1.0</IDVersionSuministro>';
        $x .= '<Titular>';
        $x .= '<T:NombreRazon>' . $this->e($issuerName) . '</T:NombreRazon>';
        $x .= '<T:NIF>' . $this->e($issuerNif) . '</T:NIF>';
        $x .= '</Titular>';
        $x .= '<TipoComunicacion>A0</TipoComunicacion>';
        $x .= '</Cabecera>';

        // RegistroFactura > RegistroAlta
        $x .= '<RegistroFactura>';
        $x .= '<T:RegistroAlta>';
        $x .= '<T:IDVersion>1.0</T:IDVersion>';

        // IDFactura (corrected field names vs SII)
        $x .= '<T:IDFactura>';
        $x .= '<T:IDEmisorFactura>' . $this->e($issuerNif) . '</T:IDEmisorFactura>';
        $x .= '<T:NumSerieFactura>' . $this->e($numSerie) . '</T:NumSerieFactura>';
        $x .= '<T:FechaExpedicionFactura>' . $this->e($fechaAEAT) . '</T:FechaExpedicionFactura>';
        $x .= '</T:IDFactura>';

        $x .= '<T:NombreRazonEmisor>' . $this->e($issuerName) . '</T:NombreRazonEmisor>';
        $x .= '<T:TipoFactura>' . $tipoFactura . '</T:TipoFactura>';

        // Rectificativa fields
        if ($invoice->corrective && $invoice->correctedInvoice) {
            $x .= '<T:TipoRectificativa>I</T:TipoRectificativa>';
            $x .= '<T:FacturasRectificadas>';
            $x .= '<T:IDFacturaRectificada>';
            $x .= '<T:IDEmisorFactura>' . $this->e($issuerNif) . '</T:IDEmisorFactura>';
            $x .= '<T:NumSerieFactura>' . $this->e($invoice->correctedInvoice->invoice_number) . '</T:NumSerieFactura>';
            $x .= '<T:FechaExpedicionFactura>' . $this->e($invoice->correctedInvoice->invoice_date->format('d-m-Y')) . '</T:FechaExpedicionFactura>';
            $x .= '</T:IDFacturaRectificada>';
            $x .= '</T:FacturasRectificadas>';
        }

        $x .= '<T:DescripcionOperacion>' . $this->e($descOp) . '</T:DescripcionOperacion>';

        // Desglose (corrected field names)
        $x .= '<T:Desglose>';
        $x .= '<T:DetalleDesglose>';
        // CalificacionOperacion: S1=sujeto y no exento, S2=ISP; OperacionExenta: E1–E8
        if ((float) $invoice->tax_rate > 0) {
            $x .= '<T:CalificacionOperacion>S1</T:CalificacionOperacion>';
            $x .= '<T:TipoImpositivo>' . number_format((float) $invoice->tax_rate, 2, '.', '') . '</T:TipoImpositivo>';
            $x .= '<T:BaseImponibleOimporteNoSujeto>' . number_format((float) $invoice->tax_base, 2, '.', '') . '</T:BaseImponibleOimporteNoSujeto>';
            $x .= '<T:CuotaRepercutida>' . $cuotaTotal . '</T:CuotaRepercutida>';
        } else {
            // Exento (IVA 0%)
            $x .= '<T:OperacionExenta>E1</T:OperacionExenta>';
            $x .= '<T:BaseImponibleOimporteNoSujeto>' . number_format((float) $invoice->tax_base, 2, '.', '') . '</T:BaseImponibleOimporteNoSujeto>';
        }
        $x .= '</T:DetalleDesglose>';
        $x .= '</T:Desglose>';

        // Totales a nivel de factura
        $x .= '<T:CuotaTotal>' . $cuotaTotal . '</T:CuotaTotal>';
        $x .= '<T:ImporteTotal>' . $importeTotal . '</T:ImporteTotal>';

        // Destinatarios (optional per schema, but expected for F1)
        if ($tipoFactura === 'F1' && ($recipientNif || $recipientName)) {
            $x .= '<T:Destinatarios>';
            $x .= '<T:IDDestinatario>';
            $x .= '<T:NombreRazon>' . $this->e($recipientName ?: 'Destinatario') . '</T:NombreRazon>';
            if ($recipientNif) {
                $x .= '<T:NIF>' . $this->e($recipientNif) . '</T:NIF>';
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
            $x .= '<T:IDEmisorFactura>' . $this->e($chain['issuerNif']) . '</T:IDEmisorFactura>';
            $x .= '<T:NumSerieFactura>' . $this->e($chain['numSerie']) . '</T:NumSerieFactura>';
            $x .= '<T:FechaExpedicionFactura>' . $this->e($chain['fecha']) . '</T:FechaExpedicionFactura>';
            $x .= '<T:Huella>' . $this->e($huellaAnterior) . '</T:Huella>';
            $x .= '</T:RegistroAnterior>';
        }
        $x .= '</T:Encadenamiento>';

        // SistemaInformatico (mandatory — identifies the software)
        $x .= '<T:SistemaInformatico>';
        $x .= '<T:NombreRazon>' . $this->e($swVendorName) . '</T:NombreRazon>';
        if ($swVendorNif) {
            $x .= '<T:NIF>' . $this->e($swVendorNif) . '</T:NIF>';
        }
        $x .= '<T:NombreSistemaInformatico>' . $this->e($swName) . '</T:NombreSistemaInformatico>';
        $x .= '<T:IdSistemaInformatico>' . $this->e(substr($swId, 0, 2)) . '</T:IdSistemaInformatico>';
        $x .= '<T:Version>' . $this->e($swVersion) . '</T:Version>';
        $x .= '<T:NumeroInstalacion>' . $this->e($swInstall) . '</T:NumeroInstalacion>';
        $x .= '<T:TipoUsoPosibleSoloVerifactu>S</T:TipoUsoPosibleSoloVerifactu>';
        $x .= '<T:TipoUsoPosibleMultiOT>N</T:TipoUsoPosibleMultiOT>';
        $x .= '<T:IndicadorMultiplesOT>N</T:IndicadorMultiplesOT>';
        $x .= '</T:SistemaInformatico>';

        // Timestamp with timezone (mandatory)
        $x .= '<T:FechaHoraHusoGenRegistro>' . $this->e($fechaHoraGen) . '</T:FechaHoraHusoGenRegistro>';

        // Huella
        $x .= '<T:TipoHuella>01</T:TipoHuella>';
        $x .= '<T:Huella>' . $huella . '</T:Huella>';
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
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
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
        $nif      = urlencode($invoice->user->dni ?? '');
        $numSerie = urlencode($invoice->invoice_number ?? '');
        $fecha    = urlencode($invoice->invoice_date?->format('d-m-Y') ?? '');
        $importe  = urlencode(number_format((float) $invoice->total_amount, 2, '.', ''));

        return "https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR"
            . "?nif={$nif}&numserie={$numSerie}&fecha={$fecha}&importe={$importe}";
    }

    /**
     * Sign XML with PKCS#12 certificate (XMLDSig RSA-SHA256).
     * In testing mode without a cert, returns the XML unsigned.
     */
    public function signXml(string $xmlString): string
    {
        $certPath    = config('services.sif_cert.path');
        $certPassword = config('services.sif_cert.password', '');
        $environment  = config('services.sif_aeat.environment', 'testing');

        if ($environment !== 'production' && (!$certPath || !file_exists($certPath))) {
            return $xmlString;
        }

        if (!file_exists($certPath)) {
            throw new \Exception('Certificado no encontrado en: ' . $certPath);
        }

        $pkcs12 = file_get_contents($certPath);
        $certs  = [];
        if (!openssl_pkcs12_read($pkcs12, $certs, $certPassword)) {
            throw new \Exception('No se pudo abrir el certificado PKCS#12. Verifica la contraseña.');
        }

        $dom       = new \DOMDocument();
        $dom->loadXML($xmlString);
        $canonical   = $dom->C14N();
        $digestValue = base64_encode(hash('sha256', $canonical, true));

        $signedInfo =
            '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'
            . '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>'
            . '<ds:Reference URI="">'
            . '<ds:Transforms>'
            . '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>'
            . '<ds:Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'
            . '</ds:Transforms>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue>' . $digestValue . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '</ds:SignedInfo>';

        $signedInfoDom = new \DOMDocument();
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
            . $signedInfo
            . '<ds:SignatureValue>' . base64_encode($signature) . '</ds:SignatureValue>'
            . '<ds:KeyInfo><ds:X509Data><ds:X509Certificate>' . $certClean . '</ds:X509Certificate></ds:X509Data></ds:KeyInfo>'
            . '</ds:Signature>';

        return str_replace(
            '</RegFactuSistemaFacturacion>',
            $dsSignature . '</RegFactuSistemaFacturacion>',
            $xmlString
        );
    }

    /**
     * Return current environment configuration status.
     */
    public function getConfig(): array
    {
        $certPath = config('services.sif_cert.path');

        return [
            'environment'     => config('services.sif_aeat.environment', 'testing'),
            'endpoint'        => config('services.sif_aeat.endpoint'),
            'cert_path'       => $certPath,
            'cert_configured' => !empty($certPath) && file_exists($certPath),
            'is_production'   => config('services.sif_aeat.environment') === 'production',
        ];
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

        $last = SifRecord::whereHas('invoice', fn($q) => $q->whereHas('user', fn($q2) => $q2->where('dni', $issuerNif)))
            ->where('status', 'OK')
            ->whereNotNull('huella')
            ->latest()
            ->first();

        if (!$last) {
            return [];
        }

        $inv = $last->invoice;

        return [
            'huella'    => $last->huella,
            'issuerNif' => $issuerNif,
            'numSerie'  => $inv->invoice_number,
            'fecha'     => $inv->invoice_date?->format('d-m-Y') ?? '',
        ];
    }

    /**
     * Build Huella for a cancellation record (RegistroAnulacion).
     * Same 8-field SHA-256 formula as RegistroAlta.
     */
    private function buildAnulacionHuella(Invoice $invoice, array $chain, string $fechaHoraGen): string
    {
        $issuerNif      = $invoice->user->dni ?? '';
        $numSerie       = $invoice->invoice_number ?? '';
        $fechaAEAT      = $invoice->invoice_date?->format('d-m-Y') ?? '';
        $tipoFactura    = $invoice->corrective ? 'R1' : 'F1';
        $huellaAnterior = $chain['huella'] ?? '';

        return strtoupper(hash('sha256',
            $issuerNif . $numSerie . $fechaAEAT . $tipoFactura .
            number_format((float) $invoice->tax_amount, 2, '.', '') .
            number_format((float) $invoice->total_amount, 2, '.', '') .
            $huellaAnterior . $fechaHoraGen
        ));
    }

    /**
     * Build the RegistroAnulacion XML.
     */
    private function buildAnulacionXml(Invoice $invoice, array $chain, string $huella, string $fechaHoraGen): string
    {
        $issuerNif  = $invoice->user->dni ?? '';
        $invSettings = InvoicingSetting::forUser($invoice->user->id)->first();
        $issuerName  = trim($invSettings?->issuer_legal_name ?? $invoice->user->name ?? '');
        $numSerie    = $invoice->invoice_number ?? '';
        $fechaAEAT   = $invoice->invoice_date?->format('d-m-Y') ?? '';
        $huellaAnterior = $chain['huella'] ?? '';

        $swVendorName = config('services.sif_software.vendor_name', 'Agro365');
        $swVendorNif  = config('services.sif_software.vendor_nif', '');
        $swName       = config('services.sif_software.name', 'Agro365');
        $swId         = config('services.sif_software.id', 'A3');
        $swVersion    = config('services.sif_software.version', '1.0.0');
        $swInstall    = 'AGR-' . $invoice->user->id;

        $ns  = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroLR.xsd';
        $nsT = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SuministroInformacion.xsd';

        $x  = '<?xml version="1.0" encoding="UTF-8"?>';
        $x .= "<RegFactuSistemaFacturacion xmlns=\"{$ns}\" xmlns:T=\"{$nsT}\">";

        $x .= '<Cabecera>';
        $x .= '<IDVersionSuministro>1.0</IDVersionSuministro>';
        $x .= '<Titular>';
        $x .= '<T:NombreRazon>' . $this->e($issuerName) . '</T:NombreRazon>';
        $x .= '<T:NIF>' . $this->e($issuerNif) . '</T:NIF>';
        $x .= '</Titular>';
        $x .= '<TipoComunicacion>A1</TipoComunicacion>';
        $x .= '</Cabecera>';

        $x .= '<RegistroFactura>';
        $x .= '<T:RegistroAnulacion>';
        $x .= '<T:IDVersion>1.0</T:IDVersion>';

        $x .= '<T:IDFactura>';
        $x .= '<T:IDEmisorFactura>' . $this->e($issuerNif) . '</T:IDEmisorFactura>';
        $x .= '<T:NumSerieFactura>' . $this->e($numSerie) . '</T:NumSerieFactura>';
        $x .= '<T:FechaExpedicionFactura>' . $this->e($fechaAEAT) . '</T:FechaExpedicionFactura>';
        $x .= '</T:IDFactura>';

        $x .= '<T:NombreRazonEmisor>' . $this->e($issuerName) . '</T:NombreRazonEmisor>';

        $x .= '<T:Encadenamiento>';
        if (empty($huellaAnterior)) {
            $x .= '<T:PrimerRegistro>S</T:PrimerRegistro>';
        } else {
            $x .= '<T:RegistroAnterior>';
            $x .= '<T:IDEmisorFactura>' . $this->e($chain['issuerNif']) . '</T:IDEmisorFactura>';
            $x .= '<T:NumSerieFactura>' . $this->e($chain['numSerie']) . '</T:NumSerieFactura>';
            $x .= '<T:FechaExpedicionFactura>' . $this->e($chain['fecha']) . '</T:FechaExpedicionFactura>';
            $x .= '<T:Huella>' . $this->e($huellaAnterior) . '</T:Huella>';
            $x .= '</T:RegistroAnterior>';
        }
        $x .= '</T:Encadenamiento>';

        $x .= '<T:SistemaInformatico>';
        $x .= '<T:NombreRazon>' . $this->e($swVendorName) . '</T:NombreRazon>';
        if ($swVendorNif) {
            $x .= '<T:NIF>' . $this->e($swVendorNif) . '</T:NIF>';
        }
        $x .= '<T:NombreSistemaInformatico>' . $this->e($swName) . '</T:NombreSistemaInformatico>';
        $x .= '<T:IdSistemaInformatico>' . $this->e(substr($swId, 0, 2)) . '</T:IdSistemaInformatico>';
        $x .= '<T:Version>' . $this->e($swVersion) . '</T:Version>';
        $x .= '<T:NumeroInstalacion>' . $this->e($swInstall) . '</T:NumeroInstalacion>';
        $x .= '<T:TipoUsoPosibleSoloVerifactu>S</T:TipoUsoPosibleSoloVerifactu>';
        $x .= '<T:TipoUsoPosibleMultiOT>N</T:TipoUsoPosibleMultiOT>';
        $x .= '<T:IndicadorMultiplesOT>N</T:IndicadorMultiplesOT>';
        $x .= '</T:SistemaInformatico>';

        $x .= '<T:FechaHoraHusoGenRegistro>' . $this->e($fechaHoraGen) . '</T:FechaHoraHusoGenRegistro>';
        $x .= '<T:TipoHuella>01</T:TipoHuella>';
        $x .= '<T:Huella>' . $huella . '</T:Huella>';
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
        if (config('services.sif_aeat.environment') === 'production') {
            $wsdl     = config('services.sif_aeat.wsdl');
            $endpoint = config('services.sif_aeat.endpoint');

            if (!file_exists($wsdl)) {
                throw new \Exception('Fichero WSDL no encontrado: ' . $wsdl);
            }

            $client = new \SoapClient($wsdl, [
                'location'   => $endpoint,
                'trace'      => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            // Operation name from SistemaFacturacion.wsdl
            return $client->__soapCall('RegFactuSistemaFacturacion', [
                'RegFactuSistemaFacturacion' => ['xmlRegistros' => $signedXml],
            ]);
        }

        // Simulated response
        return (object) [
            'EstadoEnvio'    => 'Correcto',
            'RespuestaLinea' => [
                (object) [
                    'EstadoRegistro'           => 'Correcta',
                    'CodigoErrorRegistro'      => null,
                    'DescripcionErrorRegistro' => null,
                    'CSV'                      => 'VFT' . strtoupper(substr(hash('sha256', ($invoice->invoice_number ?? '') . now()), 0, 16)),
                ],
            ],
        ];
    }

    /**
     * Send verified invoice PDF by email to the client.
     */
    public function sendVerifiedEmail(Invoice $invoice): void
    {
        $email = $invoice->billing_email ?? $invoice->client?->email ?? null;

        if (!$email) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\InvoiceVerifiedMail($invoice));
        } catch (\Exception $e) {
            Log::warning('VerifactuService: could not send verified email', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function responseToXml(object $response): string
    {
        $xml = new \SimpleXMLElement('<RespuestaRegFactuSistemaFacturacion/>');
        $xml->addChild('EstadoEnvio', $response->EstadoEnvio ?? '');

        if (!empty($response->RespuestaLinea)) {
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
