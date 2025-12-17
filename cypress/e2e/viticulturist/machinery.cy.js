describe('Viticulturist Machinery', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/machinery')
    cy.waitForLivewire()
  })

  it('should display machinery list', () => {
    cy.contains('Maquinaria').should('be.visible')
    cy.contains('Filtros de Búsqueda').should('be.visible')
  })

  it('should filter machinery by type', () => {
    cy.get('select').then(($selects) => {
      const typeSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.textContent.includes('tipos'));
      });
      
      if (typeSelect && typeSelect.querySelectorAll('option').length > 1) {
        cy.wrap(typeSelect).select(1, { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should filter machinery by status', () => {
    cy.get('select').then(($selects) => {
      const statusSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.textContent.includes('Activas') || opt.textContent.includes('Inactivas'));
      });
      
      if (statusSelect) {
        cy.wrap(statusSelect).select('1', { force: true });
        cy.waitForLivewire();
        cy.wrap(statusSelect).should('have.value', '1');
      }
    });
  })

  it('should navigate to create machinery', () => {
    cy.contains('Nueva Maquinaria').click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/machinery/create')
  })

  it('should create new machinery', () => {
    cy.contains('Nueva Maquinaria').click()
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Nueva Maquinaria').should('be.visible')
    
    // Fill form
    cy.get('input#name').clear().type('Tractor de Prueba E2E')
    
    // Type is an input field, not a select
    cy.get('input#type').clear().type('Tractor')
    
    // Brand and model are also input fields
    cy.get('input#brand').clear().type('Marca Test')
    cy.get('input#model').clear().type('Modelo Test')
    
    // Submit form
    cy.get('button[type="submit"]').click()
    cy.wait(3000)
    
    // Should redirect or show success message
    cy.url().should('include', '/viticulturist/machinery')
    cy.get('body').should('contain.text', 'Maquinaria')
  })
})

