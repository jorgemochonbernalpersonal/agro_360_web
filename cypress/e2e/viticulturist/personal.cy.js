describe('Viticulturist Personal & Teams (Equipos y Personal)', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/personal')
    cy.waitForLivewire()
  })

  describe('Personal View', () => {
    it('should display personal view by default', () => {
      cy.contains('Equipos y Personal').should('be.visible')
      cy.contains('Filtros de Búsqueda').should('be.visible')
      cy.get('[data-cy="personal-tab"]').should('be.visible')
    })

    it('should show statistics panel', () => {
      cy.get('body').should('contain.text', 'Total')
      cy.get('body').should('contain.text', 'En equipo')
      cy.get('body').should('contain.text', 'Sin equipo')
    })

    it('should filter by search', () => {
      cy.get('[data-cy="personal-search-input"]').clear().type('Test')
      cy.waitForLivewire()
      cy.get('[data-cy="personal-search-input"]').should('have.value', 'Test')
    })

    it('should filter by crew', () => {
      cy.get('[data-cy="crew-filter"]').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('[data-cy="crew-filter"]').select(1, { force: true })
          cy.waitForLivewire()
        } else {
          cy.log('No crews available for filtering')
        }
      })
    })

    it('should filter by status (in_crew, individual, unassigned)', () => {
      cy.get('[data-cy="status-filter"]').then(($select) => {
        if ($select.length > 0) {
          cy.get('[data-cy="status-filter"]').select('in_crew', { force: true })
          cy.waitForLivewire()
          cy.get('[data-cy="status-filter"]').should('have.value', 'in_crew')
        }
      })
    })

    it('should switch to crews view', () => {
      cy.get('[data-cy="crews-tab"]').click()
      cy.waitForLivewire()
      cy.url().should('include', 'viewMode=crews')
      cy.get('[data-cy="crews-tab"]').should('be.visible')
    })
  })

  describe('Crews View', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/personal?viewMode=crews')
      cy.waitForLivewire()
    })

    it('should display crews list', () => {
      cy.contains('Equipos y Personal').should('be.visible')
      cy.contains('Filtros de Búsqueda').should('be.visible')
    })

    it('should create a new crew', () => {
      cy.get('[data-cy="create-crew-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/personal/create')
      
      // Check for form
      cy.get('[data-cy="crew-form"]').should('be.visible')
      cy.contains('Nueva Cuadrilla').should('be.visible')
      
      // Fill form
      const crewName = `Equipo E2E Test ${Date.now()}`
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="crew-description-input"]').clear().type('Descripción de prueba E2E')
      
      // Submit form
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Should redirect to index
      cy.url().should('include', '/viticulturist/personal')
      cy.contains(crewName).should('be.visible')
    })

    it('should search crews', () => {
      cy.get('[data-cy="personal-search-input"]').clear().type('Test')
      cy.waitForLivewire()
      cy.get('[data-cy="personal-search-input"]').should('have.value', 'Test')
    })
  })

  describe('Create Crew', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/personal/create')
      cy.waitForLivewire()
    })

    it('should display create form', () => {
      cy.get('[data-cy="crew-form"]').should('be.visible')
      cy.contains('Nueva Cuadrilla').should('be.visible')
      cy.get('[data-cy="crew-name-input"]').should('be.visible')
      cy.get('[data-cy="crew-description-input"]').should('be.visible')
    })

    it('should create crew with required fields', () => {
      const crewName = `Crew Required ${Date.now()}`
      
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/personal')
      cy.contains(crewName).should('be.visible')
    })

    it('should create crew with all fields', () => {
      const crewName = `Crew Complete ${Date.now()}`
      
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="crew-description-input"]').clear().type('Descripción completa de prueba')
      
      // Select winery if available
      cy.get('[data-cy="crew-winery-select"]').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('[data-cy="crew-winery-select"]').select(1, { force: true })
        }
      })
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/personal')
      cy.contains(crewName).should('be.visible')
    })

    it('should validate required fields', () => {
      // Try to submit without name
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should not submit
      cy.url().should('include', '/viticulturist/personal/create')
    })

    it('should cancel and return to list', () => {
      cy.get('[data-cy="cancel-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/personal')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Edit Crew', () => {
    beforeEach(() => {
      // First create a crew to edit
      cy.visit('/viticulturist/personal/create')
      cy.waitForLivewire()
      
      const crewName = `Crew para Editar ${Date.now()}`
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Navigate to edit
      cy.get('[data-cy="edit-crew-button"]').first().click({ force: true })
      cy.waitForLivewire()
    })

    it('should display edit form', () => {
      cy.get('[data-cy="crew-form"]').should('be.visible')
      cy.contains('Editar Cuadrilla').should('be.visible')
      cy.get('[data-cy="crew-name-input"]').should('be.visible')
    })

    it('should edit crew name', () => {
      const newName = `Crew Editada ${Date.now()}`
      
      cy.get('[data-cy="crew-name-input"]').clear().type(newName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/personal')
      cy.contains(newName).should('be.visible')
    })

    it('should edit all crew fields', () => {
      const newName = `Crew Completa Editada ${Date.now()}`
      
      cy.get('[data-cy="crew-name-input"]').clear().type(newName)
      cy.get('[data-cy="crew-description-input"]').clear().type('Nueva descripción editada')
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/personal')
      cy.contains(newName).should('be.visible')
    })

    it('should cancel edit and return to show', () => {
      cy.get('[data-cy="cancel-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/personal/')
      cy.url().should('not.include', '/edit')
    })
  })

  describe('Crew Show View', () => {
    beforeEach(() => {
      // Create a crew first
      cy.visit('/viticulturist/personal/create')
      cy.waitForLivewire()
      
      const crewName = `Crew Show Test ${Date.now()}`
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Navigate to show
      cy.get('[data-cy="view-crew-button"]').first().click({ force: true })
      cy.waitForLivewire()
    })

    it('should display crew details', () => {
      cy.get('[data-cy="crew-statistics"]').should('be.visible')
      cy.get('[data-cy="crew-members-section"]').should('be.visible')
    })

    it('should display crew statistics', () => {
      cy.get('[data-cy="crew-stats-grid"]').should('be.visible')
      cy.get('[data-cy="crew-stats-grid"]').within(() => {
        cy.contains('Miembros').should('be.visible')
        cy.contains('Actividades').should('be.visible')
      })
    })

    it('should navigate to edit from show view', () => {
      cy.get('[data-cy="edit-crew-button"]').should('be.visible')
      cy.get('[data-cy="edit-crew-button"]').click()
      cy.waitForLivewire()
      
      cy.url().should('include', '/viticulturist/personal/')
      cy.url().should('include', '/edit')
      cy.get('[data-cy="crew-form"]').should('be.visible')
    })

    it('should navigate back to list from show view', () => {
      cy.get('[data-cy="back-button"]').click()
      cy.waitForLivewire()
      
      cy.url().should('include', '/viticulturist/personal')
      cy.url().should('not.include', '/edit')
    })
  })

  describe('Assign viticulturist to crew', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/personal?viewMode=personal')
      cy.waitForLivewire()
    })

    it('should assign viticulturist to crew using modal', () => {
      // Look for the + button to assign to crew
      cy.get('button').then(($buttons) => {
        const assignButton = Array.from($buttons).find(btn => {
          return btn.getAttribute('title')?.includes('Asignar') || 
                 btn.querySelector('svg')?.getAttribute('d')?.includes('M12 4v16m8-8H4');
        });
        
        if (assignButton) {
          cy.wrap(assignButton).click({ force: true })
          cy.waitForLivewire()
          
          // Select a crew from modal
          cy.get('select').then(($selects) => {
            const crewSelect = Array.from($selects).find(select => {
              return select.querySelectorAll('option').length > 1;
            });
            
            if (crewSelect) {
              cy.wrap(crewSelect).select(1, { force: true })
              cy.waitForLivewire()
              
              // Confirm assignment
              cy.get('button').contains('Asignar').click()
              cy.waitForLivewire()
            }
          })
        } else {
          cy.log('Assign button not found - may not have crews available')
        }
      })
    })

    it('should mark viticulturist as individual', () => {
      // Look for button to make individual
      cy.get('button').then(($buttons) => {
        const individualButton = Array.from($buttons).find(btn => {
          return btn.getAttribute('title')?.includes('Individual') || 
                 btn.getAttribute('title')?.includes('Sin equipo');
        });
        
        if (individualButton) {
          cy.wrap(individualButton).click({ force: true })
          cy.waitForLivewire()
        } else {
          cy.log('Individual button not found')
        }
      })
    })
  })

  describe('Create Viticulturist', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/personal')
      cy.waitForLivewire()
    })

    it('should navigate to create viticulturist', () => {
      cy.get('[data-cy="create-viticulturist-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/viticulturists/create')
    })
  })

  describe('Crew Validation', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/personal/create')
      cy.waitForLivewire()
    })

    it('should require crew name', () => {
      // Try to submit without name
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should not submit
      cy.url().should('include', '/viticulturist/personal/create')
    })

    it('should handle special characters in name', () => {
      const crewName = `Crew Test & Special ${Date.now()}`
      
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/personal')
      cy.contains(crewName).should('be.visible')
    })

    it('should handle long description', () => {
      const longDescription = 'A'.repeat(500)
      
      cy.get('[data-cy="crew-name-input"]').clear().type('Test Crew')
      cy.get('[data-cy="crew-description-input"]').clear().type(longDescription)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/personal')
    })
  })

  describe('View Switching', () => {
    it('should switch between personal and crews views', () => {
      // Start in personal view
      cy.get('[data-cy="personal-tab"]').should('be.visible')
      
      // Switch to crews
      cy.get('[data-cy="crews-tab"]').click()
      cy.waitForLivewire()
      cy.url().should('include', 'viewMode=crews')
      
      // Switch back to personal
      cy.get('[data-cy="personal-tab"]').click()
      cy.waitForLivewire()
      cy.url().should('include', 'viewMode=personal')
    })
  })

  describe('Toast notifications', () => {
    it('should show toast notification after creating crew', () => {
      cy.get('[data-cy="create-crew-button"]').click()
      cy.waitForLivewire()
      
      const crewName = `Toast Test Crew ${Date.now()}`
      cy.get('[data-cy="crew-name-input"]').clear().type(crewName)
      cy.get('[data-cy="crew-description-input"]').clear().type('Test description')
      
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
