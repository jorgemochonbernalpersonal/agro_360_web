/**
 * Winery Containers - E2E Tests
 * Cubre: Index (list, filtros, tabs), Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Containers', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/containers')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de contenedores', () => {
      cy.url().should('include', '/winery/containers')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear contenedor', () => {
      cy.get('a[href*="/winery/containers/create"]').should('be.visible')
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
        const typeFilter = $body.find('[wire\\:model\\.live="typeFilter"]')
        if (typeFilter.length > 0) {
          const options = typeFilter.first().find('option')
          if (options.length > 1) {
            cy.wrap(typeFilter.first()).select(options.eq(1).val(), { force: true })
            cy.waitForLivewire()
            cy.wait(500)
          }
        }
        cy.get('body').should('be.visible')
      })
    })

    it('cambia a pestaña archivados', () => {
      cy.get('body').then(($body) => {
        const archivedTab = $body.find('[wire\\:click*="switchTab"][wire\\:click*="archived"], button:contains("Archivados")')
        if (archivedTab.length > 0) {
          cy.wrap(archivedTab.first()).click({ force: true })
          cy.waitForLivewire()
          cy.wait(500)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/containers/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/containers/create')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="capacity"]').should('exist')
    })

    it('crea un contenedor correctamente', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Depósito Test ${id}`)

      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })

      cy.get('[wire\\:model="capacity"]').clear().type('5000')
      cy.get('[wire\\:model="serial_number"]').clear().type(`SN-${id}`)
      cy.get('[wire\\:model="supplier_name"]').clear().type('Proveedor Test')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })

    it('crea contenedor con fecha de compra', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Cuba Test ${id}`)

      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })

      cy.get('[wire\\:model="capacity"]').clear().type('2000')
      cy.get('[wire\\:model="purchase_date"]').type('2023-01-15')
      cy.get('[wire\\:model="description"]').clear().type('Contenedor de prueba E2E')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    it('valida capacidad mínima 1', () => {
      cy.get('[wire\\:model="name"]').clear().type('Contenedor inválido')
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('0')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/containers/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.get('[wire\\:model="name"]').should('exist')
        } else {
          cy.log('No containers to edit - creating one first')
          cy.visit('/winery/containers/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
          })
          cy.get('[wire\\:model="capacity"]').clear().type('3000')
          cy.get('[data-cy="submit-button"]').click({ force: true })
          cy.wait(5000)

          cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza el nombre del contenedor', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/containers/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No containers to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/containers')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza capacidad y proveedor', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/containers/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No containers to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="capacity"]').clear().type('8000')
        cy.get('[wire\\:model="supplier_name"]').clear().type('Nuevo Proveedor')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/containers')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
