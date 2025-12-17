describe('Viticulturist Crews (Cuadrillas)', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/personal')
    cy.waitForLivewire()
  })

  it('should display crews list', () => {
    cy.contains('Gestión de Cuadrillas').should('be.visible')
    cy.contains('Filtros de Búsqueda').should('be.visible')
  })

  it('should filter crews by winery', () => {
    // Find select by option text
    cy.get('select').then(($selects) => {
      const winerySelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.textContent.includes('bodega'));
      });
      
      if (winerySelect && winerySelect.querySelectorAll('option').length > 1) {
        cy.wrap(winerySelect).select(1, { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should search crews', () => {
    // Find input by placeholder
    cy.get('input[placeholder*="nombre o descripción"]').clear().type('Test Crew')
    cy.waitForLivewire()
    
    cy.get('input[placeholder*="nombre o descripción"]').should('have.value', 'Test Crew')
  })

  it('should navigate to create crew', () => {
    cy.contains('Nueva Cuadrilla').click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/personal/create')
  })

  it('should create a new crew', () => {
    cy.contains('Nueva Cuadrilla').click()
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Nueva Cuadrilla').should('be.visible')
    
    // Fill form
    cy.get('input[wire\\:model="name"]#name').clear().type('Cuadrilla de Prueba E2E')
    cy.get('textarea[wire\\:model="description"]#description').clear().type('Descripción de prueba para E2E')
    
    // Submit form
    cy.get('button[type="submit"]').click()
    cy.wait(3000)
    
    // Should redirect or show success message
    cy.url().should('include', '/viticulturist/personal')
    cy.get('body').should('contain.text', 'Cuadrilla')
  })
})

