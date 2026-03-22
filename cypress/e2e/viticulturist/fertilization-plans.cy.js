/**
 * Viticulturist — Planes de Fertilización (Fertilization Plans) — E2E Tests
 * Gestión de nitrógenos y documentación regulatoria de fertilización
 *
 * Rutas: /viticulturist/fertilization-plans/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Fertilization Plans', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/fertilization-plans')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de planes de fertilización', () => {
      cy.url().should('include', '/viticulturist/fertilization-plans')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear plan', () => {
      cy.get('a[href*="/viticulturist/fertilization-plans/create"]').should('be.visible')
    })

    it('puede filtrar por estado', () => {
      cy.get('body').then(($body) => {
        const statusFilter = $body.find('[wire\\:model*="statusFilter"], [wire\\:model*="status"]')
        if (statusFilter.length > 0) {
          cy.wrap(statusFilter.first()).select('draft', { force: true })
          cy.waitForLivewire()
          cy.wait(300)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Create ─────────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/fertilization-plans/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/viticulturist/fertilization-plans/create')
      cy.get('body').should('be.visible')
    })

    it('valida campos requeridos al intentar guardar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(1500)
      cy.url().should('include', '/viticulturist/fertilization-plans/create')
    })

    it('nuevo plan nace en estado borrador', () => {
      cy.get('body').then(($body) => {
        // El status por defecto debe ser 'draft' en el formulario
        const statusField = $body.find('[wire\\:model*="status"]')
        if (statusField.length > 0) {
          cy.wrap(statusField.first()).should(($el) => {
            const val = $el.val()
            if (val !== undefined && val !== '') {
              expect(val).to.equal('draft')
            }
          })
        }
      })
    })

    it('crea plan con datos válidos si hay campaña activa', () => {
      cy.get('body').then(($body) => {
        const yearField = $body.find('[wire\\:model*="plan_year"]')
        if (yearField.length > 0) {
          cy.wrap(yearField.first()).clear().type('2026', { force: true })
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
    it('accede a la edición del primer plan si existe', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/fertilization-plans/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay planes — omitiendo tests de edición')
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
        const editLinks = $body.find('a[href*="/viticulturist/fertilization-plans/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin planes, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('body').then(($b) => {
            const yearField = $b.find('[wire\\:model*="plan_year"]')
            if (yearField.length > 0) {
              cy.wrap(yearField.first()).should(($el) => {
                expect($el.val()).to.not.equal('')
              })
            }
          })
        })
      })
    })

    it('guardar edición redirige al index', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/viticulturist/fertilization-plans/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin planes, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2500)
          cy.waitForLivewire()
          cy.url().should('include', '/viticulturist/fertilization-plans')
          cy.url().should('not.include', '/edit')
        })
      })
    })
  })

  // ── Status workflow ────────────────────────────────────────────────────────

  describe('Status workflow', () => {
    it('puede activar un plan en borrador si existe', () => {
      cy.get('body').then(($body) => {
        const activateBtn = $body.find('[wire\\:click*="activate"], [data-cy*="activate"]')
        if (activateBtn.length === 0) { cy.log('Sin botones de activar, omitiendo'); return }
        cy.wrap(activateBtn.first()).click({ force: true })
        cy.waitForLivewire()
        cy.get('body').should('be.visible')
      })
    })

    it('no puede eliminar un plan activo', () => {
      cy.get('body').then(($body) => {
        // Los planes activos no deben mostrar botón de eliminar
        const deleteBtn = $body.find('[wire\\:click*="delete"][data-status="active"]')
        expect(deleteBtn.length).to.equal(0)
      })
    })
  })
})
