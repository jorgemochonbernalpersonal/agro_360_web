describe('Cuaderno Digital — Vendimias', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/digital-notebook/harvest')
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de vendimias', () => {
      cy.contains('Vendimia').should('be.visible')
    })

    it('tiene botón de nueva vendimia', () => {
      cy.get('[data-cy="create-harvest-btn"]').should('exist')
    })
  })

  // ── Estructura del formulario de creación ──────────────────────────────────

  describe('Create — estructura', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-harvest-btn"]').click()
      cy.url().should('include', '/harvest/create')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene campo de fecha de inicio de vendimia', () => {
      cy.get('[data-cy="harvest-start-date-input"]').should('exist')
    })

    it('tiene campo de peso total', () => {
      cy.get('[data-cy="total-weight-input"]').should('exist')
    })

    it('tiene campo de alcohol potencial', () => {
      cy.get('[data-cy="potential-alcohol-input"]').should('exist')
    })

    it('tiene campo de nº de documento de transporte', () => {
      cy.get('[data-cy="transport-document-input"]').should('exist')
    })

    it('tiene campo de REGA destino', () => {
      cy.get('[data-cy="destination-rega-input"]').should('exist')
    })

    it('tiene campo de matrícula vehículo', () => {
      cy.get('[data-cy="vehicle-plate-input"]').should('exist')
    })
  })

  // ── Estructura del formulario de edición ──────────────────────────────────

  describe('Edit — estructura', () => {
    it('formulario de edición tiene los campos principales', () => {
      // Navigate to an existing harvest's edit page
      cy.visit('/viticulturist/digital-notebook/harvest')
      cy.get('[data-cy="harvest-edit-btn"]').first().click()
      cy.get('[data-cy="harvest-edit-form"]').should('exist')
      cy.get('[data-cy="total-weight-input"]').should('exist')
      cy.get('[data-cy="potential-alcohol-input"]').should('exist')
      cy.get('[data-cy="transport-document-input"]').should('exist')
      cy.get('[data-cy="destination-rega-input"]').should('exist')
      cy.get('[data-cy="vehicle-plate-input"]').should('exist')
    })
  })
})
