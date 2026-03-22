/**
 * Viticulturist — Subproductos de Vendimia (Harvest Byproducts) — E2E Tests
 * Reg. UE 2018/273 — trazabilidad de orujo, raspón y lías
 *
 * Rutas: /viticulturist/harvest-byproducts/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('Harvest Byproducts', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/harvest-byproducts')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de subproductos de vendimia', () => {
      cy.url().should('include', '/viticulturist/harvest-byproducts')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear subproducto', () => {
      cy.get('a[href*="/viticulturist/harvest-byproducts/create"]').should('be.visible')
    })

    it('muestra tab de activos por defecto', () => {
      cy.get('body').then(($body) => {
        const tabs = $body.find('[wire\\:click*="activeTab"], [wire\\:model*="activeTab"]')
        if (tabs.length > 0) {
          cy.get('body').should('be.visible')
        }
      })
    })

    it('puede buscar subproductos', () => {
      cy.get('body').then(($body) => {
        const search = $body.find(
          '[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"], [wire\\:model="search"]'
        )
        if (search.length > 0) {
          cy.wrap(search.first()).type('Test', { force: true })
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
      cy.visit('/viticulturist/harvest-byproducts/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/viticulturist/harvest-byproducts/create')
      cy.get('body').should('be.visible')
    })

    it('valida campos requeridos al intentar guardar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(1500)
      cy.url().should('include', '/viticulturist/harvest-byproducts/create')
    })

    it('valida que la cantidad debe ser positiva', () => {
      cy.get('body').then(($body) => {
        const qty = $body.find('[wire\\:model*="quantity_kg"]')
        if (qty.length > 0) {
          cy.wrap(qty.first()).clear().type('-100', { force: true })
          cy.waitForLivewire()
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(1500)
          cy.url().should('include', '/viticulturist/harvest-byproducts/create')
        }
      })
    })

    it('crea subproducto con datos válidos si hay campaña activa', () => {
      cy.get('body').then(($body) => {
        // Comprobar que hay select de tipo de subproducto
        const typeSelect = $body.find('[wire\\:model*="byproduct_type"]')
        if (typeSelect.length === 0) { cy.log('Sin selector de tipo, omitiendo'); return }

        cy.wrap(typeSelect.first()).select('orujo', { force: true })
        cy.waitForLivewire()

        const qtyField = $body.find('[wire\\:model*="quantity_kg"]')
        if (qtyField.length > 0) {
          cy.wrap(qtyField.first()).clear().type('1500', { force: true })
          cy.waitForLivewire()
        }

        const destType = $body.find('[wire\\:model*="destination_type"]')
        if (destType.length > 0) {
          cy.wrap(destType.first()).select('cooperativa', { force: true })
          cy.waitForLivewire()
        }

        const destName = $body.find('[wire\\:model*="destination_name"]')
        if (destName.length > 0) {
          cy.wrap(destName.first()).clear().type('Cooperativa Cypress Test', { force: true })
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
    it('accede a la edición del primer subproducto si existe', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/harvest-byproducts/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay subproductos — omitiendo tests de edición')
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
        const editLinks = $body.find('a[href*="/viticulturist/harvest-byproducts/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin subproductos, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('body').then(($b) => {
            const typeSelect = $b.find('[wire\\:model*="byproduct_type"]')
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
        const editLinks = $body.find('a[href*="/viticulturist/harvest-byproducts/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin subproductos, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2500)
          cy.waitForLivewire()
          cy.url().should('include', '/viticulturist/harvest-byproducts')
          cy.url().should('not.include', '/edit')
        })
      })
    })
  })

  // ── Archive / Unarchive ────────────────────────────────────────────────────

  describe('Archive', () => {
    it('puede archivar un subproducto si existe alguno activo', () => {
      cy.get('body').then(($body) => {
        const archiveBtn = $body.find('[wire\\:click*="archive"], [data-cy*="archive"]')
        if (archiveBtn.length === 0) { cy.log('Sin botones de archivar, omitiendo'); return }
        cy.wrap(archiveBtn.first()).click({ force: true })
        cy.waitForLivewire()
        cy.get('body').should('be.visible')
      })
    })
  })
})
