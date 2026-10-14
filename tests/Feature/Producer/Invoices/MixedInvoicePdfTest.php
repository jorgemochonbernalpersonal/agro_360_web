<?php

namespace Tests\Feature\Producer\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use Tests\Feature\ProducerTestCase;

/**
 * Tests para los PDF de albaranes mixtos del producer.
 *
 * Regresión: el listado enlazaba a producer.invoices.mixed.pdf y
 * .delivery-note-pdf sin que las rutas existieran, así que la pantalla
 * reventaba con 500 en cuanto había un albarán (delivery_note_code se puebla
 * al crear) o nada más emitir una factura, que redirige justo ahí.
 */
class MixedInvoicePdfTest extends ProducerTestCase
{
    private \App\Models\User $producer;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);

        $client = Client::factory()->create(['user_id' => $this->producer->id]);

        $this->invoice = Invoice::factory()->create([
            'user_id' => $this->producer->id,
            'invoice_type' => 'producer_sale',
            'client_id' => $client->id,
            'delivery_note_code' => 'ALB-2026-0001',
            'invoice_number' => 'FAC-2026-0001',
        ]);
    }

    public function test_index_renders_with_an_invoiced_delivery_note(): void
    {
        $this->get(route('producer.invoices.mixed.index'))
            ->assertOk()
            ->assertSee('ALB-2026-0001');
    }

    public function test_invoice_pdf_route_responds(): void
    {
        $this->get(route('producer.invoices.mixed.pdf', $this->invoice->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_delivery_note_pdf_route_responds(): void
    {
        $this->get(route('producer.invoices.mixed.delivery-note-pdf', $this->invoice->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_another_producer_cannot_download_the_pdf(): void
    {
        $this->actingAs($this->makeOtherProducer());

        $this->get(route('producer.invoices.mixed.pdf', $this->invoice->id))
            ->assertNotFound();
    }
}
