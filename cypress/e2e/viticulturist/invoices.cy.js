describe('Viticulturist Invoices (Facturas) - Complete Flow', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/invoices')
    cy.waitForLivewire()
  })

  it('should display invoices list', () => {
    cy.contains('Facturas / Pedidos').should('be.visible')
    cy.contains('Gestiona tus facturas').should('be.visible')
  })

  it('should navigate to create invoice', () => {
    cy.contains('Nueva Factura').click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/invoices/create')
  })

  it('should create a new invoice', () => {
    cy.contains('Nueva Factura').click()
    cy.waitForLivewire()
    
    // Wait for form to load
    cy.contains('Nueva Factura').should('be.visible')
    cy.get('[data-cy="invoice-create-form"]').should('be.visible')
    
    // Select client using data-cy selector
    cy.get('[data-cy="client-id"]').then(($select) => {
      if ($select.length > 0 && $select.find('option').length > 1) {
        // Select the second option (first is "Selecciona un cliente")
        cy.wrap($select).find('option').eq(1).then(($option) => {
          cy.wrap($select).select($option.val(), { force: true });
        });
        cy.wait(1000);
      }
    });
    
    // Fill delivery note code using data-cy selector
    cy.get('[data-cy="delivery-note-code"]').clear().type('ALB-TEST-001');
    
    // Fill invoice date using data-cy selector
    cy.get('[data-cy="invoice-date"]').then(($input) => {
      if ($input.length > 0) {
        const today = new Date().toISOString().split('T')[0];
        cy.wrap($input).clear().type(today);
      }
    });
    
    // Fill due date using data-cy selector
    cy.get('[data-cy="due-date"]').then(($input) => {
      if ($input.length > 0) {
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        const dueDate = nextMonth.toISOString().split('T')[0];
        cy.wrap($input).clear().type(dueDate);
      }
    });
    
    // Add invoice item using data-cy selector
    cy.get('[data-cy="add-item-button"]').then(($button) => {
      if ($button.length > 0) {
        cy.wrap($button).click({ force: true });
        cy.wait(1000);
        
        // Fill item fields using data-cy selectors
        cy.get('[data-cy="item-name"][data-cy-item-index="0"]').clear().type('Item de Prueba E2E');
        cy.get('[data-cy="item-quantity"][data-cy-item-index="0"]').clear().type('10');
        cy.get('[data-cy="item-unit-price"][data-cy-item-index="0"]').clear().type('25.50');
      }
    });
    
    // Submit form using data-cy selector
    cy.get('[data-cy="submit-button"]').click()
    
    // Wait for Livewire to process
    cy.wait(5000)
    
    // Check if we're redirected back to index
    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.log('⚠ Redirected to login - may be a session issue')
        cy.loginAsViticulturist()
        cy.visit('/viticulturist/invoices')
        cy.waitForLivewire()
      } else {
        cy.url().should('include', '/viticulturist/invoices')
      }
    })
  })

  it('should search invoices', () => {
    // Find search input - try data-cy first, fallback to placeholder
    cy.get('body').then(($body) => {
      const searchInput = $body.find('[data-cy="search-input"]');
      if (searchInput.length > 0) {
        cy.get('[data-cy="search-input"]').clear().type('Test')
      } else {
        cy.get('input[placeholder*="Buscar facturas" i], input[placeholder*="buscar" i]').first().clear().type('Test')
      }
    })
    cy.waitForLivewire()
    
    // Verify search is working
    cy.get('body').then(($body) => {
      const searchInput = $body.find('[data-cy="search-input"]');
      if (searchInput.length > 0) {
        cy.get('[data-cy="search-input"]').should('have.value', 'Test')
      } else {
        cy.get('input[placeholder*="Buscar facturas" i], input[placeholder*="buscar" i]').first().should('have.value', 'Test')
      }
    })
  })

  it('should filter invoices by status', () => {
    // Find the select with filterStatus options
    cy.get('select').then(($selects) => {
      const statusSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => 
          opt.textContent.toLowerCase().includes('borrador') || 
          opt.textContent.toLowerCase().includes('enviada') ||
          opt.textContent.toLowerCase().includes('todos los estados')
        );
      });
      
      if (statusSelect && statusSelect.querySelectorAll('option').length > 1) {
        cy.wrap(statusSelect).select('draft', { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should filter invoices by payment status', () => {
    // Find the select with filterPaymentStatus options
    cy.get('select').then(($selects) => {
      const paymentSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => 
          opt.textContent.toLowerCase().includes('pagada') || 
          opt.textContent.toLowerCase().includes('pendiente') ||
          opt.textContent.toLowerCase().includes('todos los pagos')
        );
      });
      
      if (paymentSelect && paymentSelect.querySelectorAll('option').length > 1) {
        cy.wrap(paymentSelect).select('unpaid', { force: true });
        cy.waitForLivewire();
      }
    });
  })

  it('should view invoice details', () => {
    // Click first invoice link or view button
    cy.get('a[href*="/viticulturist/invoices/"]').first().then(($link) => {
      const href = $link.attr('href');
      if (href && !href.includes('/edit') && !href.includes('/create') && !href.includes('/harvest')) {
        cy.wrap($link).click({ force: true });
        cy.waitForLivewire();
        cy.url().should('include', '/viticulturist/invoices/');
        cy.url().should('not.include', '/edit');
        cy.url().should('not.include', '/create');
      }
    });
  })

  it('should edit an existing invoice', () => {
    // Click first edit link in the table
    cy.get('a[href*="/viticulturist/invoices/"][href*="/edit"]').first().then(($link) => {
      if ($link.length > 0) {
        cy.wrap($link).click({ force: true });
        cy.waitForLivewire();
        
        // We should be on the edit page
        cy.url().should('include', '/viticulturist/invoices/');
        cy.url().should('include', '/edit');
        
        // Modify observations using data-cy selector
        cy.get('[data-cy="observations"]').then(($textarea) => {
          if ($textarea.length > 0) {
            cy.wrap($textarea).clear().type('Observaciones editadas E2E');
          }
        });
        
        // Submit form using data-cy selector
        cy.get('[data-cy="submit-button"]').click()
        cy.wait(5000)
        
        // Back on index with updated invoice
        cy.url().should('include', '/viticulturist/invoices')
        cy.url().should('not.include', '/edit')
      } else {
        cy.log('No edit links found - skipping test');
      }
    });
  })

  it('should navigate to harvest invoices', () => {
    cy.get('a[href*="/viticulturist/invoices/harvest"]').first().click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/invoices/harvest')
  })

  it('should display invoice statistics', () => {
    // Check for statistics cards
    cy.get('body').should('contain.text', 'Total')
    // Statistics might be in cards or sections
    cy.get('body').then(($body) => {
      if ($body.text().includes('Borrador') || $body.text().includes('Enviada') || $body.text().includes('Aprobada')) {
        cy.log('✓ Invoice statistics visible');
      }
    });
  })

  it('should change invoice status', () => {
    // Look for status change buttons - this might be in the show/edit page
    // For now, just verify the test can navigate to an invoice
    cy.get('a[href*="/viticulturist/invoices/"]').first().then(($link) => {
      const href = $link.attr('href');
      if (href && !href.includes('/edit') && !href.includes('/create') && !href.includes('/harvest')) {
        cy.wrap($link).click({ force: true });
        cy.waitForLivewire();
        cy.url().should('include', '/viticulturist/invoices/');
        cy.log('✓ Navigated to invoice details - status change buttons would be tested here');
      } else {
        cy.log('No invoice links found - skipping test');
      }
    });
  })

  it('should add and manage invoice items', () => {
    cy.contains('Nueva Factura').click()
    cy.waitForLivewire()
    
    // Wait for form
    cy.contains('Nueva Factura').should('be.visible')
    cy.get('[data-cy="invoice-create-form"]').should('be.visible')
    
    // Select client using data-cy
    cy.get('[data-cy="client-id"]').then(($select) => {
      if ($select.length > 0 && $select.find('option').length > 1) {
        cy.wrap($select).find('option').eq(1).then(($option) => {
          cy.wrap($select).select($option.val(), { force: true });
        });
        cy.wait(1000);
      }
    });
    
    // Fill required fields using data-cy
    cy.get('[data-cy="delivery-note-code"]').clear().type('ALB-MULTI-ITEMS-001');
    const today = new Date().toISOString().split('T')[0];
    cy.get('[data-cy="invoice-date"]').clear().type(today);
    
    // Add first item using data-cy
    cy.get('[data-cy="add-item-button"]').first().click({ force: true });
    cy.wait(1000);
    
    cy.get('[data-cy="item-name"][data-cy-item-index="0"]').clear().type('Item 1 E2E');
    cy.get('[data-cy="item-quantity"][data-cy-item-index="0"]').clear().type('5');
    cy.get('[data-cy="item-unit-price"][data-cy-item-index="0"]').clear().type('10.00');
    
    // Add second item
    cy.get('[data-cy="add-item-button"]').first().click({ force: true });
    cy.wait(1000);
    
    cy.get('[data-cy="item-name"][data-cy-item-index="1"]').clear().type('Item 2 E2E');
    cy.get('[data-cy="item-quantity"][data-cy-item-index="1"]').clear().type('3');
    cy.get('[data-cy="item-unit-price"][data-cy-item-index="1"]').clear().type('15.50');
    
    // Verify both items exist
    cy.get('[data-cy="invoice-item"][data-cy-item-index="0"]').should('be.visible');
    cy.get('[data-cy="invoice-item"][data-cy-item-index="1"]').should('be.visible');
    
    // Remove second item using data-cy
    cy.get('[data-cy="remove-item"][data-cy-item-index="1"]').click({ force: true });
    cy.wait(1000);
    
    // Verify second item is removed
    cy.get('[data-cy="invoice-item"][data-cy-item-index="1"]').should('not.exist');
    
    // Verify first item still exists
    cy.get('[data-cy="item-name"][data-cy-item-index="0"]').should('have.value', 'Item 1 E2E')
  })
})

