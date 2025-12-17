describe('Viticulturist Dashboard', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  it('should display dashboard correctly', () => {
    cy.url().should('include', '/viticulturist/dashboard')
    // Dashboard title should be visible
    cy.get('body').should('be.visible')
  })

  it('should have navigation menu in sidebar', () => {
    // Check for main navigation items in sidebar
    cy.get('#sidebar').should('be.visible')
    cy.get('body').should('contain.text', 'Parcelas')
    cy.get('body').should('contain.text', 'Campaña') // Note: it's "Campaña" not "Campañas" in sidebar
    cy.get('body').should('contain.text', 'Cuaderno Digital')
    cy.get('body').should('contain.text', 'Personal') // Note: it's "Personal" not "Cuadrillas" in sidebar
    cy.get('body').should('contain.text', 'Maquinaria')
  })

  it('should navigate to plots from sidebar', () => {
    cy.get('a[href*="/plots"]').first().click()
    cy.waitForLivewire()
    cy.url().should('include', '/plots')
  })

  it('should navigate to campaigns from sidebar', () => {
    cy.get('a[href*="/viticulturist/campaign"]').first().click()
    cy.waitForLivewire()
    cy.url().should('include', '/viticulturist/campaign')
  })
})

