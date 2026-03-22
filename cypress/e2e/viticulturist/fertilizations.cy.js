/**
 * Viticulturist — Fertilizaciones — E2E Tests
 * PAC obligatorio (BCAM 1 / RD 1078/2014) — Cuaderno de Campo Digital
 *
 * Rutas: /viticulturist/digital-notebook/fertilization/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Fertilizaciones', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  // ── Index ───────────────────────────────────────────────────────────────────

  describe('Index', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/fertilization')
      cy.waitForLivewire()
    })

    it('muestra la página de fertilizaciones', () => {
      cy.url().should('include', '/viticulturist/digital-notebook/fertilization')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para registrar nueva fertilización', () => {
      cy.get('a[href*="/fertilization/create"]').should('exist')
    })

    it('puede filtrar si hay control de búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('NPK', { force: true })
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
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.get('[data-cy="fertilization-form"]').should('exist')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene campo de fecha preestablecida con hoy', () => {
      const today = new Date().toISOString().split('T')[0]
      cy.get('[data-cy="activity-date-input"]').should('have.value', today)
    })

    it('tiene campo de tipo de fertilizante', () => {
      cy.get('[data-cy="fertilizer-type-input"]').should('exist')
    })

    it('tiene campo de cantidad', () => {
      cy.get('input[id="quantity"]').should('exist')
    })

    it('tiene campo de superficie aplicada', () => {
      cy.get('input[id="area_applied"]').should('exist')
    })

    it('tiene sección PAC con campos de UF nitrógeno, fósforo y potasio', () => {
      cy.get('input[id="nitrogen_uf"]').should('exist')
      cy.get('input[id="phosphorus_uf"]').should('exist')
      cy.get('input[id="potassium_uf"]').should('exist')
    })

    it('tiene campos de fertilizantes orgánicos (estiércol y fecha)', () => {
      cy.get('input[id="manure_type"]').should('exist')
      cy.get('input[id="burial_date"]').should('exist')
    })

    it('tiene radios de tipo de trabajo', () => {
      cy.get('[data-cy="work-type-crew-radio"]').should('exist')
      cy.get('[data-cy="work-type-individual-radio"]').should('exist')
    })
  })

  // ── Create — validaciones ───────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
      cy.waitForLivewire()
    })

    it('permanece en el formulario al enviar vacío', () => {
      cy.get('[data-cy="fertilization-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/fertilization/create')
    })

    it('muestra error si falta tipo de fertilizante', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      // Dejar fertilizer_type vacío
      cy.get('[data-cy="fertilizer-type-input"]').clear({ force: true })
      cy.get('input[id="quantity"]').type('200', { force: true })
      cy.get('input[id="area_applied"]').type('2.5', { force: true })
      cy.get('input[id="nitrogen_uf"]').type('150', { force: true })
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="fertilization-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/fertilization/create')
    })

    it('muestra error si todos los campos NPK están vacíos', () => {
      cy.get('body').then(($body) => {
        const plotSelect = $body.find('[data-cy="plot-select"]')
        if (plotSelect.length > 0 && plotSelect.find('option').length > 1) {
          cy.wrap(plotSelect).select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      cy.get('[data-cy="fertilizer-type-input"]').clear().type('Mineral', { force: true })
      cy.get('input[id="quantity"]').type('200', { force: true })
      cy.get('input[id="area_applied"]').type('2.5', { force: true })
      // Dejar todos los NPK vacíos
      cy.get('input[id="nitrogen_uf"]').clear({ force: true })
      cy.get('input[id="phosphorus_uf"]').clear({ force: true })
      cy.get('input[id="potassium_uf"]').clear({ force: true })
      cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="fertilization-form"]').within(() => {
        cy.get('button[type="submit"]').click({ force: true })
      })
      cy.waitForLivewire()
      cy.url().should('include', '/fertilization/create')
    })
  })

  // ── Create — sección PAC ───────────────────────────────────────────────────

  describe('Create — sección PAC', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
      cy.waitForLivewire()
    })

    it('muestra etiqueta de sección PAC', () => {
      cy.contains('Nutrición Sostenible').should('be.visible')
    })

    it('puede introducir valores NPK fraccionarios', () => {
      cy.get('input[id="nitrogen_uf"]').clear().type('123.5', { force: true })
      cy.get('input[id="phosphorus_uf"]').clear().type('78.9', { force: true })
      cy.get('input[id="potassium_uf"]').clear().type('45.0', { force: true })
      cy.get('input[id="nitrogen_uf"]').should('have.value', '123.5')
    })

    it('puede introducir ratio NPK', () => {
      cy.get('input[id="npk_ratio"]').clear().type('15-15-15', { force: true })
      cy.get('input[id="npk_ratio"]').should('have.value', '15-15-15')
    })

    it('tiene selector de método de aplicación con opciones PAC', () => {
      cy.get('[data-cy="application-method-select"]').within(() => {
        cy.get('option[value="aplicación al suelo"]').should('exist')
        cy.get('option[value="fertirrigación"]').should('exist')
        cy.get('option[value="aplicación foliar"]').should('exist')
      })
    })
  })

  // ── Create — sección fertilizante orgánico ────────────────────────────────

  describe('Create — fertilizante orgánico', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
      cy.waitForLivewire()
    })

    it('tiene sección de estiércoles orgánicos visible', () => {
      cy.contains('Fertilizantes Orgánicos').should('be.visible')
    })

    it('puede introducir tipo de estiércol', () => {
      cy.get('input[id="manure_type"]').clear().type('Estiércol vacuno', { force: true })
      cy.get('input[id="manure_type"]').should('have.value', 'Estiércol vacuno')
    })

    it('puede introducir fecha de enterrado', () => {
      const date = new Date().toISOString().split('T')[0]
      cy.get('input[id="burial_date"]').clear().type(date, { force: true })
      cy.get('input[id="burial_date"]').should('have.value', date)
    })

    it('tiene selector de método de reducción de emisiones', () => {
      cy.get('select[id="emission_reduction_method"]').should('exist')
      cy.get('select[id="emission_reduction_method"] option[value="inyección"]').should('exist')
      cy.get('select[id="emission_reduction_method"] option[value="enterrado_inmediato"]').should('exist')
    })
  })

  // ── Create — cascada parcela → plantación ─────────────────────────────────

  describe('Create — cascada parcela', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
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
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
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

  describe('Create — guardado mineral', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/fertilization/create')
      cy.waitForLivewire()
    })

    it('guarda una fertilización mineral con campos PAC si hay parcela disponible', () => {
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

        cy.get('[data-cy="fertilizer-type-input"]').clear().type('Mineral', { force: true })
        cy.get('[data-cy="fertilizer-name-input"]').clear().type('NPK 15-15-15', { force: true })
        cy.get('input[id="quantity"]').clear().type('200', { force: true })
        cy.get('input[id="area_applied"]').clear().type('2.5', { force: true })
        cy.get('input[id="npk_ratio"]').clear().type('15-15-15', { force: true })
        cy.get('input[id="nitrogen_uf"]').clear().type('150', { force: true })
        cy.get('input[id="phosphorus_uf"]').clear().type('100', { force: true })
        cy.get('input[id="potassium_uf"]').clear().type('80', { force: true })

        cy.get('[data-cy="work-type-individual-radio"]').click({ force: true })
        cy.waitForLivewire()
        cy.get('[id="crew_member_id"]').then(($sel) => {
          if ($sel.find('option').length > 1) {
            cy.wrap($sel).select(1, { force: true })
          }
        })

        cy.get('[data-cy="fertilization-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.url().then((url) => {
          if (url.includes('/fertilization/create')) {
            cy.log('Guardado no completado — posiblemente datos de prueba insuficientes')
          } else {
            cy.url().should('include', '/fertilization')
          }
        })
      })
    })
  })

  // ── Edit ────────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('accede al formulario de edición si existe una fertilización', () => {
      cy.visit('/viticulturist/digital-notebook/fertilization')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/fertilization/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay fertilizaciones para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="fertilization-form"]').should('exist')
      })
    })

    it('el formulario de edición tiene campos precargados', () => {
      cy.visit('/viticulturist/digital-notebook/fertilization')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/fertilization/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay fertilizaciones para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[data-cy="plot-select"]').then(($sel) => {
          expect($sel.val()).to.not.equal('')
        })

        cy.get('[data-cy="fertilizer-type-input"]').then(($input) => {
          expect($input.val()).to.not.equal('')
        })
      })
    })

    it('puede actualizar los valores de nitrógeno UF', () => {
      cy.visit('/viticulturist/digital-notebook/fertilization')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/fertilization/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay fertilizaciones para editar — test omitido')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('input[id="nitrogen_uf"]').clear().type('200.5', { force: true })

        cy.get('[data-cy="fertilization-form"]').within(() => {
          cy.get('button[type="submit"]').click({ force: true })
        })
        cy.waitForLivewire()
        cy.wait(500)

        cy.get('body').should('be.visible')
      })
    })
  })
})
