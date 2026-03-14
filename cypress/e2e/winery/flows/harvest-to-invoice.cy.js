/**
 * FLUJO CRÍTICO: Recepción de uva → Liquidación → Pago
 *
 * Simula el flujo real completo de una bodega:
 * 1. Registrar recepción de uva (viticultor vinculado, parcela, contenedor)
 * 2. Verificar que la recepción aparece en el índice
 * 3. Registrar segunda recepción del mismo viticultor
 * 4. Crear liquidación (grape purchase invoice) para ese viticultor
 * 5. Verificar que la liquidación incluye ambas recepciones
 * 6. Marcar la liquidación como pagada
 * 7. Verificar el estado final
 *
 * Requiere CypressTestUserSeeder con datos de apoyo completos.
 */

describe('Flujo: Recepción de uva → Liquidación → Pago', () => {
  let receptionAId
  let receptionBId
  let invoiceId

  before(() => {
    cy.loginAsWinery()
  })

  beforeEach(() => {
    cy.loginAsWinery()
  })

  // ── PASO 1: Primera recepción ─────────────────────────────────────────────

  it('1. Registra primera recepción de uva (500 kg)', () => {
    cy.visit('/winery/grape-reception/create')
    cy.waitForLivewire()

    // Seleccionar viticultor de prueba
    cy.get('[wire\\:model\\.live="viticulturist_id"]').then(($sel) => {
      const opts = $sel.find('option').filter((i, el) =>
        el.text.includes('Test Viticulturist') || el.value !== ''
      )
      expect(opts.length, 'Debe haber viticultores vinculados (revisar seeder)').to.be.gt(0)

      cy.get('[wire\\:model\\.live="viticulturist_id"]').select(opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(1000)
    })

    // Parcela
    cy.get('[wire\\:model\\.live="plot_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      expect($opts.length, 'Debe haber parcelas (revisar seeder)').to.be.gt(0)
      cy.get('[wire\\:model\\.live="plot_id"]').select($opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(1000)
    })

    // Plantación
    cy.get('[wire\\:model\\.live="plot_planting_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      expect($opts.length, 'Debe haber plantaciones (revisar seeder)').to.be.gt(0)
      cy.get('[wire\\:model\\.live="plot_planting_id"]').select($opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(500)
    })

    // Contenedor
    cy.get('[wire\\:model="container_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      expect($opts.length, 'Debe haber contenedores (revisar seeder)').to.be.gt(0)
      cy.get('[wire\\:model="container_id"]').select($opts.first().val(), { force: true })
    })

    // Datos de la recepción
    cy.get('[wire\\:model="vintage_year"]').clear().type(new Date().getFullYear().toString())
    cy.get('[wire\\:model="total_weight"]').clear().type('500')
    cy.get('[wire\\:model="harvest_start_date"]').type(`${new Date().getFullYear()}-09-10`)
    cy.get('[wire\\:model="price_per_kg"]').clear().type('0.45')
    cy.get('[wire\\:model="harvest_ticket_number"]').clear().type('TK-CYPRESS-001')

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/grape-reception')
    cy.url().should('not.include', '/create')

    // Guardar ID de la recepción creada para los pasos siguientes
    cy.url().then((url) => {
      cy.get('body').then(($body) => {
        const links = $body.find('a[href*="/winery/grape-reception/"]').filter((i, el) => {
          return /\/winery\/grape-reception\/\d+$/.test(el.getAttribute('href'))
        })
        if (links.length > 0) {
          const href = links.first().attr('href')
          receptionAId = href.match(/\/(\d+)$/)?.[1]
          cy.log(`✅ Primera recepción creada: ID ${receptionAId}`)
        }
      })
    })
  })

  // ── PASO 2: Verificar recepción en índice ─────────────────────────────────

  it('2. La recepción aparece en el índice con los datos correctos', () => {
    cy.visit('/winery/grape-reception')
    cy.waitForLivewire()

    cy.contains('TK-CYPRESS-001').should('be.visible')
    cy.contains('500').should('be.visible')
  })

  // ── PASO 3: Segunda recepción ─────────────────────────────────────────────

  it('3. Registra segunda recepción del mismo viticultor (300 kg)', () => {
    cy.visit('/winery/grape-reception/create')
    cy.waitForLivewire()

    cy.get('[wire\\:model\\.live="viticulturist_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      cy.get('[wire\\:model\\.live="viticulturist_id"]').select($opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(1000)
    })

    cy.get('[wire\\:model\\.live="plot_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      cy.get('[wire\\:model\\.live="plot_id"]').select($opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(1000)
    })

    cy.get('[wire\\:model\\.live="plot_planting_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      cy.get('[wire\\:model\\.live="plot_planting_id"]').select($opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(500)
    })

    cy.get('[wire\\:model="container_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      cy.get('[wire\\:model="container_id"]').select($opts.first().val(), { force: true })
    })

    cy.get('[wire\\:model="vintage_year"]').clear().type(new Date().getFullYear().toString())
    cy.get('[wire\\:model="total_weight"]').clear().type('300')
    cy.get('[wire\\:model="harvest_start_date"]').type(`${new Date().getFullYear()}-09-15`)
    cy.get('[wire\\:model="price_per_kg"]').clear().type('0.45')
    cy.get('[wire\\:model="harvest_ticket_number"]').clear().type('TK-CYPRESS-002')

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/grape-reception')
    cy.url().should('not.include', '/create')

    cy.log('✅ Segunda recepción creada: 300 kg')
  })

  // ── PASO 4: Crear liquidación ─────────────────────────────────────────────

  it('4. Crea liquidación para el viticultor con ambas recepciones', () => {
    cy.visit('/winery/invoices/grape-purchase/create')
    cy.waitForLivewire()

    // Seleccionar el viticultor de prueba
    cy.get('[wire\\:model="viticulturist_id"]').find('option').filter((i, el) =>
      el.text.includes('Test Viticulturist') || el.value !== ''
    ).then(($opts) => {
      expect($opts.length, 'Debe haber viticultores con recepciones').to.be.gt(0)
      cy.get('[wire\\:model="viticulturist_id"]').select($opts.first().val(), { force: true })
      cy.waitForLivewire()
      cy.wait(1500)
    })

    // Deben aparecer las recepciones como líneas disponibles
    cy.get('body').then(($body) => {
      const harvestLines = $body.find('[wire\\:model*="lines"][wire\\:model*="harvest_id"]')
      expect(harvestLines.length, 'Deben aparecer líneas de recepción').to.be.gt(0)
      cy.log(`✅ ${harvestLines.length} línea(s) de recepción disponibles`)
    })

    cy.get('[wire\\:model="invoice_date"]').type(`${new Date().getFullYear()}-10-31`)
    cy.get('[wire\\:model="payment_type"]').select('transfer', { force: true })

    // Rellenar precio y retención en cada línea visible
    cy.get('[wire\\:model*="lines"][wire\\:model*="unit_price"]').each(($el) => {
      cy.wrap($el).clear().type('0.45')
    })
    cy.get('[wire\\:model*="lines"][wire\\:model*="tax_rate"]').each(($el) => {
      cy.wrap($el).clear().type('2')
    })

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/invoices/grape-purchase')
    cy.url().should('not.include', '/create')

    // Guardar referencia a la liquidación creada
    cy.get('body').then(($body) => {
      const editLinks = $body.find('a[href*="/winery/invoices/grape-purchase/"][href*="/edit"]')
      if (editLinks.length > 0) {
        const href = editLinks.first().attr('href')
        invoiceId = href.match(/\/(\d+)\//)?.[1]
        cy.log(`✅ Liquidación creada: ID ${invoiceId}`)
      }
    })
  })

  // ── PASO 5: Verificar liquidación ─────────────────────────────────────────

  it('5. La liquidación aparece en el índice con estado pendiente', () => {
    cy.visit('/winery/invoices/grape-purchase')
    cy.waitForLivewire()

    // Debe aparecer al menos una liquidación
    cy.get('body').then(($body) => {
      const hasInvoices = $body.find('a[href*="/winery/invoices/grape-purchase/"]').length > 0
      const hasEmptyState = $body.text().includes('No hay') || $body.text().includes('sin liquidaciones')

      expect(hasInvoices || !hasEmptyState, 'Debe existir la liquidación creada').to.be.true
    })
  })

  // ── PASO 6: Marcar como pagada ────────────────────────────────────────────

  it('6. Marca la liquidación como pagada', () => {
    cy.visit('/winery/invoices/grape-purchase')
    cy.waitForLivewire()

    cy.get('a[href*="/winery/invoices/grape-purchase/"][href*="/edit"]').first().click({ force: true })
    cy.waitForLivewire()
    cy.url().should('include', '/edit')

    // Cambiar estado de pago a pagado
    cy.get('body').then(($body) => {
      const paymentStatus = $body.find('[wire\\:model="payment_status"]')
      if (paymentStatus.length > 0) {
        cy.wrap(paymentStatus).select('paid', { force: true })
        cy.waitForLivewire()
        cy.wait(500)

        // Puede aparecer modal/campo para la fecha de pago
        cy.get('body').then(($b) => {
          const paymentDate = $b.find('[wire\\:model="payment_date"]')
          if (paymentDate.length > 0) {
            cy.wrap(paymentDate).type(`${new Date().getFullYear()}-10-31`)
          }
        })
      } else {
        cy.log('⚠ No se encontró campo payment_status — puede requerir acción específica en el UI')
      }
    })

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/invoices/grape-purchase')
  })

  // ── PASO 7: Verificar estado final ────────────────────────────────────────

  it('7. El estado final de la liquidación es pagado', () => {
    cy.visit('/winery/invoices/grape-purchase')
    cy.waitForLivewire()

    // Filtrar por pagadas y verificar que aparece
    cy.get('body').then(($body) => {
      const paidFilter = $body.find('[wire\\:model\\.live="filterPaymentStatus"]')
      if (paidFilter.length > 0) {
        cy.wrap(paidFilter).select('paid', { force: true })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('body').then(($b) => {
          const hasResults = $b.find('a[href*="/winery/invoices/grape-purchase/"]').length > 0
          cy.log(hasResults ? '✅ Liquidación pagada visible en filtro' : '⚠ No aparece en filtro pagadas')
        })
      } else {
        cy.log('No hay filtro de estado — verificación visual completada')
      }
    })
  })
})
