// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

/**
 * Login as a viticulturist user
 * @param {string} email - User email
 * @param {string} password - User password
 */
Cypress.Commands.add('loginAsViticulturist', (email = 'viticulturist@example.com', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.get('input[wire\\:model="email"]').clear().type(email)
    cy.get('input[wire\\:model="password"]').clear().type(password)
    cy.get('button[type="submit"]').click()
    
    // Wait for Livewire to process the request
    cy.wait(3000)
    
    // Verify we're on the dashboard (or handle error if credentials are wrong)
    cy.url().then((url) => {
      if (url.includes('/login')) {
        // Still on login, might be credentials issue
        cy.log('⚠ Warning: Still on login page. User may not exist. Run: php artisan db:seed --class=ViticulturistTestUserSeeder')
      } else {
        cy.url().should('include', '/viticulturist/dashboard')
      }
    })
  })
  
  // After session is created, visit dashboard to ensure we're logged in
  cy.visit('/viticulturist/dashboard')
  cy.url().should('include', '/viticulturist/dashboard')
})

/**
 * Login as a specific user type
 * @param {string} role - User role (admin, supervisor, winery, viticulturist)
 * @param {string} email - User email
 * @param {string} password - User password
 */
Cypress.Commands.add('loginAs', (role, email, password) => {
  cy.visit('/login')
  cy.get('input[wire\\:model="email"]').type(email)
  cy.get('input[wire\\:model="password"]').type(password)
  cy.get('button[type="submit"]').click()
  cy.waitForLivewire()
  cy.url().should('include', `/${role}/dashboard`)
})

/**
 * Wait for Livewire to finish loading
 */
Cypress.Commands.add('waitForLivewire', () => {
  cy.window().then((win) => {
    return new Cypress.Promise((resolve) => {
      if (win.Livewire) {
        // Wait for Livewire to be ready
        cy.wait(500)
        resolve()
      } else {
        resolve()
      }
    })
  })
})

/**
 * Click a Livewire button and wait for response
 */
Cypress.Commands.add('clickLivewire', (selector) => {
  cy.get(selector).click()
  cy.waitForLivewire()
})

/**
 * Fill a Livewire form field
 */
Cypress.Commands.add('fillLivewireField', (name, value) => {
  cy.get(`[wire\\:model="${name}"]`).clear().type(value)
  cy.waitForLivewire()
})

/**
 * Select from a Livewire select field
 */
Cypress.Commands.add('selectLivewireOption', (name, value) => {
  cy.get(`[wire\\:model="${name}"]`).select(value)
  cy.waitForLivewire()
})

/**
 * Navigate to a route and wait for Livewire
 */
Cypress.Commands.add('visitRoute', (routeName, params = {}) => {
  cy.window().then((win) => {
    const url = win.route(routeName, params)
    cy.visit(url)
    cy.waitForLivewire()
  })
})

/**
 * Check for flash messages
 */
Cypress.Commands.add('shouldSeeFlashMessage', (message) => {
  cy.get('.glass-card').contains(message).should('be.visible')
})

/**
 * Check for error messages
 */
Cypress.Commands.add('shouldSeeError', (field, message) => {
  cy.get(`[name="${field}"]`).parent().contains(message).should('be.visible')
})

