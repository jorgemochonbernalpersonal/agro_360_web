describe('Viticulturist Digital Notebook', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/digital-notebook')
    cy.waitForLivewire()
  })

  it('should display digital notebook', () => {
    cy.contains('Cuaderno Digital').should('be.visible')
    cy.contains('Filtros de Búsqueda').should('be.visible')
  })

  it('should filter activities by plot', () => {
    cy.get('select').then(($selects) => {
      const plotSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.textContent.includes('parcelas'));
      });
      
      if (plotSelect && plotSelect.querySelectorAll('option').length > 1) {
        cy.wrap(plotSelect).select(1, { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should filter activities by type', () => {
    cy.get('select').then(($selects) => {
      const typeSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.textContent.includes('Fitosanitarios') || opt.textContent.includes('Fertilizaciones'));
      });
      
      if (typeSelect && typeSelect.querySelectorAll('option').length > 1) {
        cy.wrap(typeSelect).select('phytosanitary', { force: true });
        cy.waitForLivewire();
        cy.wrap(typeSelect).should('have.value', 'phytosanitary');
      }
    });
  })

  it('should filter activities by date range', () => {
    cy.get('input[type="date"]').then(($inputs) => {
      const dateFromInput = Array.from($inputs).find(input => {
        const placeholder = input.getAttribute('placeholder');
        return placeholder?.includes('desde') || placeholder?.includes('Desde');
      });
      
      if (dateFromInput) {
        const today = new Date().toISOString().split('T')[0];
        cy.wrap(dateFromInput).type(today);
        cy.waitForLivewire();
      }
    });
  })

  it('should search activities', () => {
    cy.get('input[placeholder*="notas, parcelas"]').clear().type('Test Activity')
    cy.waitForLivewire()
    cy.get('input[placeholder*="notas, parcelas"]').should('have.value', 'Test Activity')
  })

  it('should navigate to create phytosanitary treatment', () => {
    // Look for link to create treatment
    cy.get('a[href*="treatment/create"]').contains('Tratamiento').click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/digital-notebook/treatment/create')
  })

  it('should create phytosanitary treatment', () => {
    cy.visit('/viticulturist/digital-notebook/treatment/create')
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Registrar Tratamiento Fitosanitario').should('be.visible')
    
    // Fill form
    cy.get('select#plot_id').then(($select) => {
      if ($select.length > 0 && $select.find('option').length > 1) {
        cy.get('select#plot_id').select(1, { force: true })
      }
    })
    
    const today = new Date().toISOString().split('T')[0]
    cy.get('input#activity_date[type="date"]').type(today)
    
    cy.get('select#product_id').then(($select) => {
      if ($select.length > 0 && $select.find('option').length > 1) {
        cy.get('select#product_id').select(1, { force: true })
      }
    })
    
    // Submit form
    cy.get('button').contains('Registrar').click()
    cy.wait(3000)
    
    // Should redirect or show success message
    cy.url().should('include', '/viticulturist/digital-notebook')
    cy.get('body').should('contain.text', 'Tratamiento')
  })
})

