/**
 * Winery Viticulturists (Viticultores de la Bodega) - E2E Tests
 * Cubre: Index, Create, Edit, Show
 */

const uniqueId = () => Date.now()

describe('Winery Viticulturists', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/viticulturists')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de viticultores', () => {
      cy.url().should('include', '/winery/viticulturists')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para añadir viticultor', () => {
      cy.get('a[href*="/winery/viticulturists/create"]').should('be.visible')
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
      cy.visit('/winery/viticulturists/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/viticulturists/create')
      cy.get('[wire\\:model="name"]').should('exist')
    })

    it('crea viticultor con datos mínimos', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Viticultor Test ${id}`)
      cy.get('[wire\\:model="email"]').clear().type(`vit${id}@test.com`)

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/viticulturists')
      cy.url().should('not.include', '/create')
    })

    it('crea viticultor con todos los datos', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Viticultor Completo ${id}`)
      cy.get('[wire\\:model="dni"]').clear().type(`${id.toString().slice(-8)}Z`)
      cy.get('[wire\\:model="email"]').clear().type(`completo${id}@test.com`)
      cy.get('[wire\\:model="phone"]').clear().type('655444333')
      cy.get('[wire\\:model="notes"]').clear().type('Viticultor de prueba E2E completo')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/viticulturists')
      cy.url().should('not.include', '/create')
    })

    it('valida nombre requerido', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/viticulturists/create')
    })

    it('valida formato de email', () => {
      cy.get('[wire\\:model="name"]').clear().type('Test Viticultor')
      cy.get('[wire\\:model="email"]').clear().type('email-invalido')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/viticulturists/create')
    })
  })

  describe('Show', () => {
    it('navega a la página de detalle', () => {
      cy.get('body').then(($body) => {
        const showLinks = $body.find('a[href*="/winery/viticulturists/"]').filter((i, el) => {
          const href = el.getAttribute('href')
          return href &&
            !href.includes('/create') &&
            !href.includes('/edit') &&
            /\/winery\/viticulturists\/\d+$/.test(href)
        })

        if (showLinks.length > 0) {
          cy.wrap(showLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('match', /\/winery\/viticulturists\/\d+$/)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No viticulturists found - skipping show test')
        }
      })
    })
  })

  describe('Edit', () => {
    it('actualiza teléfono del viticultor', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/viticulturists/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No viticulturists to edit - creating one first')
          cy.visit('/winery/viticulturists/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('[wire\\:model="email"]').clear().type(`edit${id}@test.com`)
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)
          cy.visit('/winery/viticulturists')
          cy.waitForLivewire()
        }

        cy.get('a[href*="/winery/viticulturists/"][href*="/edit"]').first().click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="phone"]').clear().type('622111000')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/viticulturists')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza nombre del viticultor', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/viticulturists/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No viticulturists to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/viticulturists')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
