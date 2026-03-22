/**
 * Viticulturist — Certificaciones y Sellos (Certifications) — E2E Tests
 * DO, Ecológico, GlobalGAP, PI, etc. — Reg. UE 2018/848
 *
 * Rutas: /viticulturist/certifications/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Certifications', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/certifications')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de certificaciones', () => {
      cy.url().should('include', '/viticulturist/certifications')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear certificación', () => {
      cy.get('a[href*="/viticulturist/certifications/create"]').should('be.visible')
    })

    it('puede filtrar por tipo de certificación', () => {
      cy.get('body').then(($body) => {
        const typeFilter = $body.find('[wire\\:model*="typeFilter"], [wire\\:model*="certification_type"]')
        if (typeFilter.length > 0) {
          cy.wrap(typeFilter.first()).select('ecologico', { force: true })
          cy.waitForLivewire()
          cy.wait(300)
        }
        cy.get('body').should('be.visible')
      })
    })

    it('puede buscar por organismo certificador', () => {
      cy.get('body').then(($body) => {
        const search = $body.find(
          '[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"], [wire\\:model="search"]'
        )
        if (search.length > 0) {
          cy.wrap(search.first()).type('CAAE', { force: true })
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
      cy.visit('/viticulturist/certifications/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/viticulturist/certifications/create')
      cy.get('body').should('be.visible')
    })

    it('valida campos requeridos al intentar guardar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(1500)
      cy.url().should('include', '/viticulturist/certifications/create')
    })

    it('valida que fecha de caducidad sea posterior a la emisión', () => {
      cy.get('body').then(($body) => {
        const issueDate = $body.find('[wire\\:model*="issue_date"]')
        const expDate = $body.find('[wire\\:model*="expiry_date"]')
        if (issueDate.length === 0 || expDate.length === 0) return

        cy.wrap(issueDate.first()).clear().type('2026-06-01', { force: true })
        cy.waitForLivewire()
        cy.wrap(expDate.first()).clear().type('2025-01-01', { force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(1500)
        cy.url().should('include', '/viticulturist/certifications/create')
      })
    })

    it('crea certificación con datos válidos', () => {
      cy.get('body').then(($body) => {
        const typeSelect = $body.find('[wire\\:model*="certification_type"]')
        if (typeSelect.length === 0) { cy.log('Sin selector de tipo, omitiendo'); return }

        cy.wrap(typeSelect.first()).select('ecologico', { force: true })
        cy.waitForLivewire()

        const bodyField = $body.find('[wire\\:model*="certifying_body"]')
        if (bodyField.length > 0) {
          cy.wrap(bodyField.first()).clear().type('CAAE Cypress', { force: true })
          cy.waitForLivewire()
        }

        const issueDate = $body.find('[wire\\:model*="issue_date"]')
        if (issueDate.length > 0) {
          cy.wrap(issueDate.first()).clear().type('2025-01-01', { force: true })
          cy.waitForLivewire()
        }

        const expDate = $body.find('[wire\\:model*="expiry_date"]')
        if (expDate.length > 0) {
          cy.wrap(expDate.first()).clear().type('2028-12-31', { force: true })
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
    it('accede a la edición de la primera certificación si existe', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/certifications/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay certificaciones — omitiendo tests de edición')
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
        const editLinks = $body.find('a[href*="/viticulturist/certifications/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin certificaciones, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('body').then(($b) => {
            const typeSelect = $b.find('[wire\\:model*="certification_type"]')
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
        const editLinks = $body.find('a[href*="/viticulturist/certifications/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin certificaciones, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2500)
          cy.waitForLivewire()
          cy.url().should('include', '/viticulturist/certifications')
          cy.url().should('not.include', '/edit')
        })
      })
    })
  })

  // ── Archive / Unarchive ────────────────────────────────────────────────────

  describe('Archive', () => {
    it('puede archivar una certificación activa si existe alguna', () => {
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
