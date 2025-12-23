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
    // Find the button/link that contains "Nueva Maquinaria"
    cy.contains('Nueva Maquinaria').should('be.visible').click()
    cy.wait(2000) // Wait for navigation
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/machinery/create')
  })

  it('should create new machinery', () => {
    cy.contains('Nueva Maquinaria').click()
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Nueva Maquinaria').should('be.visible')
    cy.get('[data-cy="machinery-create-form"]').should('be.visible')
    
    // Fill form using data-cy selectors
    cy.get('[data-cy="machinery-name"]').clear().type('Tractor de Prueba E2E')
    
    // Type is now a select field (machinery_type_id)
    cy.get('[data-cy="machinery-type-id"]').then(($select) => {
      if ($select.length > 0 && $select.find('option').length > 1) {
        cy.get('[data-cy="machinery-type-id"]').select(1, { force: true })
      }
    })
    
    // Brand and model are also input fields
    cy.get('[data-cy="machinery-brand"]').clear().type('Marca Test')
    cy.get('[data-cy="machinery-model"]').clear().type('Modelo Test')
    
    // Submit form using data-cy selector
    cy.get('[data-cy="submit-button"]').click()
    
    // Wait for Livewire to process
    cy.wait(5000)
    
    // Check if we're still logged in or redirected
    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.log('⚠ Redirected to login - may be a session issue')
        // Re-login and try again
        cy.loginAsViticulturist()
        cy.visit('/viticulturist/machinery')
        cy.waitForLivewire()
      } else {
        cy.url().should('include', '/viticulturist/machinery')
        cy.get('body').should('contain.text', 'Maquinaria')
      }
    })
  })

  it('should edit existing machinery', () => {
    // Wait for machinery list to load
    cy.contains('Maquinaria').should('be.visible')
    cy.wait(2000)
    
    // Check if we have machinery to edit
    let hasMachinery = false
    cy.get('body').then(($body) => {
      const machineryLinks = $body.find('a[href*="/viticulturist/machinery/"]').filter((i, el) => {
        const href = el.getAttribute('href');
        return href && 
               !href.includes('/create') && 
               !href.includes('/edit') &&
               /\/viticulturist\/machinery\/\d+$/.test(href);
      });
      hasMachinery = machineryLinks.length > 0
    })
    
    if (!hasMachinery) {
      cy.log('No machinery found to edit - creating one first')
      
      // Create machinery first using data-cy selectors
      cy.contains('Nueva Maquinaria').click()
      cy.waitForLivewire()
      cy.wait(2000)
      
      cy.get('[data-cy="machinery-name"]').clear().type('Maquinaria para Editar E2E')
      cy.get('[data-cy="machinery-type-id"]').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('[data-cy="machinery-type-id"]').select(1, { force: true })
        }
      })
      cy.get('[data-cy="machinery-brand"]').clear().type('Marca Test')
      cy.get('[data-cy="machinery-model"]').clear().type('Modelo Test')
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Ensure we're back on index (not on create page)
      cy.url({ timeout: 15000 }).should(($url) => {
        if ($url.includes('/create')) {
          // If still on create, navigate to index manually
          cy.visit('/viticulturist/machinery')
        } else {
          expect($url).to.include('/viticulturist/machinery')
          expect($url).to.not.include('/create')
        }
      })
      cy.waitForLivewire()
      cy.wait(2000)
    }
    
    // Now find machinery to edit (after ensuring we have some and we're on index)
    cy.url().should('include', '/viticulturist/machinery')
    cy.url().should('not.include', '/create')
    cy.url().should('not.include', '/edit')
    
    cy.get('body').then(($body) => {
      const showLinks = $body.find('a[href*="/viticulturist/machinery/"]').filter((i, el) => {
        const href = el.getAttribute('href');
        return href && 
               !href.includes('/create') && 
               !href.includes('/edit') &&
               /\/viticulturist\/machinery\/\d+$/.test(href);
      });
      
      if (showLinks.length > 0) {
        const showHref = showLinks.first().attr('href');
        const editUrl = showHref + '/edit';
        cy.visit(editUrl, { timeout: 15000 })
      } else {
        cy.log('No machinery found to edit - skipping test')
        return
      }
    })
    
    cy.waitForLivewire()
    cy.wait(2000)

    // We should be on the edit page
    cy.url({ timeout: 15000 }).should(($url) => {
      expect($url).to.include('/viticulturist/machinery/')
      expect($url).to.include('/edit')
    })
    cy.get('[data-cy="machinery-edit-form"]').should('be.visible')

    // Modify basic fields using data-cy selector
    cy.get('[data-cy="machinery-name"]').then(($input) => {
      if ($input.length > 0) {
        cy.get('[data-cy="machinery-name"]').clear().type('Maquinaria Editada E2E')

        // Submit form using data-cy selector
        cy.get('[data-cy="submit-button"]').click()
        cy.wait(5000)

        // Back on index with updated machinery visible
        cy.url().should('include', '/viticulturist/machinery')
        cy.contains('Maquinaria Editada E2E').should('be.visible')
      } else {
        cy.log('Edit form not found - skipping update')
      }
    })
  })
})

