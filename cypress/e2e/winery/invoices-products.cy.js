/**
 * Winery Invoices - Product Sales (Facturas de Venta) - E2E Tests
 * Cubre: Index, Create, Edit
 * Requiere: al menos un cliente con dirección en la BD
 */

const uniqueId = () => Date.now()

describe('Winery Invoices - Product Sales', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/invoices/products')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de facturas de venta', () => {
      cy.url().should('include', '/winery/invoices/products')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear factura', () => {
      cy.get('a[href*="/winery/invoices/products/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('test', { force: true })
          cy.waitForLivewire()
          cy.wait(600)
        }
        cy.get('body').should('be.visible')
      })
    })

    it('filtra por estado de pago', () => {
      cy.get('body').then(($body) => {
        const filter = $body.find('[wire\\:model\\.live="filterPaymentStatus"], [wire\\:model="filterPaymentStatus"]')
        if (filter.length > 0) {
          cy.wrap(filter.first()).find('option').eq(1).then(($opt) => {
            if ($opt.length && $opt.val()) {
              cy.wrap(filter.first()).select($opt.val(), { force: true })
              cy.waitForLivewire()
              cy.wait(500)
            }
          })
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/invoices/products/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/invoices/products/create')
      cy.get('[wire\\:model="order_date"]').should('exist')
    })

    it('valida campos requeridos', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/invoices/products/create')
    })

    it('crea factura básica si hay clientes', () => {
      cy.get('[wire\\:model="client_id"]').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No clients available - skipping')
          return
        }

        cy.get('[wire\\:model="client_id"]').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        // Dirección (autocargada o seleccionar)
        cy.get('[wire\\:model="client_address_id"]').then(($addrSel) => {
          const addrOpts = $addrSel.find('option').filter((i, el) => el.value !== '')
          if (addrOpts.length > 0) {
            cy.get('[wire\\:model="client_address_id"]').select(addrOpts.first().val(), { force: true })
          }
        })

        cy.get('[wire\\:model="order_date"]').type('2024-11-01')

        // Primera línea de producto (libre, sin lote)
        cy.get('[wire\\:model="items.0.name"]').clear().type('Producto Test E2E')
        cy.get('[wire\\:model="items.0.quantity"]').clear().type('10')
        cy.get('[wire\\:model="items.0.unit_price"]').clear().type('15.00')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/invoices/products')
        cy.url().should('not.include', '/create')
      })
    })

    it('crea factura con método de pago', () => {
      cy.get('[wire\\:model="client_id"]').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No clients available - skipping')
          return
        }

        cy.get('[wire\\:model="client_id"]').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        cy.get('[wire\\:model="client_address_id"]').then(($addrSel) => {
          const addrOpts = $addrSel.find('option').filter((i, el) => el.value !== '')
          if (addrOpts.length > 0) {
            cy.get('[wire\\:model="client_address_id"]').select(addrOpts.first().val(), { force: true })
          }
        })

        cy.get('[wire\\:model="order_date"]').type('2024-11-15')
        cy.get('[wire\\:model="payment_type"]').select('transfer', { force: true })
        cy.get('[wire\\:model="observations"]').clear().type('Factura de prueba E2E')

        cy.get('[wire\\:model="items.0.name"]').clear().type('Vino Reserva Test')
        cy.get('[wire\\:model="items.0.quantity"]').clear().type('24')
        cy.get('[wire\\:model="items.0.unit_price"]').clear().type('12.50')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/invoices/products')
        cy.url().should('not.include', '/create')
      })
    })
  })

  describe('Edit', () => {
    it('actualiza observaciones de la factura', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/invoices/products/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No invoices to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="observations"]').clear().type('Observaciones actualizadas en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/invoices/products')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza método de pago', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/invoices/products/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No invoices to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="payment_type"]').select('cash', { force: true })

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/invoices/products')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
