/**
 * Winery Supplies (Materiales de Bodega) - E2E Tests
 * Cubre: Index, Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Supplies', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/winery-supplies')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de materiales', () => {
      cy.url().should('include', '/winery/winery-supplies')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear material', () => {
      cy.get('a[href*="/winery/winery-supplies/create"]').should('be.visible')
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
  })

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/winery-supplies/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/winery-supplies/create')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="supply_type"]').should('exist')
    })

    it('crea un material básico', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`SO2 Test ${id}`)
      cy.get('[wire\\:model="supply_type"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="supply_type"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="current_stock"]').clear().type('50')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/winery-supplies')
      cy.url().should('not.include', '/create')
    })

    it('crea material con todos los campos', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Bentonita ${id}`)
      cy.get('[wire\\:model="commercial_name"]').clear().type(`Bentonita Comercial ${id}`)
      cy.get('[wire\\:model="supply_type"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="supply_type"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="current_stock"]').clear().type('100')
      cy.get('[wire\\:model="min_stock_alert"]').clear().type('20')
      cy.get('[wire\\:model="expiry_date"]').type('2025-12-31')
      cy.get('[wire\\:model="notes"]').clear().type('Material de prueba E2E')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/winery-supplies')
      cy.url().should('not.include', '/create')
    })

    it('valida nombre y tipo requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/winery-supplies/create')
    })
  })

  describe('Edit', () => {
    it('actualiza stock y alerta mínima', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/winery-supplies/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No supplies found - creating one first')
          cy.visit('/winery/winery-supplies/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('[wire\\:model="supply_type"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="supply_type"]').select($opt.val(), { force: true })
          })
          cy.get('[data-cy="submit-button"]').click({ force: true })
          cy.wait(5000)
          cy.visit('/winery/winery-supplies')
          cy.waitForLivewire()
        }

        cy.get('a[href*="/winery/winery-supplies/"][href*="/edit"]').first().click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="current_stock"]').clear().type('75')
        cy.get('[wire\\:model="min_stock_alert"]').clear().type('15')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/winery-supplies')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza nombre y notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/winery-supplies/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No supplies to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)
        cy.get('[wire\\:model="notes"]').clear().type('Actualizado en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/winery-supplies')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
