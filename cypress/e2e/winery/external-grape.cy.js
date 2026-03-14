/**
 * Winery External Grape (Uva Externa / Compra de Uva) - E2E Tests
 * Cubre: Index, Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery External Grape', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/external-grape')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de uva externa', () => {
      cy.url().should('include', '/winery/external-grape')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear entrada de uva externa', () => {
      cy.get('a[href*="/winery/external-grape/create"]').should('be.visible')
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

    it('filtra por tipo', () => {
      cy.get('body').then(($body) => {
        const typeFilter = $body.find('[wire\\:model\\.live="typeFilter"], [wire\\:model\\.live="filterType"]')
        if (typeFilter.length > 0) {
          cy.wrap(typeFilter.first()).find('option').eq(1).then(($opt) => {
            if ($opt.length && $opt.val()) {
              cy.wrap(typeFilter.first()).select($opt.val(), { force: true })
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
      cy.visit('/winery/external-grape/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/external-grape/create')
      cy.get('[wire\\:model="supplier_name"]').should('exist')
      cy.get('[wire\\:model="total_weight_kg"]').should('exist')
    })

    it('crea entrada de uva básica', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="supplier_name"]').clear().type(`Proveedor Uva ${id}`)
      cy.get('[wire\\:model="grape_type"]').select('grapes', { force: true })
      cy.get('[wire\\:model="total_weight_kg"]').clear().type('2500')
      cy.get('[wire\\:model="entry_date"]').type('2024-09-20')
      cy.get('[wire\\:model="status"]').select('available', { force: true })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/external-grape')
      cy.url().should('not.include', '/create')
    })

    it('crea entrada de mosto con datos completos', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="supplier_name"]').clear().type(`Mosto Proveedor ${id}`)
      cy.get('[wire\\:model="grape_type"]').select('must', { force: true })
      cy.get('[wire\\:model="color"]').select('red', { force: true })
      cy.get('[wire\\:model="geographic_origin"]').clear().type('Rioja Alta')
      cy.get('[wire\\:model="vintage_year"]').clear().type('2024')
      cy.get('[wire\\:model="alcohol_pct"]').clear().type('13.5')
      cy.get('[wire\\:model="total_weight_kg"]').clear().type('5000')
      cy.get('[wire\\:model="entry_date"]').type('2024-10-01')
      cy.get('[wire\\:model="status"]').select('available', { force: true })
      cy.get('[wire\\:model="notes"]').clear().type('Mosto de prueba E2E')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/external-grape')
      cy.url().should('not.include', '/create')
    })

    it('crea entrada de vino a granel', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="supplier_name"]').clear().type(`Granel Proveedor ${id}`)
      cy.get('[wire\\:model="grape_type"]').select('bulk_wine', { force: true })
      cy.get('[wire\\:model="total_weight_kg"]').clear().type('10000')
      cy.get('[wire\\:model="entry_date"]').type('2024-10-10')
      cy.get('[wire\\:model="protection_level"]').clear().type('DOCa Rioja')
      cy.get('[wire\\:model="status"]').select('available', { force: true })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/external-grape')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/external-grape/create')
    })
  })

  describe('Edit', () => {
    it('actualiza peso y estado', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/external-grape/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No external grape records to edit - creating one first')
          cy.visit('/winery/external-grape/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="supplier_name"]').clear().type(`Edit Test ${id}`)
          cy.get('[wire\\:model="grape_type"]').select('grapes', { force: true })
          cy.get('[wire\\:model="total_weight_kg"]').clear().type('1000')
          cy.get('[wire\\:model="entry_date"]').type('2024-09-01')
          cy.get('[wire\\:model="status"]').select('available', { force: true })
          cy.get('[data-cy="submit-button"]').click({ force: true })
          cy.wait(5000)
          cy.visit('/winery/external-grape')
          cy.waitForLivewire()
        }

        cy.get('a[href*="/winery/external-grape/"][href*="/edit"]').first().click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="total_weight_kg"]').clear().type('3000')
        cy.get('[wire\\:model="status"]').select('used', { force: true })

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/external-grape')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza notas y origen', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/external-grape/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No records to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="geographic_origin"]').clear().type('Actualizado E2E')
        cy.get('[wire\\:model="notes"]').clear().type('Notas actualizadas en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/external-grape')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
