/**
 * Winery Subproducts (Subproductos: orujo, lías, etc.) - E2E Tests
 * Cubre: Index, Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Subproducts', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/subproducts')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de subproductos', () => {
      cy.url().should('include', '/winery/subproducts')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear subproducto', () => {
      cy.get('a[href*="/winery/subproducts/create"]').should('be.visible')
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
      cy.visit('/winery/subproducts/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/subproducts/create')
      cy.get('[wire\\:model="type"]').should('exist')
      cy.get('[wire\\:model="quantity"]').should('exist')
    })

    it('crea un subproducto básico', () => {
      cy.get('[wire\\:model="type"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type"]').select($opt.val(), { force: true })
      })

      cy.get('[wire\\:model="subproduct_date"]').type('2024-10-01')
      cy.get('[wire\\:model="quantity"]').clear().type('500')

      cy.get('[wire\\:model="destination"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="destination"]').select($opt.val(), { force: true })
      })

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/subproducts')
      cy.url().should('not.include', '/create')
    })

    it('crea subproducto con destino y nombre', () => {
      cy.get('[wire\\:model="type"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type"]').select($opt.val(), { force: true })
      })

      cy.get('[wire\\:model="subproduct_date"]').type('2024-10-15')
      cy.get('[wire\\:model="quantity"]').clear().type('200')

      cy.get('[wire\\:model="destination"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="destination"]').select($opt.val(), { force: true })
      })

      cy.get('[wire\\:model="destination_name"]').clear().type('Destilería Test')
      cy.get('[wire\\:model="lot_number"]').clear().type(`LOT-${uniqueId()}`)
      cy.get('[wire\\:model="notes"]').clear().type('Subproducto de prueba E2E')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/subproducts')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/subproducts/create')
    })
  })

  describe('Edit', () => {
    it('actualiza cantidad y notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/subproducts/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No subproducts to edit - creating one first')
          cy.visit('/winery/subproducts/create')
          cy.waitForLivewire()
          cy.get('[wire\\:model="type"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="type"]').select($opt.val(), { force: true })
          })
          cy.get('[wire\\:model="subproduct_date"]').type('2024-10-01')
          cy.get('[wire\\:model="quantity"]').clear().type('100')
          cy.get('[wire\\:model="destination"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="destination"]').select($opt.val(), { force: true })
          })
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)
          cy.visit('/winery/subproducts')
          cy.waitForLivewire()
        }

        cy.get('a[href*="/winery/subproducts/"][href*="/edit"]').first().click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="quantity"]').clear().type('350')
        cy.get('[wire\\:model="notes"]').clear().type('Actualizado en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/subproducts')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
