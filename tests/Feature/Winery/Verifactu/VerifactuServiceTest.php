<?php

namespace Tests\Feature\Winery\Verifactu;

use App\Models\Invoice;
use App\Models\User;
use App\Services\VerifactuService;
use Illuminate\Http\UploadedFile;
use Tests\Feature\WineryTestCase;

/**
 * Tests del núcleo de cumplimiento Verifactu (AEAT): generación de la huella
 * SHA-256 y encadenamiento de registros. Son deterministas y NO contactan con
 * la AEAT — validan la fórmula del hash y la cadena, que es la obligación legal
 * crítica antes de pasar SIF_ENVIRONMENT a producción.
 */
class VerifactuServiceTest extends WineryTestCase
{
    // ── validación ────────────────────────────────────────────────────────────

    public function test_generate_xml_returns_errors_when_nif_missing(): void
    {
        $winery = $this->makeWineryWithNif('');
        $invoice = $this->makeInvoice($winery->id);

        $result = $this->service()->generateXml($invoice->fresh(['user']));

        $this->assertArrayHasKey('errors', $result);
        $this->assertNotEmpty($result['errors']);
        $this->assertArrayNotHasKey('huella', $result);
    }

    public function test_generate_xml_returns_errors_when_total_is_zero(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['total_amount' => 0]);

        $result = $this->service()->generateXml($invoice->fresh(['user']));

        $this->assertArrayHasKey('errors', $result);
    }

    // ── huella ──────────────────────────────────────────────────────────────

    public function test_generate_xml_produces_huella_matching_documented_formula(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertArrayHasKey('huella', $result);
        $this->assertArrayHasKey('fechaHoraGen', $result);

        // La huella es SHA-256 → 64 hex en mayúsculas
        $this->assertMatchesRegularExpression('/^[A-F0-9]{64}$/', $result['huella']);

        // Y debe coincidir exactamente con la fórmula recalculada de forma independiente
        $this->assertSame(
            $this->expectedHuella($invoice, $result['fechaHoraGen']),
            $result['huella']
        );
    }

    public function test_huella_appears_inside_generated_xml(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:Huella>'.$result['huella'].'</T:Huella>', $result['xml']);
        $this->assertStringContainsString('<T:TipoHuella>01</T:TipoHuella>', $result['xml']);
    }

    // ── encadenamiento ──────────────────────────────────────────────────────

    public function test_first_record_is_marked_when_no_chain(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:PrimerRegistro>S</T:PrimerRegistro>', $result['xml']);
        $this->assertStringNotContainsString('<T:RegistroAnterior>', $result['xml']);
    }

    public function test_previous_record_is_chained_when_chain_provided(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['invoice_number' => 'F-2026-0002'])->fresh(['user']);

        $previousHuella = strtoupper(hash('sha256', 'registro-anterior'));
        $chain = [
            'huella' => $previousHuella,
            'issuerNif' => '12345678Z',
            'numSerie' => 'F-2026-0001',
            'fecha' => '19-05-2026',
        ];

        $result = $this->service()->generateXml($invoice, $chain);

        $this->assertStringNotContainsString('<T:PrimerRegistro>', $result['xml']);
        $this->assertStringContainsString('<T:RegistroAnterior>', $result['xml']);
        $this->assertStringContainsString('<T:Huella>'.$previousHuella.'</T:Huella>', $result['xml']);
        $this->assertStringContainsString('<T:NumSerieFactura>F-2026-0001</T:NumSerieFactura>', $result['xml']);
    }

    /**
     * Integridad de la cadena: la huella anterior forma parte del hash, así que
     * la misma factura encadenada a registros distintos produce huellas distintas.
     */
    public function test_chaining_changes_resulting_huella(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $first = $this->service()->generateXml($invoice);

        $chain = [
            'huella' => strtoupper(hash('sha256', 'otro-registro')),
            'issuerNif' => '12345678Z',
            'numSerie' => 'F-2026-0000',
            'fecha' => '18-05-2026',
        ];
        $chained = $this->service()->generateXml($invoice, $chain);

        $this->assertNotSame($first['huella'], $chained['huella']);

        // Y la huella encadenada cuadra con la fórmula incluyendo la huella anterior
        $this->assertSame(
            $this->expectedHuella($invoice, $chained['fechaHoraGen'], $chain['huella']),
            $chained['huella']
        );
    }

    // ── tipo de factura ───────────────────────────────────────────────────────

    public function test_tipo_factura_is_f1_for_normal_invoice(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['corrective' => false])->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:TipoFactura>F1</T:TipoFactura>', $result['xml']);
    }

    public function test_tipo_factura_is_r1_for_corrective_invoice(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['corrective' => true])->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:TipoFactura>R1</T:TipoFactura>', $result['xml']);
    }

    // ── destinatario ──────────────────────────────────────────────────────────

    /**
     * En liquidaciones de vendimia no hay Client y el destinatario es el
     * viticultor. Sin su NIF la AEAT rechaza la F1, así que debe caer al DNI
     * del viticultor aunque billing_company_document esté vacío.
     */
    public function test_grape_purchase_falls_back_to_viticulturist_nif(): void
    {
        $winery = $this->makeWineryWithNif();
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'name' => 'Bodega Ejemplo Viticultor',
            'dni' => '87654321X',
        ]);

        $invoice = $this->makeInvoice($winery->id, [
            'invoice_type' => 'grape_purchase',
            'viticulturist_id' => $viticulturist->id,
            'billing_first_name' => $viticulturist->name,
        ])->fresh(['user', 'viticulturist']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:Destinatarios>', $result['xml']);
        $this->assertStringContainsString('<T:NIF>87654321X</T:NIF>', $result['xml']);
        $this->assertStringContainsString('Liquidación de vendimia', $result['xml']);
    }

    // ── firma con certificado del emisor ──────────────────────────────────────

    public function test_signxml_signs_with_the_issuers_own_certificate(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $winery = $this->makeWineryWithNif('12345678Z');
        app(\App\Services\VerifactuCertificateService::class)->store(
            $winery,
            $this->makeP12Upload('12345678Z'),
            'secret123'
        );
        $winery->refresh();

        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);
        $xml = $this->service()->generateXml($invoice)['xml'];

        $signed = $this->service()->signXml($xml, $winery);

        $this->assertStringContainsString('<ds:Signature', $signed);
        $this->assertStringContainsString('<ds:SignatureValue>', $signed);
    }

    public function test_signxml_returns_unsigned_in_testing_without_certificate(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $winery = $this->makeWineryWithNif('12345678Z');
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);
        $xml = $this->service()->generateXml($invoice)['xml'];

        $signed = $this->service()->signXml($xml, $winery);

        $this->assertSame($xml, $signed);
        $this->assertStringNotContainsString('<ds:Signature', $signed);
    }

    public function test_signxml_throws_when_certificate_expired(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $winery = $this->makeWineryWithNif('12345678Z');
        app(\App\Services\VerifactuCertificateService::class)->store(
            $winery,
            $this->makeP12Upload('12345678Z'),
            'secret123'
        );
        $winery->forceFill(['sif_cert_expires_at' => now()->subDay()])->save();
        $winery->refresh();

        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);
        $xml = $this->service()->generateXml($invoice)['xml'];

        $this->expectExceptionMessage('caducado');

        $this->service()->signXml($xml, $winery);
    }

    // ── QR ──────────────────────────────────────────────────────────────────

    public function test_qr_url_contains_invoice_identifiers(): void
    {
        $winery = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $url = $this->service()->buildQrUrl($invoice);

        $this->assertStringContainsString('ValidarQR', $url);
        $this->assertStringContainsString('nif=12345678Z', $url);
        $this->assertStringContainsString('numserie=F-2026-0001', $url);
        $this->assertStringContainsString('importe=121.00', $url);
    }

    private function makeWineryWithNif(string $dni = '12345678Z'): User
    {
        return User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
            'dni' => $dni,
        ]);
    }

    /**
     * Genera un .p12 autofirmado de prueba con el NIF dado incrustado en el
     * Subject, para probar la firma real con el certificado del emisor.
     */
    private function makeP12Upload(string $dni, string $password = 'secret123'): UploadedFile
    {
        $opensslConfig = [];
        foreach ([
            'C:\\Program Files\\Common Files\\SSL\\openssl.cnf',
            'C:\\Program Files\\Git\\mingw64\\etc\\ssl\\openssl.cnf',
            'C:\\Program Files\\Git\\usr\\ssl\\openssl.cnf',
            '/etc/ssl/openssl.cnf',
        ] as $candidate) {
            if (file_exists($candidate)) {
                $opensslConfig = ['config' => $candidate];
                break;
            }
        }

        $pkey = openssl_pkey_new($opensslConfig + ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $csr = openssl_csr_new(['commonName' => "TEST {$dni}", 'countryName' => 'ES'], $pkey, $opensslConfig);
        $cert = openssl_csr_sign($csr, null, $pkey, 365, $opensslConfig);

        openssl_pkcs12_export($cert, $p12, $pkey, $password);

        $path = tempnam(sys_get_temp_dir(), 'verifactu_test_').'.p12';
        file_put_contents($path, $p12);

        return new UploadedFile($path, 'certificado.p12', 'application/x-pkcs12', null, true);
    }

    private function makeInvoice(int $wineryId, array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => $wineryId,
            'invoice_number' => 'F-2026-0001',
            'invoice_date' => '2026-05-20',
            'tax_base' => 100.00,
            'tax_rate' => 21.00,
            'tax_amount' => 21.00,
            'total_amount' => 121.00,
            'status' => 'sent',
            'sif_status' => 'pendiente',
            'invoice_type' => 'product_sale',
        ], $attrs));
    }

    private function service(): VerifactuService
    {
        return app(VerifactuService::class);
    }

    /**
     * Recalcula la huella esperada con la misma fórmula documentada en el
     * servicio: SHA-256 de NIF+NumSerie+Fecha+TipoFactura+CuotaTotal+
     * ImporteTotal+HuellaAnterior+FechaHoraGen, en mayúsculas.
     */
    private function expectedHuella(Invoice $invoice, string $fechaHoraGen, string $huellaAnterior = ''): string
    {
        $input =
            trim($invoice->user->dni)
            .$invoice->invoice_number
            .$invoice->invoice_date->format('d-m-Y')
            .($invoice->corrective ? 'R1' : 'F1')
            .number_format((float) $invoice->tax_amount, 2, '.', '')
            .number_format((float) $invoice->total_amount, 2, '.', '')
            .$huellaAnterior
            .$fechaHoraGen;

        return strtoupper(hash('sha256', $input));
    }
}
