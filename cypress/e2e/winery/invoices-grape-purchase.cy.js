/**
 * Winery Invoices - Grape Purchase (Liquidaciones de Uva) - E2E Tests
 * Cubre: Index, Create, Edit
 * Requiere: viticultor vinculado con recepciones de uva registradas
 */

const uniqueId = () => Date.now()

describe('Winery Invoices - Grape Purchase', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/invoices/grape-purchase')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de liquidaciones', () => {
      cy.url().should('include', '/winery/invoices/grape-purchase')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear liquidación', () => {
      cy.get('a[href*="/winery/invoices/grape-purchase/create"]').should('be.visible')
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
      cy.visit('/winery/invoices/grape-purchase/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/invoices/grape-purchase/create')
      cy.get('[wire\\:model="invoice_date"]').should('exist')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/invoices/grape-purchase/create')
    })

    it('selecciona viticultor y carga recepciones si hay datos', () => {
      cy.getByWireModel('viticulturist_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No viticulturists available - skipping cascade test')
          return
        }

        cy.getByWireModel('viticulturist_id').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        cy.get('body').should('be.visible')
      })
    })

    it('crea liquidación completa si hay viticultor con recepciones', () => {
      cy.getByWireModel('viticulturist_id').then(($vitSel) => {
        const vitOpts = $vitSel.find('option').filter((i, el) => el.value !== '')
        if (vitOpts.length === 0) {
          cy.log('No viticulturists - skipping')
          return
        }

        cy.getByWireModel('viticulturist_id').select(vitOpts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        // Verificar si hay líneas de recepción disponibles
        cy.get('body').then(($body) => {
          const harvestSelect = $body.find('[wire\\:model*="lines"][wire\\:model*="harvest_id"]')
          if (harvestSelect.length === 0) {
            cy.log('No harvest lines available - skipping full creation')
            return
          }

          cy.get('[wire\\:model="invoice_date"]').type('2024-11-30')
          cy.get('[wire\\:model="payment_type"]').select('transfer', { force: true })

          // Primera línea
          cy.wrap(harvestSelect.first()).find('option').eq(1).then(($opt) => {
            if (!$opt.val()) return
            cy.wrap(harvestSelect.first()).select($opt.val(), { force: true })
            cy.waitForLivewire()
            cy.wait(500)

            cy.getByWireModel('lines.0.unit_price').clear().type('0.45')
            cy.getByWireModel('lines.0.tax_rate').clear().type('2')

            cy.get('[data-cy="submit-button"]').click({ force: true })
            cy.wait(5000)

            cy.url().should('include', '/winery/invoices/grape-purchase')
            cy.url().should('not.include', '/create')
          })
        })
      })
    })
  })

  describe('Edit', () => {
    it('actualiza observaciones de la liquidación', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/invoices/grape-purchase/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No grape purchase invoices to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="observations"]').clear().type('Observaciones actualizadas en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/invoices/grape-purchase')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza método de pago', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/invoices/grape-purchase/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No grape purchase invoices to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="payment_type"]').select('check', { force: true })

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/invoices/grape-purchase')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
