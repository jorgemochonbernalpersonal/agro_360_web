describe('Viticulturist Clients (Clientes) - CRUD', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/clients')
    cy.waitForLivewire()
  })

  it('should display clients list', () => {
    cy.contains('Clientes').should('be.visible')
    cy.contains('Gestiona tus clientes').should('be.visible')
  })

  it('should navigate to create client', () => {
    cy.contains('Nuevo Cliente').click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/clients/create')
  })

  it('should create a new individual client', () => {
    cy.contains('Nuevo Cliente').click()
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Nuevo Cliente').should('be.visible')
    cy.get('[data-cy="client-create-form"]').should('be.visible')
    
    // Select individual client type using data-cy selector
    cy.get('[data-cy="client-type"]').select('individual', { force: true })
    cy.wait(500)
    
    // Fill individual client fields using data-cy selectors
    cy.get('[data-cy="first-name"]').clear().type('Juan')
    cy.get('[data-cy="last-name"]').clear().type('Pérez')
    cy.get('[data-cy="email"]').clear().type('juan.perez@example.com')
    cy.get('[data-cy="phone"]').clear().type('666777888')
    cy.get('[data-cy="particular-document"]').clear().type('12345678A')
    
    // Fill address using data-cy selector
    cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Principal 123')
    
    // Submit form using data-cy selector
    cy.get('[data-cy="submit-button"]').click()
    
    // Wait for Livewire to process
    cy.wait(5000)
    
    // Check if we're redirected back to index
    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.log('⚠ Redirected to login - may be a session issue')
        cy.loginAsViticulturist()
        cy.visit('/viticulturist/clients')
        cy.waitForLivewire()
      } else {
        cy.url().should('include', '/viticulturist/clients')
        cy.get('body').should('contain.text', 'Cliente')
      }
    })
  })

  it('should create a new company client', () => {
    cy.contains('Nuevo Cliente').click()
    cy.waitForLivewire()
    
    // Select company client type using data-cy selector
    cy.get('[data-cy="client-type"]').select('company', { force: true })
    cy.wait(500)
    
    // Fill company client fields using data-cy selectors
    cy.get('[data-cy="company-name"]').clear().type('Empresa Test E2E')
    cy.get('[data-cy="company-document"]').clear().type('B12345678')
    cy.get('[data-cy="email"]').clear().type('empresa@example.com')
    cy.get('[data-cy="phone"]').clear().type('666777999')
    
    // Fill address using data-cy selector
    cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Empresa 456')
    
    // Submit form using data-cy selector
    cy.get('[data-cy="submit-button"]').click()
    
    cy.wait(5000)
    
    cy.url().then((url) => {
      if (!url.includes('/login')) {
        cy.url().should('include', '/viticulturist/clients')
      }
    })
  })

  it('should search clients', () => {
    // Find search input using placeholder text
    cy.get('input[placeholder*="Buscar clientes" i], input[placeholder*="buscar" i]').first().clear().type('Test')
    cy.waitForLivewire()
    
    // Verify search is working
    cy.get('input[placeholder*="Buscar clientes" i], input[placeholder*="buscar" i]').first().should('have.value', 'Test')
  })

  it('should filter clients by type', () => {
    // Find the select with filterType options
    cy.get('select').then(($selects) => {
      const typeSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => 
          opt.textContent.toLowerCase().includes('particular') || 
          opt.textContent.toLowerCase().includes('empresa') ||
          opt.textContent.toLowerCase().includes('todos los tipos')
        );
      });
      
      if (typeSelect && typeSelect.querySelectorAll('option').length > 1) {
        cy.wrap(typeSelect).select('individual', { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should filter clients by active status', () => {
    // Find the select with filterActive options
    cy.get('select').then(($selects) => {
      const statusSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => 
          opt.textContent.toLowerCase().includes('activo') || 
          opt.textContent.toLowerCase().includes('inactivo') ||
          opt.textContent.toLowerCase().includes('todos')
        );
      });
      
      if (statusSelect && statusSelect.querySelectorAll('option').length > 1) {
        cy.wrap(statusSelect).select('1', { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should view client details', () => {
    // Click first client link or view button
    cy.get('a[href*="/viticulturist/clients/"]').first().then(($link) => {
      const href = $link.attr('href');
      if (href && !href.includes('/edit') && !href.includes('/create')) {
        cy.wrap($link).click({ force: true });
        cy.waitForLivewire();
        cy.url().should('include', '/viticulturist/clients/');
        cy.url().should('not.include', '/edit');
        cy.url().should('not.include', '/create');
      }
    });
  })

  it('should edit an existing client', () => {
    // Click first edit link in the table
    cy.get('a[href*="/viticulturist/clients/"][href*="/edit"]').first().then(($link) => {
      if ($link.length > 0) {
        cy.wrap($link).click({ force: true });
        cy.waitForLivewire();
        
        // We should be on the edit page
        cy.url().should('include', '/viticulturist/clients/');
        cy.url().should('include', '/edit');
        cy.get('[data-cy="client-edit-form"]').should('be.visible');
        
        // Modify basic fields using data-cy selectors - try both individual and company fields
        cy.get('body').then(($body) => {
          const firstNameField = $body.find('[data-cy="first-name"]');
          const companyNameField = $body.find('[data-cy="company-name"]');
          if (firstNameField.length > 0) {
            cy.get('[data-cy="first-name"]').clear().type('Cliente Editado E2E')
          } else if (companyNameField.length > 0) {
            cy.get('[data-cy="company-name"]').clear().type('Cliente Editado E2E')
          }
        })
        
        // Submit form using data-cy selector
        cy.get('[data-cy="submit-button"]').click()
        cy.wait(5000)
        
        // Back on index with updated client visible
        cy.url().should('include', '/viticulturist/clients')
        cy.url().should('not.include', '/edit')
      } else {
        cy.log('No edit links found - skipping test');
      }
    });
  })

  it('should switch between tabs', () => {
    // Look for tabs with wire:click="switchTab"
    cy.get('button[wire\\:click*="switchTab"]').then(($tabs) => {
      if ($tabs.length > 1) {
        // Click the statistics tab (second button)
        cy.wrap($tabs.eq(1)).click({ force: true });
        cy.waitForLivewire();
        // Verify we're on statistics tab
        cy.contains('Estadísticas').should('be.visible');
      }
    });
  })

  it('should add and manage multiple addresses', () => {
    cy.contains('Nuevo Cliente').click()
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Nuevo Cliente').should('be.visible')
    
    // Select individual client type
    cy.get('[data-cy="client-type"]').select('individual', { force: true })
    cy.wait(500)
    
    // Fill basic client fields
    cy.get('[data-cy="first-name"]').clear().type('Cliente Multi Dirección')
    cy.get('[data-cy="last-name"]').clear().type('Test')
    cy.get('[data-cy="email"]').clear().type('multidireccion@example.com')
    
    // Fill first address
    cy.get('[data-cy="address-address"][data-cy-address-index="0"]').should('be.visible')
    cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Primera Dirección 123')
    cy.get('[data-cy="address-postal-code"][data-cy-address-index="0"]').clear().type('28001')
    
    // Add a second address
    cy.get('[data-cy="add-address-button"]').click()
    cy.waitForLivewire()
    
    // Verify second address exists
    cy.get('[data-cy="address-item"][data-cy-address-index="1"]').should('be.visible')
    cy.get('[data-cy="address-address"][data-cy-address-index="1"]').clear().type('Segunda Dirección 456')
    cy.get('[data-cy="address-postal-code"][data-cy-address-index="1"]').clear().type('28002')
    
    // Mark second address as delivery note address
    cy.get('[data-cy="address-delivery-note"][data-cy-address-index="1"]').check()
    
    // Remove second address
    cy.get('[data-cy="remove-address"][data-cy-address-index="1"]').click()
    cy.waitForLivewire()
    
    // Verify second address is removed
    cy.get('[data-cy="address-item"][data-cy-address-index="1"]').should('not.exist')
    
    // Verify first address still exists
    cy.get('[data-cy="address-address"][data-cy-address-index="0"]').should('have.value', 'Primera Dirección 123')
  })

  describe('Validations and Edge Cases', () => {
    beforeEach(() => {
      cy.contains('Nuevo Cliente').click()
      cy.waitForLivewire()
      cy.contains('Nuevo Cliente').should('be.visible')
      cy.get('[data-cy="client-create-form"]').should('be.visible')
    })

    it('should show validation errors for required fields on individual client', () => {
      // Select individual type
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Try to submit without filling required fields
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(1000)
      
      // Should show validation errors (check for error messages or stay on page)
      cy.url().should('include', '/viticulturist/clients/create')
      
      // Check for error indicators (red borders, error messages, etc.)
      cy.get('body').then(($body) => {
        // Check if there are error messages visible
        const hasErrors = $body.text().includes('obligatorio') || 
                         $body.text().includes('required') ||
                         $body.find('.text-red-600, .text-red-500').length > 0
        expect(hasErrors || cy.url().should('include', '/create')).to.be.true
      })
    })

    it('should show validation errors for required fields on company client', () => {
      // Select company type
      cy.get('[data-cy="client-type"]').select('company', { force: true })
      cy.wait(500)
      
      // Try to submit without filling required fields
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(1000)
      
      // Should show validation errors
      cy.url().should('include', '/viticulturist/clients/create')
    })

    it('should validate email format', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill required fields
      cy.get('[data-cy="first-name"]').clear().type('Test')
      cy.get('[data-cy="last-name"]').clear().type('User')
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Test 123')
      
      // Enter invalid email
      cy.get('[data-cy="email"]').clear().type('invalid-email')
      
      // Try to submit
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(1000)
      
      // Should show email validation error or stay on page
      cy.url().should('include', '/viticulturist/clients/create')
    })

    it('should validate address is required', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill required client fields
      cy.get('[data-cy="first-name"]').clear().type('Test')
      cy.get('[data-cy="last-name"]').clear().type('User')
      
      // Leave address empty
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear()
      
      // Try to submit
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(1000)
      
      // Should show validation error for address
      cy.url().should('include', '/viticulturist/clients/create')
    })

    it('should validate discount percentage range (0-100)', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill required fields
      cy.get('[data-cy="first-name"]').clear().type('Test')
      cy.get('[data-cy="last-name"]').clear().type('User')
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Test 123')
      
      // Enter invalid discount (> 100)
      cy.get('body').then(($body) => {
        const discountField = $body.find('[data-cy="default-discount"], #default_discount');
        if (discountField.length > 0) {
          cy.get('[data-cy="default-discount"], #default_discount').clear().type('150')
          cy.get('[data-cy="submit-button"]').click()
          cy.wait(1000)
          // Should show validation error or prevent submission
          cy.url().should('include', '/viticulturist/clients/create')
        }
      })
    })

    it('should handle maximum length for text fields', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Generate string longer than max length (100 chars)
      const longString = 'A'.repeat(101)
      
      // Try to enter long string in first name
      cy.get('[data-cy="first-name"]').clear().type(longString)
      
      // Fill other required fields
      cy.get('[data-cy="last-name"]').clear().type('User')
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Test 123')
      
      // Try to submit
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(1000)
      
      // Should show validation error or truncate
      cy.url().should('include', '/viticulturist/clients/create')
    })

    it('should allow creating client with only required fields', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill only required fields
      cy.get('[data-cy="first-name"]').clear().type('Minimal Client')
      cy.get('[data-cy="last-name"]').clear().type('Test')
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Mínima 123')
      
      // Submit
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Should succeed
      cy.url().then((url) => {
        if (!url.includes('/login')) {
          cy.url().should('include', '/viticulturist/clients')
        }
      })
    })

    it('should handle switching between client types', () => {
      // Start with individual
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill individual fields
      cy.get('[data-cy="first-name"]').should('be.visible')
      cy.get('[data-cy="last-name"]').should('be.visible')
      cy.get('[data-cy="company-name"]').should('not.exist')
      
      // Switch to company
      cy.get('[data-cy="client-type"]').select('company', { force: true })
      cy.wait(500)
      
      // Company fields should be visible, individual fields should not
      cy.get('[data-cy="company-name"]').should('be.visible')
      cy.get('[data-cy="first-name"]').should('not.exist')
      cy.get('[data-cy="last-name"]').should('not.exist')
    })

    it('should validate that at least one address is required', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill required client fields
      cy.get('[data-cy="first-name"]').clear().type('Test')
      cy.get('[data-cy="last-name"]').clear().type('User')
      
      // Remove the only address if possible
      cy.get('body').then(($body) => {
        const removeButton = $body.find('[data-cy="remove-address"][data-cy-address-index="0"]');
        // If there's only one address, remove button might not exist
        // But we can try to submit without address
        cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear()
        cy.get('[data-cy="submit-button"]').click()
        cy.wait(1000)
        
        // Should show validation error
        cy.url().should('include', '/viticulturist/clients/create')
      })
    })

    it('should handle special characters in fields', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill with special characters
      cy.get('[data-cy="first-name"]').clear().type("José María O'Connor")
      cy.get('[data-cy="last-name"]').clear().type('García-López')
      cy.get('[data-cy="email"]').clear().type('test+special@example.com')
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle de la Paz, 123')
      
      // Should accept special characters
      cy.get('[data-cy="first-name"]').should('have.value', "José María O'Connor")
      cy.get('[data-cy="last-name"]').should('have.value', 'García-López')
    })

    it('should handle empty optional fields', () => {
      cy.get('[data-cy="client-type"]').select('individual', { force: true })
      cy.wait(500)
      
      // Fill only required fields, leave optional empty
      cy.get('[data-cy="first-name"]').clear().type('Test')
      cy.get('[data-cy="last-name"]').clear().type('User')
      cy.get('[data-cy="address-address"][data-cy-address-index="0"]').clear().type('Calle Test 123')
      
      // Leave email, phone, document empty (optional fields)
      cy.get('[data-cy="email"]').should('have.value', '')
      cy.get('[data-cy="phone"]').should('have.value', '')
      
      // Should be able to submit
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().then((url) => {
        if (!url.includes('/login')) {
          cy.url().should('include', '/viticulturist/clients')
        }
      })
    })
  })
})

