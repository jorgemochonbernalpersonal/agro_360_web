describe('Cuaderno Digital — Tratamientos Post-Vendimia', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/digital-notebook/post-harvest')
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de tratamientos post-vendimia', () => {
      cy.contains('Post-Vendimia').should('be.visible')
    })

    it('tiene botón de nuevo tratamiento', () => {
      cy.get('[data-cy="create-post-harvest-btn"]').should('exist')
    })
  })

  // ── Estructura del formulario de creación ──────────────────────────────────

  describe('Create — estructura', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-post-harvest-btn"]').click()
      cy.url().should('include', '/post-harvest/create')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene campo de fecha de tratamiento', () => {
      cy.get('[data-cy="activity-date-input"]').should('exist')
    })

    it('tiene selector de estadio fenológico', () => {
      cy.get('[data-cy="phenological-stage-select"]').should('exist')
    })

    it('tiene selector de tipo de aplicación', () => {
      cy.get('[data-cy="application-type-select"]').should('exist')
    })

    it('tipo de aplicación tiene las opciones correctas', () => {
      cy.get('[data-cy="application-type-select"]').should('contain', 'Tratamiento con cobre')
      cy.get('[data-cy="application-type-select"]').should('contain', 'Tratamiento con azufre')
      cy.get('[data-cy="application-type-select"]').should('contain', 'Sellado de heridas')
      cy.get('[data-cy="application-type-select"]').should('contain', 'Aplicación foliar')
    })

    it('tiene campo de superficie tratada', () => {
      cy.get('[data-cy="treated-area-input"]').should('exist')
    })

    it('tiene campo de volumen de caldo', () => {
      cy.get('[data-cy="water-volume-input"]').should('exist')
    })

    it('tiene campo de dosis por hectárea', () => {
      cy.get('[data-cy="dose-per-hectare-input"]').should('exist')
    })

    it('tiene selector de unidad de dosis', () => {
      cy.get('[data-cy="dose-unit-select"]').should('exist')
    })

    it('tiene campo de plazo de reentrada (PAC)', () => {
      cy.get('[data-cy="reentry-interval-input"]').should('exist')
    })

    it('tiene radio buttons de tipo de trabajo', () => {
      cy.get('[data-cy="work-type-crew"]').should('exist')
      cy.get('[data-cy="work-type-individual"]').should('exist')
    })
  })

  // ── Validaciones ──────────────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-post-harvest-btn"]').click()
    })

    it('falla si no se selecciona tipo de aplicación', () => {
      cy.get('[data-cy="treated-area-input"]').type('2.5')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('button[type="submit"]').click()
      cy.contains('application type').should('be.visible')
    })

    it('falla si superficie tratada está vacía', () => {
      cy.get('[data-cy="application-type-select"]').select('copper_treatment')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('button[type="submit"]').click()
      cy.contains('treated area').should('be.visible')
    })
  })

  // ── Creación con plazo de reentrada (PAC) ─────────────────────────────────

  describe('Create — plazo de reentrada PAC', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-post-harvest-btn"]').click()
    })

    it('guarda tratamiento con plazo de reentrada', () => {
      cy.get('[data-cy="plot-select"]').select(1)
      cy.get('[data-cy="application-type-select"]').select('copper_treatment')
      cy.get('[data-cy="treated-area-input"]').type('2.5')
      cy.get('[data-cy="dose-per-hectare-input"]').type('3.0')
      cy.get('[data-cy="water-volume-input"]').type('300')
      cy.get('[data-cy="reentry-interval-input"]').type('24')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('[data-cy="crew-member-select"]').select(1)
      cy.get('button[type="submit"]').click()
      cy.url().should('include', '/post-harvest')
    })

    it('guarda tratamiento sin plazo de reentrada (opcional)', () => {
      cy.get('[data-cy="plot-select"]').select(1)
      cy.get('[data-cy="application-type-select"]').select('wound_sealing')
      cy.get('[data-cy="treated-area-input"]').type('1.0')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('[data-cy="crew-member-select"]').select(1)
      cy.get('button[type="submit"]').click()
      cy.url().should('include', '/post-harvest')
    })

    it('guarda con plazo de reentrada 0 (sin restricción)', () => {
      cy.get('[data-cy="plot-select"]').select(1)
      cy.get('[data-cy="application-type-select"]').select('wound_sealing')
      cy.get('[data-cy="treated-area-input"]').type('1.5')
      cy.get('[data-cy="reentry-interval-input"]').type('0')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('[data-cy="crew-member-select"]').select(1)
      cy.get('button[type="submit"]').click()
      cy.url().should('include', '/post-harvest')
    })
  })

  // ── Edición ──────────────────────────────────────────────────────────────

  describe('Edit — estructura', () => {
    it('tiene campo de plazo de reentrada en edición', () => {
      cy.get('[data-cy="edit-post-harvest-btn"]').first().click()
      cy.get('[data-cy="reentry-interval-input"]').should('exist')
    })

    it('precarga el valor del plazo de reentrada', () => {
      cy.get('[data-cy="edit-post-harvest-btn"]').first().click()
      cy.get('[data-cy="reentry-interval-input"]').should('exist')
    })
  })
})
