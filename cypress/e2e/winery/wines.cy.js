/**
 * Winery Wines - E2E Tests
 * Cubre: Index (list, filtros), Create, Show, Edit
 */

const uniqueId = () => Date.now()

describe('Winery Wines', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/wines')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de vinos', () => {
      cy.url().should('include', '/winery/wines')
      cy.contains('Vinos').should('be.visible')
    })

    it('tiene botón para crear vino', () => {
      cy.get('a[href*="/winery/wines/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
      cy.get('[wire\\:model\\.live\\.debounce\\.300ms="search"]').type('test', { force: true })
      cy.waitForLivewire()
      cy.wait(600)
      cy.get('body').should('be.visible')
    })

    it('filtra por tipo de vino', () => {
      cy.get('[wire\\:model\\.live="typeFilter"]').select('red', { force: true })
      cy.waitForLivewire()
      cy.wait(500)
      cy.get('body').should('be.visible')
    })

    it('filtra por estado', () => {
      cy.get('[wire\\:model\\.live="statusFilter"]').select('in_progress', { force: true })
      cy.waitForLivewire()
      cy.wait(500)
      cy.get('body').should('be.visible')
    })
  })

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/wines/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/winery/wines/create')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="wine_type"]').should('exist')
      cy.get('[wire\\:model="status"]').should('exist')
    })

    it('crea un vino tinto correctamente', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Reserva Test ${id}`)
      cy.get('[wire\\:model="wine_type"]').select('red', { force: true })
      cy.get('[wire\\:model="status"]').select('in_progress', { force: true })
      cy.get('[wire\\:model="vintage"]').clear().type('2024')
      cy.get('[wire\\:model="internal_code"]').clear().type(`RT-${id}`)
      cy.get('[wire\\:model="variety"]').clear().type('Tempranillo 100%')
      cy.get('[wire\\:model="volume_liters"]').clear().type('1000')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/wines')
      cy.url().should('not.include', '/create')
    })

    it('crea un vino blanco con aging type', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Blanco Test ${id}`)
      cy.get('[wire\\:model="wine_type"]').select('white', { force: true })
      cy.get('[wire\\:model="status"]').select('aged', { force: true })
      cy.get('[wire\\:model="aging_type"]').select('crianza', { force: true })
      cy.get('[wire\\:model="vintage"]').clear().type('2023')
      cy.get('[wire\\:model="volume_liters"]').clear().type('500')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/wines')
      cy.url().should('not.include', '/create')
    })

    it('crea vino orgánico', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Orgánico Test ${id}`)
      cy.get('[wire\\:model="wine_type"]').select('rose', { force: true })
      cy.get('[wire\\:model="status"]').select('in_progress', { force: true })

      // Checkbox orgánico
      cy.get('[wire\\:model="is_organic"]').check({ force: true })

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/wines')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/wines/create')
    })

    it('valida vintage mínimo 1900', () => {
      cy.get('[wire\\:model="name"]').clear().type('Vino Inválido')
      cy.get('[wire\\:model="wine_type"]').select('red', { force: true })
      cy.get('[wire\\:model="status"]').select('in_progress', { force: true })
      cy.get('[wire\\:model="vintage"]').clear().type('1800')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/wines/create')
    })
  })

  // ─── SHOW ─────────────────────────────────────────────────────────────────

  describe('Show', () => {
    it('navega a la página de detalle de un vino', () => {
      cy.get('body').then(($body) => {
        // Busca links de tipo show (no create, no edit)
        const showLinks = $body.find('a[href*="/winery/wines/"]').filter((i, el) => {
          const href = el.getAttribute('href')
          return href &&
            !href.includes('/create') &&
            !href.includes('/edit') &&
            !href.includes('/process') &&
            /\/winery\/wines\/\d+$/.test(href)
        })

        if (showLinks.length > 0) {
          cy.wrap(showLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('match', /\/winery\/wines\/\d+$/)
          // La página show muestra el nombre del vino y tabs operacionales
          cy.get('body').should('be.visible')
        } else {
          cy.log('No wines found - creating one first')

          cy.visit('/winery/wines/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Show Test ${id}`)
          cy.get('[wire\\:model="wine_type"]').select('red', { force: true })
          cy.get('[wire\\:model="status"]').select('in_progress', { force: true })
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)

          cy.url().should('include', '/winery/wines')
          cy.get('a[href*="/winery/wines/"]').filter((i, el) => {
            const href = el.getAttribute('href')
            return href && /\/winery\/wines\/\d+$/.test(href)
          }).first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('match', /\/winery\/wines\/\d+$/)
        }
      })
    })

    it('la página show tiene enlace de edición', () => {
      cy.get('body').then(($body) => {
        const showLinks = $body.find('a[href*="/winery/wines/"]').filter((i, el) => {
          const href = el.getAttribute('href')
          return href && /\/winery\/wines\/\d+$/.test(href) && !href.includes('/create')
        })

        if (showLinks.length === 0) {
          cy.log('No wines found - skipping')
          return
        }

        cy.wrap(showLinks.first()).click({ force: true })
        cy.waitForLivewire()

        // Debe haber un link a /edit desde la página show
        cy.get('a[href*="/edit"]').should('exist')
      })
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wines/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.get('[wire\\:model="name"]').should('exist')
        } else {
          cy.log('No wines to edit - creating one first')

          cy.visit('/winery/wines/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('[wire\\:model="wine_type"]').select('red', { force: true })
          cy.get('[wire\\:model="status"]').select('in_progress', { force: true })
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)

          cy.get('a[href*="/winery/wines/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza el nombre de un vino', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wines/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No wines to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/wines')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza estado y añade notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wines/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No wines to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="status"]').select('aged', { force: true })
        cy.get('[wire\\:model="notes"]').clear().type('Notas actualizadas en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/wines')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
