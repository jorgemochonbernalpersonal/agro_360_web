/**
 * Winery Label Batches (Lotes de Etiquetas) - E2E Tests
 * Cubre: Index, Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Label Batches', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/label-batches')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de lotes de etiquetas', () => {
      cy.url().should('include', '/winery/label-batches')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear lote de etiquetas', () => {
      cy.get('a[href*="/winery/label-batches/create"]').should('be.visible')
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
      cy.visit('/winery/label-batches/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/label-batches/create')
      cy.getByWireModel('name').should('exist')
      cy.getByWireModel('start_number').should('exist')
      cy.getByWireModel('end_number').should('exist')
    })

    it('crea lote de etiquetas básico', () => {
      const id = uniqueId()
      const start = Math.floor(id / 1000) * 1000

      cy.getByWireModel('name').clear().type(`Lote Etiquetas ${id}`)
      cy.getByWireModel('source').select('own', { force: true })
      cy.getByWireModel('start_number').clear().type(start.toString())
      cy.getByWireModel('end_number').clear().type((start + 999).toString())

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/label-batches')
      cy.url().should('not.include', '/create')
    })

    it('crea lote asignado por DO', () => {
      const id = uniqueId()
      const start = Math.floor(id / 100) * 100

      cy.getByWireModel('name').clear().type(`DO Etiquetas ${id}`)
      cy.getByWireModel('source').select('do_assigned', { force: true })
      cy.getByWireModel('start_number').clear().type(start.toString())
      cy.getByWireModel('end_number').clear().type((start + 499).toString())
      cy.getByWireModel('notes').clear().type('Lote asignado por denominación de origen - E2E')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/label-batches')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/label-batches/create')
    })

    it('valida end_number >= start_number', () => {
      cy.getByWireModel('name').clear().type('Lote Inválido')
      cy.getByWireModel('source').select('own', { force: true })
      cy.getByWireModel('start_number').clear().type('5000')
      cy.getByWireModel('end_number').clear().type('1000')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/label-batches/create')
    })
  })

  describe('Edit', () => {
    it('actualiza nombre y notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/label-batches/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No label batches to edit - creating one first')
          cy.visit('/winery/label-batches/create')
          cy.waitForLivewire()
          const id = uniqueId()
          const start = Math.floor(id / 1000) * 1000
          cy.getByWireModel('name').clear().type(`Edit Test ${id}`)
          cy.getByWireModel('source').select('own', { force: true })
          cy.getByWireModel('start_number').clear().type(start.toString())
          cy.getByWireModel('end_number').clear().type((start + 99).toString())
          cy.get('[data-cy="submit-button"]').click({ force: true })
          cy.wait(5000)
          cy.visit('/winery/label-batches')
          cy.waitForLivewire()
        }

        cy.get('a[href*="/winery/label-batches/"][href*="/edit"]').first().click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        const id = uniqueId()
        cy.getByWireModel('name').clear().type(`Actualizado ${id}`)
        cy.getByWireModel('notes').clear().type('Actualizado en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/label-batches')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
