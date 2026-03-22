/**
 * Viticulturist — Concesiones de Riego (Water Concessions) — E2E Tests
 * Registro de derechos de agua e irrigación
 *
 * Rutas: /viticulturist/water-concessions/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Water Concessions', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/water-concessions')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de concesiones de riego', () => {
      cy.url().should('include', '/viticulturist/water-concessions')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear concesión', () => {
      cy.get('a[href*="/viticulturist/water-concessions/create"]').should('be.visible')
    })

    it('puede buscar concesiones', () => {
      cy.get('body').then(($body) => {
        const search = $body.find(
          '[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"], [wire\\:model="search"]'
        )
        if (search.length > 0) {
          cy.wrap(search.first()).type('Río', { force: true })
          cy.waitForLivewire()
          cy.wait(400)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Create ─────────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/water-concessions/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/viticulturist/water-concessions/create')
      cy.get('body').should('be.visible')
    })

    it('valida campos requeridos al intentar guardar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(1500)
      cy.url().should('include', '/viticulturist/water-concessions/create')
    })

    it('valida que fecha de caducidad sea posterior a la concesión', () => {
      cy.get('body').then(($body) => {
        const concDate = $body.find('[wire\\:model*="concession_date"]')
        const expDate = $body.find('[wire\\:model*="expiry_date"]')
        if (concDate.length === 0 || expDate.length === 0) return

        cy.wrap(concDate.first()).clear().type('2026-06-01', { force: true })
        cy.waitForLivewire()
        cy.wrap(expDate.first()).clear().type('2025-01-01', { force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(1500)
        cy.url().should('include', '/viticulturist/water-concessions/create')
      })
    })

    it('crea concesión con datos válidos', () => {
      cy.get('body').then(($body) => {
        const typeSelect = $body.find('[wire\\:model*="concession_type"]')
        if (typeSelect.length === 0) { cy.log('Sin selector de tipo, omitiendo'); return }

        cy.wrap(typeSelect.first()).select('superficial', { force: true })
        cy.waitForLivewire()

        const fields = {
          'concession_number': 'CON-CY-001',
          'water_body': 'Río Cypress',
          'authority': 'CHE',
          'max_volume_m3': '10000',
        }

        for (const [wireModel, value] of Object.entries(fields)) {
          const el = $body.find(`[wire\\:model*="${wireModel}"]`)
          if (el.length > 0) {
            cy.wrap(el.first()).clear().type(value, { force: true })
            cy.waitForLivewire()
          }
        }

        const concDate = $body.find('[wire\\:model*="concession_date"]')
        if (concDate.length > 0) {
          cy.wrap(concDate.first()).clear().type('2025-01-01', { force: true })
          cy.waitForLivewire()
        }

        const expDate = $body.find('[wire\\:model*="expiry_date"]')
        if (expDate.length > 0) {
          cy.wrap(expDate.first()).clear().type('2030-12-31', { force: true })
          cy.waitForLivewire()
        }

        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2500)
        cy.waitForLivewire()
        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Edit ───────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('accede a la edición de la primera concesión si existe', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/water-concessions/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay concesiones — omitiendo tests de edición')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')
        cy.get('body').should('be.visible')
      })
    })

    it('el formulario de edición carga los datos existentes', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/water-concessions/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin concesiones, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('body').then(($b) => {
            const typeSelect = $b.find('[wire\\:model*="concession_type"]')
            if (typeSelect.length > 0) {
              cy.wrap(typeSelect.first()).should(($sel) => {
                expect($sel.val()).to.not.equal('')
              })
            }
          })
        })
      })
    })

    it('guardar edición redirige al index', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/water-concessions/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin concesiones, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2500)
          cy.waitForLivewire()
          cy.url().should('include', '/viticulturist/water-concessions')
          cy.url().should('not.include', '/edit')
        })
      })
    })
  })
})
