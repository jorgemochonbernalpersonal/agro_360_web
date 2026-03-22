describe('Gestión de Plagas y Enfermedades', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/pest-management')
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de gestión de plagas', () => {
      cy.contains('Gestión de Plagas').should('be.visible')
    })

    it('tiene buscador', () => {
      cy.get('[data-cy="pest-search-input"]').should('exist')
    })

    it('tiene botón de filtros', () => {
      cy.get('[data-cy="pest-filter-btn"]').should('exist')
    })

    it('muestra tarjetas de plagas', () => {
      cy.get('[data-cy="pest-card"]').should('have.length.greaterThan', 0)
    })
  })

  // ── Búsqueda ───────────────────────────────────────────────────────────────

  describe('Búsqueda', () => {
    it('filtra por nombre', () => {
      cy.get('[data-cy="pest-search-input"]').type('Mildiu')
      cy.get('[data-cy="pest-card"]').should('have.length', 1)
      cy.contains('Mildiu').should('be.visible')
    })

    it('filtra por nombre científico', () => {
      cy.get('[data-cy="pest-search-input"]').type('Plasmopara')
      cy.contains('Mildiu').should('be.visible')
    })
  })

  // ── Filtros ────────────────────────────────────────────────────────────────

  describe('Filtros', () => {
    beforeEach(() => {
      cy.get('[data-cy="pest-filter-btn"]').click()
    })

    it('abre modal de filtros', () => {
      cy.get('[data-cy="pest-type-filter"]').should('be.visible')
    })

    it('tiene selector de tipo con opciones correctas', () => {
      cy.get('[data-cy="pest-type-filter"]').should('contain', 'Todos los tipos')
      cy.get('[data-cy="pest-type-filter"]').should('contain', 'Solo Plagas')
      cy.get('[data-cy="pest-type-filter"]').should('contain', 'Solo Enfermedades')
    })

    it('tiene checkbox de solo en riesgo', () => {
      cy.get('[data-cy="pest-risk-filter"]').should('exist')
    })

    it('filtra solo plagas', () => {
      cy.get('[data-cy="pest-type-filter"]').select('pest')
      cy.contains('Aplicar').click()
      cy.get('[data-cy="pest-card"]').each(($card) => {
        cy.wrap($card).contains('Plaga')
      })
    })

    it('filtra solo enfermedades', () => {
      cy.get('[data-cy="pest-type-filter"]').select('disease')
      cy.contains('Aplicar').click()
      cy.get('[data-cy="pest-card"]').each(($card) => {
        cy.wrap($card).contains('Enfermedad')
      })
    })
  })

  // ── Show ───────────────────────────────────────────────────────────────────

  describe('Show — estructura', () => {
    beforeEach(() => {
      cy.get('[data-cy="pest-card"]').first().contains('Ver detalles').click()
    })

    it('muestra el nombre de la plaga', () => {
      cy.get('h1').should('be.visible')
    })

    it('tiene sección de prevención', () => {
      cy.get('[data-cy="prevention-methods-section"]').should('exist')
    })
  })

  // ── Control Methods IPM (PAC) ──────────────────────────────────────────────

  describe('Show — Métodos de Control IPM (PAC)', () => {
    it('muestra la sección de métodos de control cuando existen', () => {
      // Navega a una plaga que tiene control_methods (seeder data)
      cy.visit('/viticulturist/pest-management')
      cy.contains('Mildiu').click()
      cy.get('[data-cy="control-methods-section"]').should('be.visible')
    })

    it('muestra métodos de control con etiquetas legibles', () => {
      cy.visit('/viticulturist/pest-management')
      cy.contains('Mildiu').click()
      cy.get('[data-cy="control-methods-section"]').within(() => {
        cy.contains('Control cultural').should('be.visible')
        cy.contains('Control químico').should('be.visible')
      })
    })

    it('muestra control biológico para plagas con MIP', () => {
      cy.visit('/viticulturist/pest-management')
      cy.contains('Polilla del Racimo').click()
      cy.get('[data-cy="control-method-biologico"]').should('be.visible')
      cy.get('[data-cy="control-method-cultural"]').should('be.visible')
      cy.get('[data-cy="control-method-quimico"]').should('be.visible')
    })
  })
})
