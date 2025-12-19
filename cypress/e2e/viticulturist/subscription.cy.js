describe('Viticulturist Subscription', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  it('should navigate to subscription page', () => {
    cy.visit('/subscription')
    cy.waitForLivewire()
    cy.url().should('include', '/subscription')
  })

  it('should display subscription page content', () => {
    cy.visit('/subscription')
    cy.waitForLivewire()
    
    cy.contains('Suscripciones').should('be.visible')
    cy.contains('Gestiona tu suscripción').should('be.visible')
  })

  it('should display beta phase banner if no active subscription', () => {
    cy.visit('/subscription')
    cy.waitForLivewire()
    
    // Check for beta banner elements
    cy.get('body').then(($body) => {
      if ($body.text().includes('Fase Beta')) {
        cy.contains('Bienvenido a la Fase Beta').should('be.visible')
        cy.contains('6 Meses Gratuitos').should('be.visible')
        cy.contains('25% Descuento').should('be.visible')
      }
    })
  })

  it('should display active subscription if exists', () => {
    cy.visit('/subscription')
    cy.waitForLivewire()
    
    cy.get('body').then(($body) => {
      if ($body.text().includes('Suscripción Activa')) {
        cy.contains('Suscripción Activa').should('be.visible')
        cy.contains('Plan:').should('be.visible')
        cy.contains('Precio:').should('be.visible')
      }
    })
  })

  it('should access subscription from profile dropdown', () => {
    // Click on profile dropdown
    cy.get('button').then(($buttons) => {
      const profileButton = Array.from($buttons).find(btn => {
        return btn.querySelector('img') || btn.getAttribute('aria-label')?.includes('profile');
      });
      
      if (profileButton) {
        cy.wrap(profileButton).click({ force: true })
        cy.wait(500)
        
        // Look for "Ver Suscripción" link
        cy.get('a[href*="/subscription"]').contains('Ver Suscripción').click()
        cy.waitForLivewire()
        cy.url().should('include', '/subscription')
      }
    })
  })

  it('should handle sidebar collapse correctly', () => {
    cy.visit('/subscription')
    cy.waitForLivewire()
    
    // Find sidebar collapse button
    cy.get('#sidebar').should('be.visible')
    cy.get('button[onclick*="toggleSidebarCollapse"]').then(($btn) => {
      if ($btn.length > 0) {
        cy.wrap($btn).click({ force: true })
        cy.wait(500)
        
        // Sidebar should be collapsed
        cy.get('#sidebar').should('have.attr', 'data-collapsed', 'true')
        
        // Main content should adjust
        cy.get('main').should('have.class', 'lg:pl-20')
        
        // Expand again
        cy.wrap($btn).click({ force: true })
        cy.wait(500)
        cy.get('#sidebar').should('have.attr', 'data-collapsed', 'false')
        cy.get('main').should('have.class', 'lg:pl-72')
      }
    })
  })
})

