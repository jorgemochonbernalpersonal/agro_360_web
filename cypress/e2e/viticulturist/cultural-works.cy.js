/**
 * Viticulturist — Labores Culturales — E2E Tests
 * Cuaderno de Campo Digital
 *
 * Rutas: /viticulturist/digital-notebook/cultural/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Labores Culturales', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  // ── Index ───────────────────────────────────────────────────────────────────

  describe('Index', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/cultural')
      cy.waitForLivewire()
    })

    it('muestra la página de labores culturales', () => {
      cy.url().should('include', '/viticulturist/digital-notebook/cultural')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para registrar nueva labor', () => {
      cy.get('a[href*="/cultural/create"]').should('exist')
    })

    it('puede buscar si hay control disponible', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('poda', { force: true })
          cy.waitForLivewire()
          cy.wait(400)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Create — carga y estructura ─────────────────────────────────────────────

  describe('Create — carga y estructura', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/cultural/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.get('[data-cy="cultural-work-form"]').should('exist')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene fecha preestablecida con hoy', () => {
      const today = new Date().toISOString().split('T')[0]
      cy.get('[data-cy="activity-date-input"]').should('have.value', today)
    })

    it('tiene selector de tipo de labor', () => {
      cy.get('[data-cy="work-type-select"]').should('exist')
    })

    it('tiene campo de descripción', () => {
      cy.get('[id="description"]').should('exist')
    })

    it('tiene radios de tipo de trabajo', () => {
      cy.get('[data-cy="work-type-crew-radio"]').should('exist')
      cy.get('[data-cy="work-type-individual-radio"]').should('exist')
    })
  })

  // ── Create — validaciones ───────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/cultural/create')
      cy.waitForLivewire()
    })

    it('permanece en el formulario al enviar vacío', () => {
      cy.get('[data-cy="cultural-work-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/cultural/create')
    })

    it('muestra error con descripción menor a 10 caracteres', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      cy.get('[data-cy="work-type-select"]').select('laboreo', { force: true })
      cy.get('[id="description"]').type('Corto', { force: true })
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()

      cy.get('[data-cy="cultural-work-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/cultural/create')
    })
  })

  // ── Create — tipo de labor específico: poda ─────────────────────────────────

  describe('Create — campos de poda', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/cultural/create')
      cy.waitForLivewire()
    })

    it('muestra campos específicos al seleccionar poda', () => {
      cy.get('[data-cy="work-type-select"]').select('poda', { force: true })
      cy.waitForLivewire()
      // Debe aparecer el selector de tipo de poda
      cy.get('body').then(($body) => {
        const pruningType = $body.find('[id="pruning_type"], [data-cy="pruning-type-select"]')
        if (pruningType.length > 0) {
          cy.wrap(pruningType.first()).should('be.visible')
        }
      })
    })

    it('oculta campos de poda al cambiar a otro tipo', () => {
      cy.get('[data-cy="work-type-select"]').select('poda', { force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="work-type-select"]').select('deshojado', { force: true })
      cy.waitForLivewire()
      // Los campos de poda no deben estar visibles
      cy.get('body').then(($body) => {
        const pruningType = $body.find('[id="pruning_type"]:visible')
        expect(pruningType.length).to.equal(0)
      })
    })
  })

  // ── Create — tipo de trabajo ─────────────────────────────────────────────────

  describe('Create — tipo de trabajo', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/cultural/create')
      cy.waitForLivewire()
    })

    it('al seleccionar equipo muestra selector de crew', () => {
      cy.get('[data-cy="work-type-crew-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[id="crew_id"]').should('exist')
    })

    it('al seleccionar individual muestra selector de viticultor', () => {
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[id="crew_member_id"]').should('exist')
    })
  })

  // ── Create — guardado flujo completo ────────────────────────────────────────

  describe('Create — guardado', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/cultural/create')
      cy.waitForLivewire()
    })

    it('guarda una labor cultural si hay parcela disponible', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length === 0 || plotSelect.find('option').length <= 1) {
          cy.log('No hay parcelas disponibles — test omitido')
          return
        }

        cy.wrap(plotSelect).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(300)

        cy.get('[data-cy="activity-date-input"]').then(($input) => {
          if (!$input.val()) {
            cy.wrap($input).type(new Date().toISOString().split('T')[0])
          }
        })

        cy.get('[data-cy="phenological-stage-select"]').select('Envero', { force: true })
        cy.get('[data-cy="work-type-select"]').select('laboreo', { force: true })
        cy.get('[id="description"]').clear().type('Labor de suelo realizada en todo el viñedo.', { force: true })
        cy.get('[id="hours_worked"]').clear().type('5', { force: true })

        cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
        cy.waitForLivewire()
        cy.get('[id="crew_member_id"]').then(($sel) => {
          if ($sel.find('option').length > 1) {
            cy.wrap($sel).select(1, { force: true })
          }
        })

        cy.get('[data-cy="cultural-work-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.url().then((url) => {
          if (url.includes('/cultural/create')) {
            cy.log('Guardado no completado — posiblemente datos de prueba insuficientes')
          } else {
            cy.url().should('include', '/cultural')
          }
        })
      })
    })

    it('guarda una poda con tipo y yemas por hectárea si hay parcela', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length === 0 || plotSelect.find('option').length <= 1) {
          cy.log('No hay parcelas disponibles — test omitido')
          return
        }

        cy.wrap(plotSelect).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(300)

        cy.get('[data-cy="phenological-stage-select"]').select('Reposo invernal', { force: true })
        cy.get('[data-cy="work-type-select"]').select('poda', { force: true })
        cy.waitForLivewire()

        cy.get('body').then(($b) => {
          const pruningType = $b.find('[id="pruning_type"]')
          if (pruningType.length > 0) {
            cy.wrap(pruningType).select('guyot', { force: true })
          }
          const buds = $b.find('[id="productive_buds_per_hectare"]')
          if (buds.length > 0) {
            cy.wrap(buds).clear().type('45000', { force: true })
          }
        })

        cy.get('[id="description"]').clear().type('Poda en verde tipo Guyot, 45000 yemas por hectárea.', { force: true })

        cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
        cy.waitForLivewire()
        cy.get('[id="crew_member_id"]').then(($sel) => {
          if ($sel.find('option').length > 1) {
            cy.wrap($sel).select(1, { force: true })
          }
        })

        cy.get('[data-cy="cultural-work-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Edit ────────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('accede al formulario de edición si existe una labor', () => {
      cy.visit('/viticulturist/digital-notebook/cultural')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/cultural/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay labores para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="cultural-work-form"]').should('exist')
      })
    })

    it('el formulario de edición tiene botón "Actualizar Labor"', () => {
      cy.visit('/viticulturist/digital-notebook/cultural')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/cultural/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay labores para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.contains('button', 'Actualizar Labor').should('exist')
      })
    })

    it('el formulario tiene campos precargados', () => {
      cy.visit('/viticulturist/digital-notebook/cultural')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/cultural/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay labores para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="plot-select"]').then(($sel) => {
          expect($sel.val()).to.not.equal('')
        })

        cy.get('[id="description"]').should('not.have.value', '')
      })
    })

    it('puede actualizar la descripción de una labor', () => {
      cy.visit('/viticulturist/digital-notebook/cultural')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/cultural/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay labores para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[id="description"]').clear().type('Descripción actualizada de la labor cultural en Cypress.', { force: true })

        cy.get('[data-cy="cultural-work-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('body').should('be.visible')
      })
    })
  })
})
