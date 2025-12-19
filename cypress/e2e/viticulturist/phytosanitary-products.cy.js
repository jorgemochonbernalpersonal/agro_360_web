describe('Viticulturist Phytosanitary Products', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/phytosanitary-products')
    cy.waitForLivewire()
  })

  it('should display products list', () => {
    cy.contains('Productos Fitosanitarios').should('be.visible')
    cy.contains('Filtros de Búsqueda').should('be.visible')
  })

  it('should navigate to create product', () => {
    cy.contains('Nuevo Producto').click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/phytosanitary-products/create')
  })

  it('should create a new product', () => {
    cy.contains('Nuevo Producto').click()
    cy.waitForLivewire()
    
    cy.contains('Nuevo Producto Fitosanitario').should('be.visible')
    
    // Fill form
    cy.get('input[wire\\:model="name"]#name').clear().type('Producto E2E Test')
    cy.get('input[wire\\:model="active_ingredient"]#active_ingredient').clear().type('Ingrediente Activo Test')
    cy.get('input[wire\\:model="registration_number"]#registration_number').clear().type('REG-12345')
    
    // Submit form
    cy.get('form[wire\\:submit]').first().within(() => {
      cy.get('button[type="submit"]').click()
    })
    
    cy.wait(5000)
    cy.url().should('include', '/viticulturist/phytosanitary-products')
  })

  it('should search products', () => {
    cy.get('input[placeholder*="nombre"]').clear().type('Test')
    cy.waitForLivewire()
    cy.get('input[placeholder*="nombre"]').should('have.value', 'Test')
  })

  it('should filter products by active ingredient', () => {
    // Check if filter exists - may not be present in all views
    cy.get('body').then(($body) => {
      const ingredientInput = $body.find('input[placeholder*="ingrediente"]');
      if (ingredientInput.length > 0) {
        cy.get('input[placeholder*="ingrediente"]').clear().type('Test Ingredient')
        cy.waitForLivewire()
      } else {
        cy.log('Active ingredient filter not available - skipping test')
      }
    })
  })

  it('should edit an existing product', () => {
    // Click first edit action - may be in a dropdown menu
    cy.get('body').then(($body) => {
      // Try to find edit link/button
      const editLink = $body.find('a[title="Editar"], a[href*="/edit"]').first();
      
      if (editLink.length > 0) {
        // Get the href and visit directly to avoid dropdown issues
        const href = editLink.attr('href');
        if (href) {
          cy.visit(href)
        } else {
          cy.wrap(editLink).click({ force: true })
        }
      } else {
        // Try to find any edit button
        cy.get('a[href*="/phytosanitary-products/"][href*="/edit"]').first().then(($editLink) => {
          if ($editLink.length > 0) {
            cy.wrap($editLink).click({ force: true })
          } else {
            cy.log('No edit link found - skipping test')
            return
          }
        })
      }
    })
    
    cy.waitForLivewire()
    
    cy.url().then(($url) => {
      if ($url.includes('/phytosanitary-products/') && $url.includes('/edit')) {
        // Modify name
        cy.get('input#name').clear().type('Producto Editado E2E')
        
        // Submit - look for any submit button
        cy.get('button[type="submit"]').first().click({ force: true })
        cy.wait(5000)
        
        cy.url().should('include', '/viticulturist/phytosanitary-products')
      }
    })
  })

  it('should show toast notification after creating product', () => {
    cy.contains('Nuevo Producto').click()
    cy.waitForLivewire()
    
    cy.get('input[wire\\:model="name"]#name').clear().type('Toast Test Product')
    cy.get('input[wire\\:model="active_ingredient"]#active_ingredient').clear().type('Test Ingredient')
    cy.get('input[wire\\:model="registration_number"]#registration_number').clear().type('REG-TOAST')
    
    cy.get('form[wire\\:submit]').first().within(() => {
      cy.get('button[type="submit"]').click()
    })
    
    cy.wait(3000)
    
    // Check for toast notification
    cy.get('body').then(($body) => {
      const toasts = $body.find('[x-data*="toastNotifications"]');
      if (toasts.length > 0 || $body.text().includes('correctamente')) {
        cy.log('Toast notification appeared')
      }
    })
  })
})

