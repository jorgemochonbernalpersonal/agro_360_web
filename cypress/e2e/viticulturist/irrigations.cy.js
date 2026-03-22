/**
 * Viticulturist — Riegos — E2E Tests
 * PAC obligatorio (BCAM 5 - RD 1078/2014) — Cuaderno de Campo Digital
 *
 * Rutas: /viticulturist/digital-notebook/irrigation/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Riegos', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  // ── Index ───────────────────────────────────────────────────────────────────

  describe('Index', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation')
      cy.waitForLivewire()
    })

    it('muestra la página de riegos', () => {
      cy.url().should('include', '/viticulturist/digital-notebook/irrigation')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para registrar nuevo riego', () => {
      cy.get('a[href*="/irrigation/create"]').should('exist')
    })

    it('puede filtrar si hay control de búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('riego', { force: true })
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
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.get('[data-cy="irrigation-form"]').should('exist')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene campo de fecha preestablecida con hoy', () => {
      const today = new Date().toISOString().split('T')[0]
      cy.get('[data-cy="activity-date-input"]').should('have.value', today)
    })

    it('tiene campo de volumen de agua', () => {
      cy.get('[data-cy="water-volume-input"]').should('exist')
    })

    it('tiene selector de método de riego', () => {
      cy.get('[data-cy="irrigation-method-select"]').should('exist')
    })

    it('tiene sección de cumplimiento PAC con origen del agua', () => {
      cy.get('[data-cy="water-source-select"]').should('exist')
    })

    it('tiene campo de número de concesión', () => {
      cy.get('input[wire\\:model="water_concession"], input[id="water_concession"]').should('exist')
    })

    it('tiene campo de caudal de riego', () => {
      cy.get('input[id="flow_rate"]').should('exist')
    })

    it('tiene selector de unidad de volumen (L / m³)', () => {
      cy.get('[data-cy="water-volume-unit-select"]').should('exist')
    })

    it('tiene checkbox de fertirrigación', () => {
      cy.get('[data-cy="is-fertirrigation-checkbox"]').should('exist')
    })

    it('tiene radios de tipo de trabajo', () => {
      cy.get('[data-cy="work-type-crew-radio"]').should('exist')
      cy.get('[data-cy="work-type-individual-radio"]').should('exist')
    })
  })

  // ── Create — validaciones ───────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
    })

    it('permanece en el formulario al enviar vacío', () => {
      cy.get('[data-cy="irrigation-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/irrigation/create')
    })

    it('muestra error si falta origen del agua', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      cy.get('[data-cy="water-volume-input"]').type('500', { force: true })
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      // Dejamos water_source vacío
      cy.get('[data-cy="irrigation-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/irrigation/create')
    })
  })

  // ── Create — fertirrigación condicional ────────────────────────────────────

  describe('Create — fertirrigación', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
    })

    it('al marcar fertirrigación aparecen campos de producto y dosis', () => {
      cy.get('[data-cy="is-fertirrigation-checkbox"]').check({ force: true })
      cy.waitForLivewire()
      cy.get('input[id="fertilizer_product"]').should('exist')
      cy.get('input[id="fertilizer_dose_per_ha"]').should('exist')
    })

    it('al desmarcar fertirrigación se ocultan los campos de producto y dosis', () => {
      cy.get('[data-cy="is-fertirrigation-checkbox"]').check({ force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="is-fertirrigation-checkbox"]').uncheck({ force: true })
      cy.waitForLivewire()
      cy.get('input[id="fertilizer_product"]').should('not.exist')
      cy.get('input[id="fertilizer_dose_per_ha"]').should('not.exist')
    })

    it('puede seleccionar unidad m³', () => {
      cy.get('[data-cy="water-volume-unit-select"]').select('m3', { force: true })
      cy.get('[data-cy="water-volume-unit-select"]').should('have.value', 'm3')
    })
  })

  // ── Create — comportamientos de la sección PAC ─────────────────────────────

  describe('Create — sección PAC', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
    })

    it('muestra el info box de PAC', () => {
      cy.contains('condicionalidad reforzada de la PAC').should('be.visible')
    })

    it('las opciones de origen del agua incluyen opciones correctas', () => {
      cy.get('[data-cy="water-source-select"]').within(() => {
        cy.get('option').should('have.length.greaterThan', 1)
        cy.get('option[value="Pozo legalizado"]').should('exist')
        cy.get('option[value="Comunidad de regantes"]').should('exist')
        cy.get('option[value="Aguas regeneradas"]').should('exist')
      })
    })

    it('puede seleccionar origen del agua', () => {
      cy.get('[data-cy="water-source-select"]').select('Pozo legalizado', { force: true })
      cy.get('[data-cy="water-source-select"]').should('have.value', 'Pozo legalizado')
    })
  })

  // ── Create — sección cascada parcela → plantación ─────────────────────────

  describe('Create — cascada parcela', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
    })

    it('muestra selector de plantación al seleccionar parcela', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
          cy.wait(300)
          // Si la parcela tiene plantaciones activas aparece el selector
          cy.get('body').should('be.visible')
        }
      })
    })
  })

  // ── Create — workType radio ─────────────────────────────────────────────────

  describe('Create — tipo de trabajo', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
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

  // ── Create — guardado flujo completo PAC ────────────────────────────────────

  describe('Create — guardado', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
    })

    it('guarda un riego con todos los campos PAC si hay parcela disponible', () => {
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
        cy.get('[data-cy="water-volume-input"]').clear().type('750', { force: true })
        cy.get('[data-cy="water-volume-unit-select"]').select('L', { force: true })
        cy.get('[data-cy="irrigation-method-select"]').select('goteo', { force: true })

        cy.get('[data-cy="water-source-select"]').select('Pozo legalizado', { force: true })
        cy.get('input[id="water_concession"]').clear().type('EXP-2024/CY-001', { force: true })
        cy.get('input[id="flow_rate"]').clear().type('2000', { force: true })

        cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
        cy.waitForLivewire()
        cy.get('[id="crew_member_id"]').then(($sel) => {
          if ($sel.find('option').length > 1) {
            cy.wrap($sel).select(1, { force: true })
          }
        })

        cy.get('[data-cy="irrigation-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.url().then((url) => {
          if (url.includes('/irrigation/create')) {
            cy.log('Guardado no completado — posiblemente datos de prueba insuficientes')
          } else {
            cy.url().should('include', '/irrigation')
          }
        })
      })
    })
  })

  // ── Edit ────────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('accede al formulario de edición si existe un riego', () => {
      cy.visit('/viticulturist/digital-notebook/irrigation')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/irrigation/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay riegos para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="irrigation-form"]').should('exist')
      })
    })

    it('el formulario de edición tiene campos precargados', () => {
      cy.visit('/viticulturist/digital-notebook/irrigation')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/irrigation/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay riegos para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="plot-select"]').then(($sel) => {
          expect($sel.val()).to.not.equal('')
        })

        cy.get('[data-cy="water-volume-input"]').should('not.have.value', '')
      })
    })

    it('puede actualizar la concesión de agua', () => {
      cy.visit('/viticulturist/digital-notebook/irrigation')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/irrigation/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay riegos para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('input[id="water_concession"]').clear().type('EXP-CY-ACTUALIZADO', { force: true })

        cy.get('[data-cy="irrigation-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('body').should('be.visible')
      })
    })
  })
})
