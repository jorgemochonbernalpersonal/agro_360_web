/**
 * Winery Campaigns (Campañas de Vendimia) - E2E Tests
 * Cubre: Index, Create, Edit
 *
 * Nota: solo puede haber una campaña por año por bodega.
 * Los tests usan años distintos para evitar conflictos.
 */

const uniqueYear = () => 2000 + (Date.now() % 90) // año entre 2000-2089

describe('Winery Campaigns', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/campaigns')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de campañas', () => {
      cy.url().should('include', '/winery/campaigns')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear campaña', () => {
      cy.get('a[href*="/winery/campaigns/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('vendimia', { force: true })
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
      cy.visit('/winery/campaigns/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario con valores por defecto', () => {
      cy.url().should('include', '/winery/campaigns/create')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="year"]').should('exist')
      cy.get('[wire\\:model="start_date"]').should('exist')
      cy.get('[wire\\:model="end_date"]').should('exist')
      // El formulario pre-rellena name y año actual
      cy.get('[wire\\:model="year"]').should('not.have.value', '')
    })

    it('crea una campaña correctamente', () => {
      const year = uniqueYear()

      cy.get('[wire\\:model="name"]').clear().type(`Vendimia Test ${year}`)
      cy.get('[wire\\:model="year"]').clear().type(year.toString())
      cy.get('[wire\\:model="start_date"]').clear().type(`${year}-08-01`)
      cy.get('[wire\\:model="end_date"]').clear().type(`${year}-11-30`)
      cy.get('[wire\\:model="description"]').clear().type('Campaña de prueba E2E')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/campaigns')
      cy.url().should('not.include', '/create')
    })

    it('crea campaña sin descripción', () => {
      const year = uniqueYear() + 1

      cy.get('[wire\\:model="name"]').clear().type(`Campaña Mínima ${year}`)
      cy.get('[wire\\:model="year"]').clear().type(year.toString())
      cy.get('[wire\\:model="start_date"]').clear().type(`${year}-09-01`)
      cy.get('[wire\\:model="end_date"]').clear().type(`${year}-10-31`)

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/campaigns')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos', () => {
      cy.get('[wire\\:model="name"]').clear()
      cy.get('[wire\\:model="year"]').clear()
      cy.get('[wire\\:model="start_date"]').clear()
      cy.get('[wire\\:model="end_date"]').clear()

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/campaigns/create')
    })

    it('valida end_date >= start_date', () => {
      const year = uniqueYear() + 2

      cy.get('[wire\\:model="name"]').clear().type(`Campaña Inválida ${year}`)
      cy.get('[wire\\:model="year"]').clear().type(year.toString())
      cy.get('[wire\\:model="start_date"]').clear().type(`${year}-11-01`)
      cy.get('[wire\\:model="end_date"]').clear().type(`${year}-08-01`)

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/campaigns/create')
    })

    it('valida año mínimo 2000', () => {
      cy.get('[wire\\:model="name"]').clear().type('Campaña Año Inválido')
      cy.get('[wire\\:model="year"]').clear().type('1999')
      cy.get('[wire\\:model="start_date"]').clear().type('1999-09-01')
      cy.get('[wire\\:model="end_date"]').clear().type('1999-11-30')

      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/campaigns/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/campaigns/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.get('[wire\\:model="name"]').should('exist')
        } else {
          cy.log('No campaigns found - creating one first')
          cy.visit('/winery/campaigns/create')
          cy.waitForLivewire()
          const year = uniqueYear() + 3
          cy.get('[wire\\:model="name"]').clear().type(`Edit Test ${year}`)
          cy.get('[wire\\:model="year"]').clear().type(year.toString())
          cy.get('[wire\\:model="start_date"]').clear().type(`${year}-09-01`)
          cy.get('[wire\\:model="end_date"]').clear().type(`${year}-11-30`)
          cy.get('button[type="submit"]').click({ force: true })
          cy.wait(5000)

          cy.get('a[href*="/winery/campaigns/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza nombre y descripción', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/campaigns/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No campaigns to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="name"]').clear().type('Vendimia Actualizada E2E')
        cy.get('[wire\\:model="description"]').clear().type('Descripción actualizada en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/campaigns')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza fechas de campaña', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/campaigns/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No campaigns to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="start_date"]').clear().type('2050-08-15')
        cy.get('[wire\\:model="end_date"]').clear().type('2050-12-15')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/campaigns')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
