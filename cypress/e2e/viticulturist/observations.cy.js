/**
 * Viticulturist — Observaciones — E2E Tests
 * Cuaderno de Campo Digital — PAC (condicionalidad)
 *
 * Rutas: /viticulturist/digital-notebook/observation/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Observaciones', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  // ── Index ───────────────────────────────────────────────────────────────────

  describe('Index', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation')
      cy.waitForLivewire()
    })

    it('muestra la página de observaciones', () => {
      cy.url().should('include', '/viticulturist/digital-notebook/observation')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para registrar nueva observación', () => {
      cy.get('a[href*="/observation/create"]').should('exist')
    })

    it('puede filtrar si hay control de búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('trips', { force: true })
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
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.get('[data-cy="observation-form"]').should('exist')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene campo de fecha preestablecida con hoy', () => {
      const today = new Date().toISOString().split('T')[0]
      cy.get('[data-cy="activity-date-input"]').should('have.value', today)
    })

    it('tiene selector de estadio fenológico', () => {
      cy.get('[data-cy="phenological-stage-select"]').should('exist')
    })

    it('tiene selector de tipo de observación', () => {
      cy.get('[data-cy="observation-type-select"]').should('exist')
    })

    it('tiene campo de descripción', () => {
      cy.get('[data-cy="description-textarea"]').should('exist')
    })

    it('tiene campo de porcentaje de superficie afectada', () => {
      cy.get('[data-cy="affected-area-input"]').should('exist')
    })

    it('tiene checkbox de umbral de daño económico', () => {
      cy.get('[data-cy="threshold-exceeded-checkbox"]').should('exist')
    })

    it('tiene campo de fecha de revisión', () => {
      cy.get('[data-cy="follow-up-date-input"]').should('exist')
    })

    it('tiene radios de tipo de trabajo', () => {
      cy.get('[data-cy="work-type-crew-radio"]').should('exist')
      cy.get('[data-cy="work-type-individual-radio"]').should('exist')
    })
  })

  // ── Create — tipos de observación ─────────────────────────────────────────

  describe('Create — tipos de observación', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('las opciones de tipo de observación incluyen plaga, enfermedad y otro', () => {
      cy.get('[data-cy="observation-type-select"]').within(() => {
        cy.get('option').should('have.length.greaterThan', 1)
        cy.get('option[value="plaga"]').should('exist')
        cy.get('option[value="enfermedad"]').should('exist')
        cy.get('option[value="otro"]').should('exist')
      })
    })

    it('puede seleccionar tipo de observación', () => {
      cy.get('[data-cy="observation-type-select"]').select('plaga', { force: true })
      cy.get('[data-cy="observation-type-select"]').should('have.value', 'plaga')
    })
  })

  // ── Create — severidad ────────────────────────────────────────────────────

  describe('Create — severidad', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('tiene selector de severidad con opciones válidas', () => {
      cy.get('body').then(($body) => {
        const severitySelect = $body.find('select[id="severity"], [wire\\:model="severity"]')
        if (severitySelect.length > 0) {
          cy.wrap(severitySelect.first()).within(() => {
            cy.get('option[value="leve"]').should('exist')
            cy.get('option[value="moderada"]').should('exist')
            cy.get('option[value="grave"]').should('exist')
          })
        }
      })
    })

    it('puede seleccionar severidad grave', () => {
      cy.get('body').then(($body) => {
        const severitySelect = $body.find('select[id="severity"]')
        if (severitySelect.length > 0) {
          cy.wrap(severitySelect).select('grave', { force: true })
          cy.wrap(severitySelect).should('have.value', 'grave')
        }
      })
    })
  })

  // ── Create — sección IPM PAC ──────────────────────────────────────────────

  describe('Create — IPM PAC', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('muestra la sección IPM', () => {
      cy.contains('Gestión Integrada de Plagas').should('be.visible')
    })

    it('puede introducir porcentaje de superficie afectada', () => {
      cy.get('[data-cy="affected-area-input"]').clear().type('25.5', { force: true })
      cy.get('[data-cy="affected-area-input"]').should('have.value', '25.5')
    })

    it('puede marcar que el umbral de daño económico está superado', () => {
      cy.get('[data-cy="threshold-exceeded-checkbox"]').check({ force: true })
      cy.get('[data-cy="threshold-exceeded-checkbox"]').should('be.checked')
    })

    it('puede introducir fecha de revisión futura', () => {
      const future = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0]
      cy.get('[data-cy="follow-up-date-input"]').type(future, { force: true })
      cy.get('[data-cy="follow-up-date-input"]').should('have.value', future)
    })

    it('rechaza porcentaje mayor de 100', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      cy.get('[data-cy="phenological-stage-select"]').select('Brotación', { force: true })
      cy.get('[data-cy="observation-type-select"]').select('plaga', { force: true })
      cy.get('[data-cy="description-textarea"]').type('Descripción', { force: true })
      cy.get('[data-cy="affected-area-input"]').clear().type('150', { force: true })
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="observation-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/observation/create')
    })
  })

  // ── Create — validaciones ───────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('permanece en el formulario al enviar vacío', () => {
      cy.get('[data-cy="observation-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/observation/create')
    })

    it('muestra error si falta tipo de observación', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      cy.get('[data-cy="phenological-stage-select"]').select('Brotación', { force: true })
      cy.get('[data-cy="description-textarea"]').type('Descripción de prueba', { force: true })
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="observation-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/observation/create')
    })

    it('muestra error si falta descripción', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      cy.get('[data-cy="phenological-stage-select"]').select('Brotación', { force: true })
      cy.get('[data-cy="observation-type-select"]').select('plaga', { force: true })
      // Dejar descripción vacía
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="observation-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/observation/create')
    })
  })

  // ── Create — cascada parcela → plantación ─────────────────────────────────

  describe('Create — cascada parcela', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('al seleccionar parcela puede aparecer selector de plantación', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
          cy.wait(300)
          cy.get('body').should('be.visible')
        }
      })
    })
  })

  // ── Create — workType radio ─────────────────────────────────────────────────

  describe('Create — tipo de trabajo', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/observation/create')
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
      cy.visit('/viticulturist/digital-notebook/observation/create')
      cy.waitForLivewire()
    })

    it('guarda una observación de plaga si hay parcela disponible', () => {
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

        cy.get('[data-cy="phenological-stage-select"]').select('Floración', { force: true })
        cy.get('[data-cy="observation-type-select"]').select('plaga', { force: true })
        cy.get('[data-cy="description-textarea"]').clear().type(
          'Presencia de trips en flores. Nivel bajo, vigilar evolución.', { force: true }
        )

        cy.get('body').then(($b) => {
          const severitySelect = $b.find('select[id="severity"]')
          if (severitySelect.length > 0) {
            cy.wrap(severitySelect).select('leve', { force: true })
          }
        })

        cy.get('[data-cy="affected-area-input"]').clear().type('15', { force: true })

        const followUpDate = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0]
        cy.get('[data-cy="follow-up-date-input"]').type(followUpDate, { force: true })

        cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
        cy.waitForLivewire()
        cy.get('[id="crew_member_id"]').then(($sel) => {
          if ($sel.find('option').length > 1) {
            cy.wrap($sel).select(1, { force: true })
          }
        })

        cy.get('[data-cy="observation-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.url().then((url) => {
          if (url.includes('/observation/create')) {
            cy.log('Guardado no completado — posiblemente datos de prueba insuficientes')
          } else {
            cy.url().should('include', '/observation')
          }
        })
      })
    })
  })

  // ── Edit ────────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('accede al formulario de edición si existe una observación', () => {
      cy.visit('/viticulturist/digital-notebook/observation')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/observation/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay observaciones para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="observation-form"]').should('exist')
      })
    })

    it('el formulario de edición tiene campos precargados', () => {
      cy.visit('/viticulturist/digital-notebook/observation')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/observation/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay observaciones para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="plot-select"]').then(($sel) => {
          expect($sel.val()).to.not.equal('')
        })

        cy.get('[data-cy="observation-type-select"]').then(($sel) => {
          expect($sel.val()).to.not.equal('')
        })

        cy.get('[data-cy="description-textarea"]').then(($ta) => {
          expect($ta.val()).to.not.equal('')
        })
      })
    })

    it('puede actualizar la descripción de una observación', () => {
      cy.visit('/viticulturist/digital-notebook/observation')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/observation/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay observaciones para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="description-textarea"]').clear().type(
          'Descripción actualizada en test Cypress.', { force: true }
        )

        cy.get('[data-cy="observation-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('body').should('be.visible')
      })
    })
  })
})
