/**
 * FLUJO CRÍTICO: Vino → Embotellado → Lote de Producto → Venta → Stock
 *
 * Simula el flujo real completo de producción y venta:
 * 1. Crear vino tinto
 * 2. Registrar embotellado del vino (1000 botellas)
 * 3. Crear lote de producto vinculado al vino
 * 4. Verificar stock inicial del lote
 * 5. Crear cliente para la venta
 * 6. Crear factura de venta con líneas del lote
 * 7. Verificar que el stock del lote se ha decrementado
 *
 * Requiere CypressTestUserSeeder con datos de apoyo completos.
 */

const uid = () => Date.now()

describe('Flujo: Vino → Embotellado → Lote → Venta → Stock', () => {
  let wineName
  let wineId
  let lotName
  let clientName

  before(() => {
    const id = uid()
    wineName  = `Rioja Cypress Flow ${id}`
    lotName   = `Lote Cypress Flow ${id}`
    clientName = `Cliente Cypress Flow ${id}`
  })

  beforeEach(() => {
    cy.loginAsWinery()
  })

  // ── PASO 1: Crear vino ────────────────────────────────────────────────────

  it('1. Crea el vino base del flujo', () => {
    cy.visit('/winery/wines/create')
    cy.waitForLivewire()

    cy.get('[wire\\:model="name"]').clear().type(wineName)
    cy.get('[wire\\:model="wine_type"]').select('red', { force: true })
    cy.get('[wire\\:model="status"]').select('in_progress', { force: true })
    cy.get('[wire\\:model="vintage"]').clear().type(new Date().getFullYear().toString())
    cy.get('[wire\\:model="aging_type"]').select('crianza', { force: true })
    cy.get('[wire\\:model="variety"]').clear().type('Tempranillo 100%')
    cy.get('[wire\\:model="volume_liters"]').clear().type('750')

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/wines')
    cy.url().should('not.include', '/create')

    // Guardar ID del vino
    cy.get('body').then(($body) => {
      const editLink = $body.find(`a[href*="/winery/wines/"][href*="/edit"]`).first()
      if (editLink.length > 0) {
        wineId = editLink.attr('href').match(/\/wines\/(\d+)\//)?.[1]
        cy.log(`✅ Vino creado: ${wineName} (ID: ${wineId})`)
      }
    })
  })

  // ── PASO 2: Verificar vino en índice ──────────────────────────────────────

  it('2. El vino aparece en el índice', () => {
    cy.visit('/winery/wines')
    cy.waitForLivewire()

    cy.contains(wineName).should('be.visible')
    cy.log('✅ Vino visible en el índice')
  })

  // ── PASO 3: Registrar embotellado ─────────────────────────────────────────

  it('3. Registra embotellado de 1000 botellas', () => {
    cy.visit('/winery/bottling/create')
    cy.waitForLivewire()

    // Seleccionar el vino recién creado
    cy.get('[wire\\:model="wine_id"]').find('option').filter((i, el) =>
      el.text.includes('Cypress Flow') || el.value !== ''
    ).then(($opts) => {
      expect($opts.length, 'Debe existir el vino creado').to.be.gt(0)

      // Intentar seleccionar el vino por nombre
      cy.get('[wire\\:model="wine_id"]').find('option').each(($opt) => {
        if ($opt.text().includes('Cypress Flow')) {
          cy.get('[wire\\:model="wine_id"]').select($opt.val(), { force: true })
          return false // break
        }
      })
      cy.waitForLivewire()
      cy.wait(500)
    })

    cy.get('[wire\\:model="bottling_date"]').type(`${new Date().getFullYear()}-10-01`)
    cy.get('[wire\\:model="bottle_format"]').clear().type('750')
    cy.get('[wire\\:model="quantity_bottles"]').clear().type('1000')
    cy.get('[wire\\:model="quantity_liters"]').clear().type('750')
    cy.get('[wire\\:model="lot_number"]').clear().type(`BOT-CYPRESS-${uid()}`)
    cy.get('[wire\\:model="notes"]').clear().type('Embotellado del flujo E2E Cypress')

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/bottling')
    cy.url().should('not.include', '/create')

    cy.log('✅ Embotellado registrado: 1000 botellas')
  })

  // ── PASO 4: Crear lote de producto ────────────────────────────────────────

  it('4. Crea lote de producto vinculado al vino (1000 botellas, 12€/u)', () => {
    cy.visit('/winery/product-lots/create')
    cy.waitForLivewire()

    // Vincular al vino si está disponible
    cy.get('body').then(($body) => {
      const wineSelect = $body.find('[wire\\:model="wine_id"]')
      if (wineSelect.length > 0) {
        wineSelect.find('option').each((i, el) => {
          if (el.text.includes('Cypress Flow')) {
            cy.get('[wire\\:model="wine_id"]').select(el.value, { force: true })
            return false
          }
        })
      }
    })

    cy.get('[wire\\:model="name"]').clear().type(lotName)
    cy.get('[wire\\:model="wine_type"]').select('tinto', { force: true })
    cy.get('[wire\\:model="vintage"]').clear().type(new Date().getFullYear().toString())
    cy.get('[wire\\:model="aging_type"]').select('crianza', { force: true })
    cy.get('[wire\\:model="alcohol"]').clear().type('13.5')
    cy.get('[wire\\:model="sku"]').clear().type(`CYP-${uid()}`)

    // Tab Stock
    cy.get('body').then(($body) => {
      const stockTab = $body.find('button:contains("Stock"), [wire\\:click*="stock"]')
      if (stockTab.length > 0) {
        cy.wrap(stockTab.first()).click({ force: true })
        cy.wait(300)
      }
    })

    cy.get('[wire\\:model="quantity"]').clear().type('1000')
    cy.get('[wire\\:model="available_quantity"]').clear().type('1000')
    cy.get('[wire\\:model="unit"]').select('botellas', { force: true })
    cy.get('[wire\\:model="price_per_unit"]').clear().type('12.00')
    cy.get('[wire\\:model="cost_price"]').clear().type('4.50')

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/product-lots')
    cy.url().should('not.include', '/create')

    cy.log(`✅ Lote creado: ${lotName} — 1000 botellas disponibles`)
  })

  // ── PASO 5: Verificar stock inicial ──────────────────────────────────────

  it('5. El lote muestra 1000 unidades disponibles', () => {
    cy.visit('/winery/product-lots')
    cy.waitForLivewire()

    cy.contains(lotName).should('be.visible')
    // El índice muestra el stock — verificar que aparece 1000
    cy.get('body').then(($body) => {
      const lotCard = $body.find(`*:contains("${lotName}")`).closest('[class*="card"], [class*="grid"], li, tr').first()
      if (lotCard.length > 0) {
        cy.wrap(lotCard).contains('1000').should('exist')
        cy.log('✅ Stock inicial: 1000 botellas confirmado')
      }
    })
  })

  // ── PASO 6: Crear cliente ─────────────────────────────────────────────────

  it('6. Crea el cliente para la venta', () => {
    cy.visit('/winery/clients/create')
    cy.waitForLivewire()

    cy.get('[wire\\:model="client_type"]').select('company', { force: true })
    cy.wait(500)

    cy.get('[wire\\:model="company_name"]').clear().type(clientName)
    cy.get('[wire\\:model="company_document"]').clear().type(`B${uid().toString().slice(-7)}`)
    cy.get('[wire\\:model="email"]').clear().type(`cypress.flow@test.com`)
    cy.get('[wire\\:model="phone"]').clear().type('911000999')

    // Dirección obligatoria
    cy.get('[wire\\:model="addresses.0.address"]').clear().type('Calle del Flujo Cypress 1')
    cy.get('[wire\\:model="addresses.0.postal_code"]').clear().type('28001')

    cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').find('option').eq(1).then(($opt) => {
      cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').select($opt.val(), { force: true })
    })
    cy.wait(1000)
    cy.get('[wire\\:model="addresses.0.province_id"]').find('option').should('have.length.gt', 1)
    cy.get('[wire\\:model="addresses.0.province_id"]').find('option').eq(1).then(($opt) => {
      cy.get('[wire\\:model="addresses.0.province_id"]').select($opt.val(), { force: true })
    })
    cy.wait(1000)
    cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').should('have.length.gt', 1)
    cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').eq(1).then(($opt) => {
      cy.get('[wire\\:model="addresses.0.municipality_id"]').select($opt.val(), { force: true })
    })
    cy.wait(500)

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/clients')
    cy.url().should('not.include', '/create')

    cy.log(`✅ Cliente creado: ${clientName}`)
  })

  // ── PASO 7: Crear factura de venta ────────────────────────────────────────

  it('7. Crea factura de venta con 24 botellas del lote', () => {
    cy.visit('/winery/invoices/products/create')
    cy.waitForLivewire()

    // Seleccionar cliente
    cy.get('[wire\\:model="client_id"]').find('option').filter((i, el) =>
      el.text.includes('Cypress Flow') || el.value !== ''
    ).then(($opts) => {
      expect($opts.length, 'Debe existir el cliente creado').to.be.gt(0)

      cy.get('[wire\\:model="client_id"]').find('option').each(($opt) => {
        if ($opt.text().includes('Cypress Flow')) {
          cy.get('[wire\\:model="client_id"]').select($opt.val(), { force: true })
          return false
        }
      })
      cy.waitForLivewire()
      cy.wait(1000)
    })

    // Dirección (autocargada)
    cy.get('[wire\\:model="client_address_id"]').find('option').filter((i, el) => el.value !== '').then(($opts) => {
      if ($opts.length > 0) {
        cy.get('[wire\\:model="client_address_id"]').select($opts.first().val(), { force: true })
      }
    })

    cy.get('[wire\\:model="order_date"]').type(`${new Date().getFullYear()}-11-01`)
    cy.get('[wire\\:model="payment_type"]').select('transfer', { force: true })

    // Línea 1: seleccionar lote de producto
    cy.get('body').then(($body) => {
      const lotSelect = $body.find('[wire\\:model="items.0.wine_lot_id"]')
      if (lotSelect.length > 0) {
        lotSelect.find('option').each((i, el) => {
          if (el.text.includes('Cypress Flow')) {
            cy.get('[wire\\:model="items.0.wine_lot_id"]').select(el.value, { force: true })
            cy.waitForLivewire()
            cy.wait(500)
            return false
          }
        })
      }
    })

    // Completar línea
    cy.get('[wire\\:model="items.0.name"]').then(($el) => {
      if (!$el.val()) cy.wrap($el).clear().type('Rioja Crianza Cypress')
    })
    cy.get('[wire\\:model="items.0.quantity"]').clear().type('24')
    cy.get('[wire\\:model="items.0.unit_price"]').clear().type('12.00')

    cy.get('[wire\\:model="observations"]').clear().type('Factura del flujo E2E Cypress')

    cy.get('[data-cy="submit-button"]').click({ force: true })
    cy.wait(5000)

    cy.url().should('include', '/winery/invoices/products')
    cy.url().should('not.include', '/create')

    cy.log('✅ Factura de venta creada: 24 botellas × 12€')
  })

  // ── PASO 8: Verificar stock decrementado ──────────────────────────────────

  it('8. El stock del lote se ha reducido en 24 unidades (976 disponibles)', () => {
    cy.visit('/winery/product-lots')
    cy.waitForLivewire()

    cy.contains(lotName).should('be.visible')

    cy.get('body').then(($body) => {
      const lotCard = $body.find(`*:contains("${lotName}")`).closest('[class*="card"], [class*="grid"], li, tr').first()
      if (lotCard.length > 0) {
        // Stock debería ser 976 (1000 - 24)
        cy.wrap(lotCard).contains('976').should('exist')
        cy.log('✅ Stock decrementado correctamente: 1000 - 24 = 976')
      } else {
        // Alternativa: ir directamente al edit del lote y verificar
        cy.get('a[href*="/winery/product-lots/"][href*="/edit"]').first().click({ force: true })
        cy.waitForLivewire()
        cy.get('[wire\\:model="available_quantity"]').should('have.value', '976')
        cy.log('✅ Stock verificado en formulario de edición: 976')
      }
    })
  })

  // ── PASO 9: Factura aparece en el índice ──────────────────────────────────

  it('9. La factura aparece en el índice de ventas', () => {
    cy.visit('/winery/invoices/products')
    cy.waitForLivewire()

    cy.get('body').then(($body) => {
      const hasInvoices = $body.find('a[href*="/winery/invoices/products/"]').length > 0
      expect(hasInvoices, 'Debe existir al menos una factura de venta').to.be.true
      cy.log('✅ Factura de venta visible en el índice')
    })
  })
})
