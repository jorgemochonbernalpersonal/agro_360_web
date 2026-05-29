<?php

namespace Tests\Feature\Winery\Verifactu;

use App\Models\Invoice;
use App\Models\User;
use App\Services\VerifactuService;
use Tests\Feature\WineryTestCase;

/**
 * Tests del núcleo de cumplimiento Verifactu (AEAT): generación de la huella
 * SHA-256 y encadenamiento de registros. Son deterministas y NO contactan con
 * la AEAT — validan la fórmula del hash y la cadena, que es la obligación legal
 * crítica antes de pasar SIF_ENVIRONMENT a producción.
 */
class VerifactuServiceTest extends WineryTestCase
{
    private function makeWineryWithNif(string $dni = '12345678Z'): User
    {
        return User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
            'dni'               => $dni,
        ]);
    }

    private function makeInvoice(int $wineryId, array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id'        => $wineryId,
            'invoice_number' => 'F-2026-0001',
            'invoice_date'   => '2026-05-20',
            'tax_base'       => 100.00,
            'tax_rate'       => 21.00,
            'tax_amount'     => 21.00,
            'total_amount'   => 121.00,
            'status'         => 'sent',
            'sif_status'     => 'pendiente',
            'invoice_type'   => 'product_sale',
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
            . $invoice->invoice_number
            . $invoice->invoice_date->format('d-m-Y')
            . ($invoice->corrective ? 'R1' : 'F1')
            . number_format((float) $invoice->tax_amount, 2, '.', '')
            . number_format((float) $invoice->total_amount, 2, '.', '')
            . $huellaAnterior
            . $fechaHoraGen;

        return strtoupper(hash('sha256', $input));
    }

    // ── validación ────────────────────────────────────────────────────────────

    public function test_generate_xml_returns_errors_when_nif_missing(): void
    {
        $winery  = $this->makeWineryWithNif('');
        $invoice = $this->makeInvoice($winery->id);

        $result = $this->service()->generateXml($invoice->fresh(['user']));

        $this->assertArrayHasKey('errors', $result);
        $this->assertNotEmpty($result['errors']);
        $this->assertArrayNotHasKey('huella', $result);
    }

    public function test_generate_xml_returns_errors_when_total_is_zero(): void
    {
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['total_amount' => 0]);

        $result = $this->service()->generateXml($invoice->fresh(['user']));

        $this->assertArrayHasKey('errors', $result);
    }

    // ── huella ──────────────────────────────────────────────────────────────

    public function test_generate_xml_produces_huella_matching_documented_formula(): void
    {
        $winery  = $this->makeWineryWithNif();
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
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:Huella>' . $result['huella'] . '</T:Huella>', $result['xml']);
        $this->assertStringContainsString('<T:TipoHuella>01</T:TipoHuella>', $result['xml']);
    }

    // ── encadenamiento ──────────────────────────────────────────────────────

    public function test_first_record_is_marked_when_no_chain(): void
    {
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:PrimerRegistro>S</T:PrimerRegistro>', $result['xml']);
        $this->assertStringNotContainsString('<T:RegistroAnterior>', $result['xml']);
    }

    public function test_previous_record_is_chained_when_chain_provided(): void
    {
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['invoice_number' => 'F-2026-0002'])->fresh(['user']);

        $previousHuella = strtoupper(hash('sha256', 'registro-anterior'));
        $chain = [
            'huella'    => $previousHuella,
            'issuerNif' => '12345678Z',
            'numSerie'  => 'F-2026-0001',
            'fecha'     => '19-05-2026',
        ];

        $result = $this->service()->generateXml($invoice, $chain);

        $this->assertStringNotContainsString('<T:PrimerRegistro>', $result['xml']);
        $this->assertStringContainsString('<T:RegistroAnterior>', $result['xml']);
        $this->assertStringContainsString('<T:Huella>' . $previousHuella . '</T:Huella>', $result['xml']);
        $this->assertStringContainsString('<T:NumSerieFactura>F-2026-0001</T:NumSerieFactura>', $result['xml']);
    }

    /**
     * Integridad de la cadena: la huella anterior forma parte del hash, así que
     * la misma factura encadenada a registros distintos produce huellas distintas.
     */
    public function test_chaining_changes_resulting_huella(): void
    {
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $first = $this->service()->generateXml($invoice);

        $chain = [
            'huella'    => strtoupper(hash('sha256', 'otro-registro')),
            'issuerNif' => '12345678Z',
            'numSerie'  => 'F-2026-0000',
            'fecha'     => '18-05-2026',
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
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['corrective' => false])->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:TipoFactura>F1</T:TipoFactura>', $result['xml']);
    }

    public function test_tipo_factura_is_r1_for_corrective_invoice(): void
    {
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id, ['corrective' => true])->fresh(['user']);

        $result = $this->service()->generateXml($invoice);

        $this->assertStringContainsString('<T:TipoFactura>R1</T:TipoFactura>', $result['xml']);
    }

    // ── QR ──────────────────────────────────────────────────────────────────

    public function test_qr_url_contains_invoice_identifiers(): void
    {
        $winery  = $this->makeWineryWithNif();
        $invoice = $this->makeInvoice($winery->id)->fresh(['user']);

        $url = $this->service()->buildQrUrl($invoice);

        $this->assertStringContainsString('ValidarQR', $url);
        $this->assertStringContainsString('nif=12345678Z', $url);
        $this->assertStringContainsString('numserie=F-2026-0001', $url);
        $this->assertStringContainsString('importe=121.00', $url);
    }
}
