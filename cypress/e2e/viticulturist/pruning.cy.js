describe('Cuaderno Digital — Poda', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/digital-notebook/pruning')
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de poda', () => {
      cy.contains('Poda').should('be.visible')
    })

    it('tiene botón de nueva poda', () => {
      cy.get('[data-cy="create-pruning-btn"]').should('exist')
    })
  })

  // ── Estructura del formulario de creación ──────────────────────────────────

  describe('Create — estructura', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-pruning-btn"]').click()
      cy.url().should('include', '/pruning/create')
    })

    it('tiene selector de parcela', () => {
      cy.get('[data-cy="plot-select"]').should('exist')
    })

    it('tiene selector de fecha de actividad', () => {
      cy.get('[data-cy="activity-date-input"]').should('exist')
    })

    it('tiene selector de estadio fenológico', () => {
      cy.get('[data-cy="phenological-stage-select"]').should('exist')
    })

    it('tiene selector de tipo de poda', () => {
      cy.get('[data-cy="pruning-type-select"]').should('exist')
    })

    it('tiene campo de yemas productivas', () => {
      cy.get('[data-cy="productive-buds-input"]').should('exist')
    })

    it('tiene campo de horas trabajadas', () => {
      cy.get('[data-cy="hours-worked-input"]').should('exist')
    })

    it('tiene campo de número de trabajadores', () => {
      cy.get('[data-cy="workers-count-input"]').should('exist')
    })

    it('tiene textarea de descripción', () => {
      cy.get('[data-cy="description-textarea"]').should('exist')
    })

    it('tiene selector de gestión de ramón (BCAM 6 PAC)', () => {
      cy.get('[data-cy="residue-management-select"]').should('exist')
    })

    it('tiene opciones válidas de gestión de ramón', () => {
      cy.get('[data-cy="residue-management-select"]').should('contain', 'Triturado e incorporado')
      cy.get('[data-cy="residue-management-select"]').should('contain', 'Triturado en superficie')
      cy.get('[data-cy="residue-management-select"]').should('contain', 'Retirado de la parcela')
      cy.get('[data-cy="residue-management-select"]').should('contain', 'Quemado')
    })

    it('tiene radio buttons de tipo de trabajo', () => {
      cy.get('[data-cy="work-type-crew"]').should('exist')
      cy.get('[data-cy="work-type-individual"]').should('exist')
    })
  })

  // ── Validaciones ──────────────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-pruning-btn"]').click()
    })

    it('falla si no se selecciona tipo de poda', () => {
      cy.get('[data-cy="description-textarea"]').type('Poda en Guyot simple en toda la parcela.')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('button[type="submit"]').click()
      cy.contains('pruning type').should('be.visible')
    })

    it('falla si descripción está vacía', () => {
      cy.get('[data-cy="pruning-type-select"]').select('guyot')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('button[type="submit"]').click()
      cy.contains('description').should('be.visible')
    })
  })

  // ── Creación con campo BCAM 6 ─────────────────────────────────────────────

  describe('Create — BCAM 6 PAC', () => {
    beforeEach(() => {
      cy.get('[data-cy="create-pruning-btn"]').click()
    })

    it('guarda poda con gestión de ramón especificada', () => {
      cy.get('[data-cy="plot-select"]').select(1)
      cy.get('[data-cy="pruning-type-select"]').select('doble_guyot')
      cy.get('[data-cy="productive-buds-input"]').type('45000')
      cy.get('[data-cy="residue-management-select"]').select('triturado_incorporado')
      cy.get('[data-cy="description-textarea"]').type('Poda Doble Guyot con triturado del ramón e incorporación al suelo.')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('[data-cy="crew-member-select"]').select(1)
      cy.get('button[type="submit"]').click()
      cy.url().should('include', '/pruning')
    })

    it('guarda poda sin gestión de ramón (campo opcional)', () => {
      cy.get('[data-cy="plot-select"]').select(1)
      cy.get('[data-cy="pruning-type-select"]').select('vaso')
      cy.get('[data-cy="description-textarea"]').type('Poda en vaso tradicional sin especificar gestión de ramón.')
      cy.get('[data-cy="work-type-individual"]').check()
      cy.get('[data-cy="crew-member-select"]').select(1)
      cy.get('button[type="submit"]').click()
      cy.url().should('include', '/pruning')
    })
  })

  // ── Edición ──────────────────────────────────────────────────────────────

  describe('Edit — estructura', () => {
    it('tiene selector de gestión de ramón en edición', () => {
      cy.get('[data-cy="edit-pruning-btn"]').first().click()
      cy.get('[data-cy="residue-management-select"]').should('exist')
    })

    it('precarga el valor de gestión de ramón existente', () => {
      cy.get('[data-cy="edit-pruning-btn"]').first().click()
      cy.get('[data-cy="residue-management-select"]').invoke('val').should('not.be.empty').or('eq', '')
    })
  })
})
