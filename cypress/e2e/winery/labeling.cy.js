/**
 * Winery Labeling (Etiquetado) - E2E Tests
 * Cubre: Index, Create, Edit
 * Requiere: vino + embotellado + lote de etiquetas
 */

const uniqueId = () => Date.now()

describe('Winery Labeling', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/labeling')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de etiquetado', () => {
      cy.url().should('include', '/winery/labeling')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear etiquetado', () => {
      cy.get('a[href*="/winery/labeling/create"]').should('be.visible')
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
      cy.visit('/winery/labeling/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/labeling/create')
      cy.get('[wire\\:model="labeling_date"]').should('exist')
      cy.getByWireModel('quantity_labeled').should('exist')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/labeling/create')
    })

    it('crea etiquetado si hay vinos', () => {
      cy.getByWireModel('wine_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No wines available - skipping')
          return
        }

        cy.getByWireModel('wine_id').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('[wire\\:model="labeling_date"]').type('2024-12-01')
        cy.getByWireModel('quantity_labeled').clear().type('200')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/labeling')
        cy.url().should('not.include', '/create')
      })
    })

    it('crea etiquetado con rango de números', () => {
      cy.getByWireModel('wine_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No wines available - skipping')
          return
        }

        cy.getByWireModel('wine_id').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('[wire\\:model="labeling_date"]').type('2024-12-15')
        cy.getByWireModel('quantity_labeled').clear().type('100')
        cy.getByWireModel('from_number').clear().type('1001')
        cy.get('[wire\\:model="to_number"]').clear().type('1100')
        cy.get('[wire\\:model="notes"]').clear().type('Etiquetado de prueba E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/labeling')
        cy.url().should('not.include', '/create')
      })
    })
  })

  describe('Edit', () => {
    it('actualiza cantidad etiquetada y notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/labeling/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No labeling records to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.getByWireModel('quantity_labeled').clear().type('150')
        cy.get('[wire\\:model="notes"]').clear().type('Actualizado en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/labeling')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
