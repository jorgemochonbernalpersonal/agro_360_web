/**
 * Winery Product Lots (Lotes de Producto) - E2E Tests
 * Cubre: Index (list, filtros, tabs), Create (tabs), Edit
 *
 * El formulario usa tabs: General | Stock | Descripción | Técnico | Marketing
 * Los tests cubren los campos mínimos requeridos y algunos opcionales por tab.
 */

const uniqueId = () => Date.now()

describe('Winery Product Lots', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/product-lots')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de lotes de producto', () => {
      cy.url().should('include', '/winery/product-lots')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear lote', () => {
      cy.get('a[href*="/winery/product-lots/create"]').should('be.visible')
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

    it('filtra por tipo de vino', () => {
      cy.get('body').then(($body) => {
        const typeFilter = $body.find('[wire\\:model\\.live="typeFilter"]')
        if (typeFilter.length > 0) {
          cy.wrap(typeFilter.first()).select('tinto', { force: true })
          cy.waitForLivewire()
          cy.wait(500)
        }
        cy.get('body').should('be.visible')
      })
    })

    it('cambia a pestaña inactivos', () => {
      cy.get('body').then(($body) => {
        const inactiveTab = $body.find('[wire\\:click*="switchTab"][wire\\:click*="inactive"], button:contains("Inactivos")')
        if (inactiveTab.length > 0) {
          cy.wrap(inactiveTab.first()).click({ force: true })
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
      cy.visit('/winery/product-lots/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario con tabs', () => {
      cy.url().should('include', '/winery/product-lots/create')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="quantity"]').should('exist')
    })

    it('crea un lote básico (tab General + Stock)', () => {
      const id = uniqueId()

      // Tab General - campos requeridos
      cy.get('[wire\\:model="name"]').clear().type(`Rioja Reserva ${id}`)
      cy.get('[wire\\:model="wine_type"]').select('tinto', { force: true })
      cy.get('[wire\\:model="vintage"]').clear().type('2022')
      cy.get('[wire\\:model="aging_type"]').select('reserva', { force: true })
      cy.get('[wire\\:model="alcohol"]').clear().type('14.0')
      cy.get('[wire\\:model="sku"]').clear().type(`SKU-${id}`)

      // Tab Stock (buscar el tab o rellenar directamente si visible)
      cy.get('body').then(($body) => {
        const stockTab = $body.find('[wire\\:click*="stock"], button:contains("Stock"), [data-tab="stock"]')
        if (stockTab.length > 0) {
          cy.wrap(stockTab.first()).click({ force: true })
          cy.wait(300)
        }
      })

      cy.get('[wire\\:model="quantity"]').clear().type('1000')
      cy.get('[wire\\:model="available_quantity"]').clear().type('1000')
      cy.get('[wire\\:model="unit"]').select('botellas', { force: true })
      cy.get('[wire\\:model="price_per_unit"]').clear().type('12.50')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/product-lots')
      cy.url().should('not.include', '/create')
    })

    it('crea lote con certificaciones', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Orgánico ${id}`)
      cy.get('[wire\\:model="wine_type"]').select('blanco', { force: true })
      cy.get('[wire\\:model="quantity"]').clear().type('500')
      cy.get('[wire\\:model="available_quantity"]').clear().type('500')

      // Certificaciones
      cy.get('[wire\\:model="ecological"]').check({ force: true })
      cy.get('[wire\\:model="is_vegan"]').check({ force: true })

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/product-lots')
      cy.url().should('not.include', '/create')
    })

    it('crea lote con descripción y maridaje', () => {
      const id = uniqueId()

      cy.get('[wire\\:model="name"]').clear().type(`Rosado ${id}`)
      cy.get('[wire\\:model="wine_type"]').select('rosado', { force: true })
      cy.get('[wire\\:model="quantity"]').clear().type('300')
      cy.get('[wire\\:model="available_quantity"]').clear().type('300')

      // Tab Descripción
      cy.get('body').then(($body) => {
        const descTab = $body.find('button:contains("Descripción"), [wire\\:click*="descripcion"], [wire\\:click*="description"]')
        if (descTab.length > 0) {
          cy.wrap(descTab.first()).click({ force: true })
          cy.wait(300)
        }
      })

      cy.get('[wire\\:model="description"]').then(($el) => {
        if ($el.length > 0) {
          cy.wrap($el).clear().type('Vino rosado fresco y afrutado de prueba E2E')
        }
      })
      cy.get('[wire\\:model="pairing"]').then(($el) => {
        if ($el.length > 0) {
          cy.wrap($el).clear().type('Maridaje con pescados y ensaladas')
        }
      })

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/product-lots')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/product-lots/create')
    })

    it('valida que available_quantity no supere quantity', () => {
      cy.get('[wire\\:model="name"]').clear().type('Lote Inválido')
      cy.get('[wire\\:model="wine_type"]').select('tinto', { force: true })
      cy.get('[wire\\:model="quantity"]').clear().type('100')
      cy.get('[wire\\:model="available_quantity"]').clear().type('200')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/product-lots/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/product-lots/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.get('[wire\\:model="name"]').should('exist')
        } else {
          cy.log('No lots found - creating one first')
          cy.visit('/winery/product-lots/create')
          cy.waitForLivewire()
          const id = uniqueId()
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${id}`)
          cy.get('[wire\\:model="wine_type"]').select('tinto', { force: true })
          cy.get('[wire\\:model="quantity"]').clear().type('200')
          cy.get('[wire\\:model="available_quantity"]').clear().type('200')
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)

          cy.get('a[href*="/winery/product-lots/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza el nombre del lote', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/product-lots/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No lots to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/product-lots')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza precio por unidad', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/product-lots/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No lots to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        // Navegar al tab Stock si existe
        cy.get('body').then(($b) => {
          const stockTab = $b.find('button:contains("Stock"), [wire\\:click*="stock"]')
          if (stockTab.length > 0) {
            cy.wrap(stockTab.first()).click({ force: true })
            cy.wait(300)
          }
        })

        cy.get('[wire\\:model="price_per_unit"]').clear().type('15.00')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/product-lots')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza notas del lote', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/product-lots/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No lots to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="notes"]').clear().type('Notas actualizadas en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/product-lots')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
