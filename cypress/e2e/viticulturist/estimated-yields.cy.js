describe('Cuaderno Digital — Rendimientos Estimados', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/digital-notebook/estimated-yields')
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de rendimientos estimados', () => {
      cy.contains('Rendimientos Estimados').should('be.visible')
    })

    it('tiene botón de nueva estimación', () => {
      cy.get('a[href*="estimated-yields/create"]').should('exist')
    })
  })

  // ── Estructura del formulario de creación ──────────────────────────────────

  describe('Create — estructura', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/estimated-yields/create')
    })

    it('tiene selector de campaña', () => {
      cy.get('[data-cy="campaign-select"]').should('exist')
    })

    it('tiene selector de plantación', () => {
      cy.get('[data-cy="planting-select"]').should('exist')
    })

    it('tiene selector de ronda de estimación', () => {
      cy.get('[data-cy="estimation-round-select"]').should('exist')
    })

    it('selector de ronda tiene las cuatro opciones PAC', () => {
      cy.get('[data-cy="estimation-round-select"]').should('contain', 'Pre-envero')
      cy.get('[data-cy="estimation-round-select"]').should('contain', 'Envero')
      cy.get('[data-cy="estimation-round-select"]').should('contain', 'Pre-vendimia')
      cy.get('[data-cy="estimation-round-select"]').should('contain', 'Revisión final')
    })

    it('tiene selector de método de estimación', () => {
      cy.get('[data-cy="estimation-method-select"]').should('exist')
    })

    it('método de estimación tiene las opciones correctas', () => {
      cy.get('[data-cy="estimation-method-select"]').should('contain', 'Visual')
      cy.get('[data-cy="estimation-method-select"]').should('contain', 'Muestreo de campo')
      cy.get('[data-cy="estimation-method-select"]').should('contain', 'Histórico')
      cy.get('[data-cy="estimation-method-select"]').should('contain', 'Satelital')
    })

    it('tiene campo de rendimiento por hectárea', () => {
      cy.get('[data-cy="yield-per-hectare-input"]').should('exist')
    })

    it('tiene campo de alcohol potencial', () => {
      cy.get('[data-cy="potential-alcohol-input"]').should('exist')
    })

    it('tiene formulario de creación', () => {
      cy.get('[data-cy="create-estimated-yield-form"]').should('exist')
    })
  })

  // ── Estructura del formulario de edición ──────────────────────────────────

  describe('Edit — estructura', () => {
    it('formulario de edición tiene los campos principales', () => {
      cy.visit('/viticulturist/digital-notebook/estimated-yields')
      cy.get('a[href*="estimated-yields"][href*="/edit"]').first().click()
      cy.get('[data-cy="edit-estimated-yield-form"]').should('exist')
      cy.get('[data-cy="yield-per-hectare-input"]').should('exist')
      cy.get('[data-cy="potential-alcohol-input"]').should('exist')
      cy.get('[data-cy="estimation-round-select"]').should('exist')
    })
  })
})
