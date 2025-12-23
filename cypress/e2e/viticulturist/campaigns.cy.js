describe('Viticulturist Campaigns', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/campaign')
    cy.waitForLivewire()
  })

  describe('Campaign List', () => {
    it('should display campaigns list', () => {
      cy.contains('Gestión de Campañas').should('be.visible')
      cy.contains('Filtros de Búsqueda').should('be.visible')
    })

    it('should filter campaigns by year', () => {
      cy.get('[data-cy="campaign-year-filter"]').should('be.visible')
      cy.get('[data-cy="campaign-year-filter"]').select(1, { force: true })
      cy.waitForLivewire()
    })

    it('should search campaigns', () => {
      cy.get('[data-cy="campaign-search-input"]').clear().type('Test Campaign')
      cy.waitForLivewire()
      cy.get('[data-cy="campaign-search-input"]').should('have.value', 'Test Campaign')
    })

    it('should navigate to create campaign', () => {
      cy.get('[data-cy="create-campaign-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/campaign/create')
    })
  })

  describe('Create Campaign', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/campaign/create')
      cy.waitForLivewire()
    })

    it('should display create form', () => {
      cy.get('[data-cy="campaign-create-form"]').should('be.visible')
      cy.contains('Nueva Campaña').should('be.visible')
      cy.get('[data-cy="campaign-name-input"]').should('be.visible')
      cy.get('[data-cy="campaign-year-input"]').should('be.visible')
    })

    it('should create a new campaign with required fields', () => {
      const campaignName = `Campaña E2E Test ${Date.now()}`
      const currentYear = new Date().getFullYear()
      
      cy.get('[data-cy="campaign-name-input"]').clear().type(campaignName)
      cy.get('[data-cy="campaign-year-input"]').clear().type(currentYear.toString())
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.contains(campaignName).should('be.visible')
    })

    it('should create campaign with all fields', () => {
      const campaignName = `Campaña Completa ${Date.now()}`
      const currentYear = new Date().getFullYear()
      const startDate = `${currentYear}-01-01`
      const endDate = `${currentYear}-12-31`
      
      cy.get('[data-cy="campaign-name-input"]').clear().type(campaignName)
      cy.get('[data-cy="campaign-year-input"]').clear().type(currentYear.toString())
      cy.get('[data-cy="campaign-description-input"]').clear().type('Descripción completa de prueba E2E')
      cy.get('[data-cy="campaign-start-date-input"]').type(startDate)
      cy.get('[data-cy="campaign-end-date-input"]').type(endDate)
      cy.get('[data-cy="campaign-active-checkbox"]').check()
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.contains(campaignName).should('be.visible')
    })

    it('should validate required fields', () => {
      // Try to submit without filling required fields
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should show validation errors
      cy.get('[data-cy="campaign-form"]').should('be.visible')
      // Form should not submit (still on create page)
      cy.url().should('include', '/viticulturist/campaign/create')
    })

    it('should validate year range', () => {
      cy.get('[data-cy="campaign-name-input"]').clear().type('Test Campaign')
      cy.get('[data-cy="campaign-year-input"]').clear().type('1999') // Below minimum
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should show validation error or prevent submission
      cy.url().should('include', '/viticulturist/campaign/create')
    })

    it('should cancel and return to list', () => {
      cy.get('[data-cy="cancel-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/campaign')
      cy.url().should('not.include', '/create')
    })

    it('should use back button to return to list', () => {
      cy.get('[data-cy="back-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/campaign')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Edit Campaign', () => {
    beforeEach(() => {
      // First create a campaign to edit
      cy.visit('/viticulturist/campaign/create')
      cy.waitForLivewire()
      
      const campaignName = `Campaña para Editar ${Date.now()}`
      const currentYear = new Date().getFullYear()
      
      cy.get('[data-cy="campaign-name-input"]').clear().type(campaignName)
      cy.get('[data-cy="campaign-year-input"]').clear().type(currentYear.toString())
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      // Navigate to edit
      cy.get('[data-cy="edit-campaign-button"]').first().click({ force: true })
      cy.waitForLivewire()
    })

    it('should display edit form', () => {
      cy.get('[data-cy="campaign-edit-form"]').should('be.visible')
      cy.contains('Editar Campaña').should('be.visible')
      cy.get('[data-cy="campaign-name-input"]').should('be.visible')
      cy.get('[data-cy="campaign-year-input"]').should('be.visible')
    })

    it('should edit campaign name', () => {
      const newName = `Campaña Editada ${Date.now()}`
      
      cy.get('[data-cy="campaign-name-input"]').clear().type(newName)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.contains(newName).should('be.visible')
    })

    it('should edit campaign year', () => {
      const newYear = (new Date().getFullYear() + 1).toString()
      
      cy.get('[data-cy="campaign-year-input"]').clear().type(newYear)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.contains(newYear).should('be.visible')
    })

    it('should edit all campaign fields', () => {
      const newName = `Campaña Completa Editada ${Date.now()}`
      const newYear = (new Date().getFullYear() + 1).toString()
      const startDate = `${newYear}-01-01`
      const endDate = `${newYear}-12-31`
      
      cy.get('[data-cy="campaign-name-input"]').clear().type(newName)
      cy.get('[data-cy="campaign-year-input"]').clear().type(newYear)
      cy.get('[data-cy="campaign-description-input"]').clear().type('Nueva descripción editada')
      cy.get('[data-cy="campaign-start-date-input"]').clear().type(startDate)
      cy.get('[data-cy="campaign-end-date-input"]').clear().type(endDate)
      cy.get('[data-cy="campaign-active-checkbox"]').check()
      
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.contains(newName).should('be.visible')
    })

    it('should cancel edit and return to list', () => {
      cy.get('[data-cy="cancel-button"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/campaign')
      cy.url().should('not.include', '/edit')
    })
  })

  describe('Campaign Validation', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/campaign/create')
      cy.waitForLivewire()
    })

    it('should require campaign name', () => {
      const currentYear = new Date().getFullYear()
      
      cy.get('[data-cy="campaign-year-input"]').clear().type(currentYear.toString())
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should not submit
      cy.url().should('include', '/viticulturist/campaign/create')
    })

    it('should require campaign year', () => {
      cy.get('[data-cy="campaign-name-input"]').clear().type('Test Campaign')
      cy.get('[data-cy="submit-button"]').click()
      cy.waitForLivewire()
      
      // Should not submit
      cy.url().should('include', '/viticulturist/campaign/create')
    })

    it('should validate year is within range', () => {
      cy.get('[data-cy="campaign-name-input"]').clear().type('Test Campaign')
      
      // Test minimum year
      cy.get('[data-cy="campaign-year-input"]').clear().type('1999')
      cy.get('[data-cy="campaign-year-input"]').should('have.attr', 'min', '2000')
      
      // Test maximum year
      const maxYear = new Date().getFullYear() + 5
      cy.get('[data-cy="campaign-year-input"]').should('have.attr', 'max', maxYear.toString())
    })

    it('should handle special characters in name', () => {
      const campaignName = `Campaña Test & Special Chars ${Date.now()}`
      const currentYear = new Date().getFullYear()
      
      cy.get('[data-cy="campaign-name-input"]').clear().type(campaignName)
      cy.get('[data-cy="campaign-year-input"]').clear().type(currentYear.toString())
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.contains(campaignName).should('be.visible')
    })

    it('should handle long description', () => {
      const longDescription = 'A'.repeat(500)
      const currentYear = new Date().getFullYear()
      
      cy.get('[data-cy="campaign-name-input"]').clear().type('Test Campaign')
      cy.get('[data-cy="campaign-year-input"]').clear().type(currentYear.toString())
      cy.get('[data-cy="campaign-description-input"]').clear().type(longDescription)
      cy.get('[data-cy="submit-button"]').click()
      cy.wait(5000)
      
      cy.url().should('include', '/viticulturist/campaign')
    })
  })

  describe('Campaign Interactions', () => {
    it('should filter and search work together', () => {
      const currentYear = new Date().getFullYear()
      
      // Filter by year
      cy.get('[data-cy="campaign-year-filter"]').select(currentYear.toString(), { force: true })
      cy.waitForLivewire()
      
      // Then search
      cy.get('[data-cy="campaign-search-input"]').clear().type('Test')
      cy.waitForLivewire()
      
      // Both filters should be active
      cy.get('[data-cy="campaign-year-filter"]').should('have.value', currentYear.toString())
      cy.get('[data-cy="campaign-search-input"]').should('have.value', 'Test')
    })

    it('should clear filters work', () => {
      cy.get('[data-cy="campaign-search-input"]').clear().type('Test')
      cy.get('[data-cy="campaign-year-filter"]').select(1, { force: true })
      cy.waitForLivewire()
      
      // Clear filters button should appear
      cy.contains('Limpiar Filtros').click()
      cy.waitForLivewire()
      
      cy.get('[data-cy="campaign-search-input"]').should('have.value', '')
      cy.get('[data-cy="campaign-year-filter"]').should('have.value', '')
    })
  })

  describe('Campaign Show View', () => {
    beforeEach(() => {
      // Create a campaign first if needed, or use existing one
      cy.visit('/viticulturist/campaign')
      cy.waitForLivewire()
      
      // Click on view button for first campaign
      cy.get('[data-cy="view-campaign-button"]').first().click({ force: true })
      cy.waitForLivewire()
    })

    it('should display campaign details', () => {
      cy.get('[data-cy="campaign-statistics"]').should('be.visible')
      cy.get('[data-cy="campaign-info"]').should('be.visible')
      cy.get('[data-cy="campaign-quick-actions"]').should('be.visible')
    })

    it('should display campaign statistics', () => {
      cy.get('[data-cy="campaign-stats-grid"]').should('be.visible')
      cy.get('[data-cy="campaign-stats-grid"]').within(() => {
        cy.contains('Total').should('be.visible')
        cy.contains('Tratamientos').should('be.visible')
        cy.contains('Fertilizaciones').should('be.visible')
        cy.contains('Riegos').should('be.visible')
        cy.contains('Labores').should('be.visible')
        cy.contains('Observaciones').should('be.visible')
      })
    })

    it('should display campaign information', () => {
      cy.get('[data-cy="campaign-info"]').within(() => {
        cy.contains('Estado').should('be.visible')
        cy.contains('Año').should('be.visible')
      })
    })

    it('should navigate to edit from show view', () => {
      cy.get('[data-cy="edit-campaign-button"]').should('be.visible')
      cy.get('[data-cy="edit-campaign-button"]').click()
      cy.waitForLivewire()
      
      cy.url().should('include', '/viticulturist/campaign/')
      cy.url().should('include', '/edit')
      cy.get('[data-cy="campaign-edit-form"]').should('be.visible')
    })

    it('should navigate back to list from show view', () => {
      cy.get('[data-cy="back-button"]').click()
      cy.waitForLivewire()
      
      cy.url().should('include', '/viticulturist/campaign')
      cy.url().should('not.include', '/edit')
      cy.url().should('not.match', /\/\d+$/) // Should not end with campaign ID
    })

    it('should activate campaign from show view', () => {
      // Only if campaign is not active
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="activate-campaign-button"]').length > 0) {
          cy.get('[data-cy="activate-campaign-button"]').click()
          cy.waitForLivewire()
          cy.wait(2000)
          
          // Should show success message or update status
          cy.get('[data-cy="campaign-info"]').should('be.visible')
        } else {
          cy.log('Campaign is already active - skipping activation test')
        }
      })
    })

    it('should display quick actions', () => {
      cy.get('[data-cy="campaign-quick-actions"]').within(() => {
        cy.get('[data-cy="view-activities-button"]').should('be.visible')
        cy.contains('Ver Actividades en Cuaderno Digital').should('be.visible')
      })
    })

    it('should navigate to digital notebook from quick actions', () => {
      cy.get('[data-cy="view-activities-button"]').click()
      cy.waitForLivewire()
      
      cy.url().should('include', '/viticulturist/digital-notebook')
    })

    it('should navigate to create treatment from quick actions', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="create-treatment-button"]').length > 0) {
          cy.get('[data-cy="create-treatment-button"]').click()
          cy.waitForLivewire()
          
          cy.url().should('include', '/viticulturist/digital-notebook/treatment/create')
        } else {
          cy.log('Create treatment button not available - may not have permissions')
        }
      })
    })

    it('should navigate to create fertilization from quick actions', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="create-fertilization-button"]').length > 0) {
          cy.get('[data-cy="create-fertilization-button"]').click()
          cy.waitForLivewire()
          
          cy.url().should('include', '/viticulturist/digital-notebook/fertilization/create')
        } else {
          cy.log('Create fertilization button not available - may not have permissions')
        }
      })
    })

    it('should display recent activities if available', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="recent-activities"]').length > 0) {
          cy.get('[data-cy="recent-activities"]').should('be.visible')
          cy.get('[data-cy="view-all-activities-link"]').should('be.visible')
          
          // Click view all activities
          cy.get('[data-cy="view-all-activities-link"]').click()
          cy.waitForLivewire()
          
          cy.url().should('include', '/viticulturist/digital-notebook')
        } else {
          // Should show no activities message
          cy.get('[data-cy="no-activities-message"]').should('be.visible')
          cy.contains('No hay actividades registradas').should('be.visible')
        }
      })
    })

    it('should navigate to register first activity if no activities', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="register-first-activity-button"]').length > 0) {
          cy.get('[data-cy="register-first-activity-button"]').click()
          cy.waitForLivewire()
          
          cy.url().should('include', '/viticulturist/digital-notebook/treatment/create')
        } else {
          cy.log('Register first activity button not available - campaign has activities or no permissions')
        }
      })
    })
  })
})
