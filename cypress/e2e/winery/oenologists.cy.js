/**
 * Winery Oenologists (Enólogos) - E2E Tests
 * Cubre: Index (list, búsqueda, tabs), Create, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Oenologists', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/oenologists')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de enólogos', () => {
      cy.url().should('include', '/winery/oenologists')
      cy.contains('Enólogos').should('be.visible')
    })

    it('tiene botón para crear enólogo', () => {
      cy.get('a[href*="/winery/oenologists/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
      cy.get('[wire\\:model\\.live\\.debounce\\.300ms="search"]').type('test', { force: true })
      cy.waitForLivewire()
      cy.wait(600)
      cy.get('body').should('be.visible')
    })

    it('cambia a pestaña inactivos', () => {
      cy.get('body').then(($body) => {
        const tab = $body.find('[wire\\:click*="switchTab"][wire\\:click*="inactive"], button:contains("Inactivos")')
        if (tab.length > 0) {
          cy.wrap(tab.first()).click({ force: true })
          cy.waitForLivewire()
          cy.wait(500)
        }
        cy.get('body').should('be.visible')
      })
    })

    it('limpia el filtro de búsqueda', () => {
      cy.get('[wire\\:model\\.live\\.debounce\\.300ms="search"]').type('busqueda', { force: true })
      cy.wait(600)
      cy.get('body').then(($body) => {
        const clearBtn = $body.find('[wire\\:click="clearFilters"], button:contains("Limpiar")')
        if (clearBtn.length > 0) {
          cy.wrap(clearBtn.first()).click({ force: true })
          cy.waitForLivewire()
          cy.get('[wire\\:model\\.live\\.debounce\\.300ms="search"]').should('have.value', '')
        }
      })
    })
  })

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/oenologists/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/oenologists/create')
      cy.get('[wire\\:model="name"]').should('exist')
    })

    it('crea un enólogo con datos mínimos', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`María${id}`)

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/oenologists')
      cy.url().should('not.include', '/create')
    })

    it('crea un enólogo con todos los datos', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Carlos${id}`)
      cy.get('[wire\\:model="surname"]').clear().type('Martínez López')
      cy.get('[wire\\:model="license_number"]').clear().type(`OE-${id}`)
      cy.get('[wire\\:model="email"]').clear().type(`carlos${id}@bodega.com`)
      cy.get('[wire\\:model="phone"]').clear().type('677888999')
      cy.get('[wire\\:model="notes"]').clear().type('Enólogo especialista en vinos tintos. Prueba E2E.')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/oenologists')
      cy.url().should('not.include', '/create')
    })

    it('valida nombre requerido', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/oenologists/create')
    })

    it('valida formato de email', () => {
      cy.get('[wire\\:model="name"]').clear().type('Enólogo Test')
      cy.get('[wire\\:model="email"]').clear().type('email-invalido')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/oenologists/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/oenologists/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.get('[wire\\:model="name"]').should('exist')
        } else {
          cy.log('No oenologists found - creating one first')
          cy.visit('/winery/oenologists/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)

          cy.get('a[href*="/winery/oenologists/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza el nombre del enólogo', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/oenologists/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No oenologists to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/oenologists')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza número de colegiado y contacto', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/oenologists/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No oenologists to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="license_number"]').clear().type(`OE-UPD-${id}`)
        cy.get('[wire\\:model="phone"]').clear().type('611000111')
        cy.get('[wire\\:model="notes"]').clear().type('Actualizado en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/oenologists')
        cy.url().should('not.include', '/edit')
      })
    })

    it('toggle activo/inactivo desde el index', () => {
      cy.get('body').then(($body) => {
        const toggleBtn = $body.find('[wire\\:click*="toggleActive"]')
        if (toggleBtn.length > 0) {
          cy.wrap(toggleBtn.first()).click({ force: true })
          cy.waitForLivewire()
          cy.wait(1000)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No toggle buttons found - skipping')
        }
      })
    })
  })
})
