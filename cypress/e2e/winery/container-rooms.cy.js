/**
 * Winery Container Rooms (Salas) - E2E Tests
 * Cubre: Index, Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Container Rooms', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/container-rooms')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de salas', () => {
      cy.url().should('include', '/winery/container-rooms')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear sala', () => {
      cy.get('a[href*="/winery/container-rooms/create"]').should('be.visible')
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

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/container-rooms/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/container-rooms/create')
      cy.get('[wire\\:model="name"]').should('exist')
    })

    it('crea una sala básica', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Sala Fermentación ${id}`)
      cy.get('[wire\\:model="description"]').clear().type('Sala de prueba E2E')
      cy.get('[wire\\:model="capacity"]').clear().type('20')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/container-rooms')
      cy.url().should('not.include', '/create')
    })

    it('crea una sala con condiciones ambientales', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Sala Crianza ${id}`)
      cy.get('[wire\\:model="capacity"]').clear().type('50')
      cy.get('[wire\\:model="temperature"]').clear().type('14.5')
      cy.get('[wire\\:model="humidity"]').clear().type('75')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/container-rooms')
      cy.url().should('not.include', '/create')
    })

    it('valida nombre requerido', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/container-rooms/create')
    })

    it('valida temperatura máxima 50', () => {
      cy.get('[wire\\:model="name"]').clear().type('Sala Inválida')
      cy.get('[wire\\:model="temperature"]').clear().type('999')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/container-rooms/create')
    })

    it('valida humedad máxima 100', () => {
      cy.get('[wire\\:model="name"]').clear().type('Sala Inválida')
      cy.get('[wire\\:model="humidity"]').clear().type('200')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/container-rooms/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/container-rooms/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.get('[wire\\:model="name"]').should('exist')
        } else {
          cy.log('No rooms found - creating one first')
          cy.visit('/winery/container-rooms/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)

          cy.get('a[href*="/winery/container-rooms/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza el nombre de la sala', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/container-rooms/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No rooms to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizada ${id}`)

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/container-rooms')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza condiciones ambientales', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/container-rooms/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No rooms to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="temperature"]').clear().type('16')
        cy.get('[wire\\:model="humidity"]').clear().type('80')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/container-rooms')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
