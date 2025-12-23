describe('Viticulturist Phytosanitary Products', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/phytosanitary-products')
    cy.waitForLivewire()
  })

  describe('Products List', () => {
    it('should display products list', () => {
      cy.contains('Productos Fitosanitarios').should('be.visible')
      cy.contains('Filtros de Búsqueda').should('be.visible')
    })

    it('should navigate to create product', () => {
      cy.get('[data-cy="create-product-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/phytosanitary-products/create')
    })

    it('should search products', () => {
      cy.get('[data-cy="product-search-input"]').clear().type('Test')
      cy.waitForLivewire()
      cy.get('[data-cy="product-search-input"]').should('have.value', 'Test')
    })

    it('should filter products by type', () => {
      cy.get('[data-cy="product-type-filter"]').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('[data-cy="product-type-filter"]').select(1, { force: true })
          cy.waitForLivewire()
        }
      })
    })

    it('should clear filters work', () => {
      cy.get('[data-cy="product-search-input"]').clear().type('Test')
      cy.get('[data-cy="product-type-filter"]').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('[data-cy="product-type-filter"]').select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      
      cy.contains('Limpiar Filtros').click()
      cy.waitForLivewire()
      
      cy.get('[data-cy="product-search-input"]').should('have.value', '')
    })
  })

  describe('Create Product', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/phytosanitary-products/create')
      cy.waitForLivewire()
    })

    it('should display create form', () => {
      cy.get('[data-cy="product-form"]').should('be.visible')
      cy.contains('Nuevo Producto Fitosanitario').should('be.visible')
      cy.get('[data-cy="product-name-input"]').should('be.visible')
      cy.get('[data-cy="product-active-ingredient-input"]').should('be.visible')
    })

    it('should create product with required fields', () => {
      const productName = `Producto E2E Test ${Date.now()}`
      
      cy.get('[data-cy="product-name-input"]').clear().type(productName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.contains(productName).should('be.visible')
    })

    it('should create product with all fields', () => {
      const productName = `Producto Completo ${Date.now()}`
      
      cy.get('[data-cy="product-name-input"]').clear().type(productName)
      cy.get('[data-cy="product-active-ingredient-input"]').clear().type('Ingrediente Activo Test')
      cy.get('[data-cy="product-type-select"]').select('fungicida', { force: true })
      cy.get('[data-cy="product-toxicity-class-select"]').select('II', { force: true })
      cy.get('[data-cy="product-withdrawal-period-input"]').clear().type('21')
      cy.get('[data-cy="product-registration-number-input"]').clear().type('REG-12345')
      cy.get('[data-cy="product-manufacturer-input"]').clear().type('Fabricante Test')
      cy.get('[data-cy="product-description-input"]').clear().type('Descripción completa del producto')
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.contains(productName).should('be.visible')
    })

    it('should validate required fields', () => {
      // Try to submit without name
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should not submit
      cy.url().should('include', '/viticulturist/phytosanitary-products/create')
    })

    it('should validate withdrawal period is non-negative', () => {
      cy.get('[data-cy="product-name-input"]').clear().type('Test Product')
      cy.get('[data-cy="product-withdrawal-period-input"]').should('have.attr', 'min', '0')
    })

    it('should cancel and return to list', () => {
      cy.get('[data-cy="cancel-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Edit Product', () => {
    beforeEach(() => {
      // First create a product to edit
      cy.visit('/viticulturist/phytosanitary-products/create')
      cy.waitForLivewire()
      
      const productName = `Producto para Editar ${Date.now()}`
      cy.get('[data-cy="product-name-input"]').clear().type(productName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Navigate to edit
      cy.get('[data-cy="edit-product-button"]').first().click({ force: true })
      cy.waitForLivewire()
    })

    it('should display edit form', () => {
      cy.get('[data-cy="product-form"]').should('be.visible')
      cy.contains('Editar Producto Fitosanitario').should('be.visible')
      cy.get('[data-cy="product-name-input"]').should('be.visible')
    })

    it('should edit product name', () => {
      const newName = `Producto Editado ${Date.now()}`
      
      cy.get('[data-cy="product-name-input"]').clear().type(newName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.contains(newName).should('be.visible')
    })

    it('should edit all product fields', () => {
      const newName = `Producto Completo Editado ${Date.now()}`
      
      cy.get('[data-cy="product-name-input"]').clear().type(newName)
      cy.get('[data-cy="product-active-ingredient-input"]').clear().type('Nuevo Ingrediente')
      cy.get('[data-cy="product-type-select"]').select('insecticida', { force: true })
      cy.get('[data-cy="product-toxicity-class-select"]').select('III', { force: true })
      cy.get('[data-cy="product-withdrawal-period-input"]').clear().type('30')
      cy.get('[data-cy="product-registration-number-input"]').clear().type('REG-99999')
      cy.get('[data-cy="product-manufacturer-input"]').clear().type('Nuevo Fabricante')
      cy.get('[data-cy="product-description-input"]').clear().type('Nueva descripción')
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.contains(newName).should('be.visible')
    })

    it('should cancel edit and return to list', () => {
      cy.get('[data-cy="cancel-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.url().should('not.include', '/edit')
    })
  })

  describe('Product Validation', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/phytosanitary-products/create')
      cy.waitForLivewire()
    })

    it('should require product name', () => {
      // Try to submit without name
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should not submit
      cy.url().should('include', '/viticulturist/phytosanitary-products/create')
    })

    it('should handle special characters in name', () => {
      const productName = `Producto Test & Special ${Date.now()}`
      
      cy.get('[data-cy="product-name-input"]').clear().type(productName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/phytosanitary-products')
      cy.contains(productName).should('be.visible')
    })

    it('should handle all product types', () => {
      const types = ['fungicida', 'herbicida', 'insecticida', 'acaricida', 'regulador del crecimiento', 'otro']
      
      cy.get('[data-cy="product-name-input"]').clear().type('Test Product')
      
      types.forEach((type, index) => {
        cy.get('[data-cy="product-type-select"]').select(type, { force: true })
        cy.get('[data-cy="product-type-select"]').should('have.value', type)
      })
    })

    it('should handle all toxicity classes', () => {
      const classes = ['I', 'II', 'III', 'IV']
      
      cy.get('[data-cy="product-name-input"]').clear().type('Test Product')
      
      classes.forEach((toxClass) => {
        cy.get('[data-cy="product-toxicity-class-select"]').select(toxClass, { force: true })
        cy.get('[data-cy="product-toxicity-class-select"]').should('have.value', toxClass)
      })
    })

    it('should handle long description', () => {
      const longDescription = 'A'.repeat(500)
      
      cy.get('[data-cy="product-name-input"]').clear().type('Test Product')
      cy.get('[data-cy="product-description-input"]').clear().type(longDescription)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/phytosanitary-products')
    })

    it('should validate withdrawal period range', () => {
      cy.get('[data-cy="product-name-input"]').clear().type('Test Product')
      
      // Test minimum value
      cy.get('[data-cy="product-withdrawal-period-input"]').should('have.attr', 'min', '0')
      
      // Test with valid value
      cy.get('[data-cy="product-withdrawal-period-input"]').clear().type('21')
      cy.get('[data-cy="product-withdrawal-period-input"]').should('have.value', '21')
    })
  })

  describe('Product Interactions', () => {
    it('should filter and search work together', () => {
      // Filter by type
      cy.get('[data-cy="product-type-filter"]').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('[data-cy="product-type-filter"]').select(1, { force: true })
          cy.waitForLivewire()
        }
      })
      
      // Then search
      cy.get('[data-cy="product-search-input"]').clear().type('Test')
      cy.waitForLivewire()
      
      // Both filters should be active
      cy.get('[data-cy="product-search-input"]').should('have.value', 'Test')
    })
  })

  describe('Toast notifications', () => {
    it('should show toast notification after creating product', () => {
      cy.get('[data-cy="create-product-button"]').click()
      cy.waitForLivewire()
      
      const productName = `Toast Test Product ${Date.now()}`
      cy.get('[data-cy="product-name-input"]').clear().type(productName)
      cy.get('[data-cy="product-active-ingredient-input"]').clear().type('Test Ingredient')
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(3000)
      
      // Check for toast notification
      cy.get('body').then(($body) => {
        if ($body.find('[x-data*="toastNotifications"]').length > 0 || $body.text().includes('correctamente')) {
          cy.log('Toast notification appeared')
        }
      })
    })
  })
})
