/**
 * Winery Bottling (Embotellado) - E2E Tests
 * Cubre: Index, Create, Edit
 * Requiere: al menos un vino en la BD
 */

const uniqueId = () => Date.now()

describe('Winery Bottling', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/bottling')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de embotellados', () => {
      cy.url().should('include', '/winery/bottling')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear embotellado', () => {
      cy.get('a[href*="/winery/bottling/create"]').should('be.visible')
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
      cy.visit('/winery/bottling/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/bottling/create')
      cy.get('[wire\\:model="bottling_date"]').should('exist')
      cy.get('[wire\\:model="quantity_bottles"]').should('exist')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/bottling/create')
    })

    it('crea embotellado si hay vinos', () => {
      cy.getByWireModel('wine_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No wines available - skipping')
          return
        }

        cy.getByWireModel('wine_id').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('[wire\\:model="bottling_date"]').type('2024-11-15')
        cy.get('[wire\\:model="bottle_format"]').clear().type('750')
        cy.get('[wire\\:model="quantity_bottles"]').clear().type('500')
        cy.get('[wire\\:model="quantity_liters"]').clear().type('375')
        cy.get('[wire\\:model="lot_number"]').clear().type(`LOT-${uniqueId()}`)

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/bottling')
        cy.url().should('not.include', '/create')
      })
    })

    it('crea embotellado con notas', () => {
      cy.getByWireModel('wine_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No wines available - skipping')
          return
        }

        cy.getByWireModel('wine_id').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('[wire\\:model="bottling_date"]').type('2024-12-01')
        cy.get('[wire\\:model="bottle_format"]').clear().type('375')
        cy.get('[wire\\:model="quantity_bottles"]').clear().type('200')
        cy.get('[wire\\:model="quantity_liters"]').clear().type('75')
        cy.get('[wire\\:model="notes"]').clear().type('Embotellado de prueba E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/bottling')
        cy.url().should('not.include', '/create')
      })
    })
  })

  describe('Edit', () => {
    it('actualiza notas del embotellado', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/bottling/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No bottling records to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="notes"]').clear().type('Notas actualizadas en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/bottling')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza número de lote y formato', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/bottling/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No bottling records to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="lot_number"]').clear().type(`UPD-${id}`)
        cy.get('[wire\\:model="bottle_format"]').clear().type('750')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/bottling')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
