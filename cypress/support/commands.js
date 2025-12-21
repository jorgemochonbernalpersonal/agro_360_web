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
 * Usa usuarios genéricos creados en la BD de test por CypressTestUserSeeder
 * @param {string} email - User email (default: viticulturist@test.com)
 * @param {string} password - User password (default: password)
 */
Cypress.Commands.add('loginAsViticulturist', (email = 'viticulturist@test.com', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/login')
    
    // Wait for page to load completely
    cy.get('input[wire\\:model="email"]').should('be.visible')
    cy.wait(500) // Wait for any overlays/tooltips to disappear
    
    // Clear and type email - use force if needed to bypass overlays
    cy.get('input[wire\\:model="email"]').clear({ force: true }).type(email, { force: true })
    cy.wait(200)
    
    // Clear and type password - use force if needed
    cy.get('input[wire\\:model="password"]').clear({ force: true }).type(password, { force: true })
    cy.wait(200)
    
    // Click submit button
    cy.get('button[type="submit"]').click({ force: true })
    
    // Wait for Livewire to process the request
    cy.wait(3000) // Give Livewire time to process
    
    // Wait for URL to change from /login to dashboard
    cy.url({ timeout: 15000 }).then((url) => {
      // Check if we're no longer on login page
      if (url.includes('/login')) {
        // Still on login - might be an error, check for error messages
        cy.get('body').then(($body) => {
          if ($body.text().includes('credenciales') || $body.text().includes('error')) {
            throw new Error('Login failed - invalid credentials or user does not exist')
          }
        })
      } else if (url.includes('/beta/expired')) {
        // Beta expired - user needs beta access
        throw new Error('User beta access expired - check CypressTestUserSeeder grants beta access')
      } else {
        // Should be on dashboard (or any valid route after login)
        // Accept dashboard or any viticulturist route
        if (url.includes('/viticulturist/') || url.includes('/plots') || url.includes('/dashboard')) {
          cy.log('✓ Login successful')
        } else {
          cy.log(`⚠ Unexpected URL after login: ${url}`)
        }
      }
    })
  })
  
  // After session is created, visit dashboard to ensure we're logged in
  cy.visit('/viticulturist/dashboard')
  cy.url({ timeout: 10000 }).then((url) => {
    // Accept dashboard or redirect to beta expired (which means user needs beta access)
    if (url.includes('/beta/expired')) {
      throw new Error('User needs beta access - check CypressTestUserSeeder')
    }
    // Should be on dashboard or any valid route
    expect(url).to.satisfy((u) => 
      u.includes('/viticulturist/dashboard') || 
      u.includes('/viticulturist/') ||
      u.includes('/plots')
    )
  })
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
 * Check for flash messages (legacy - now uses toasts)
 */
Cypress.Commands.add('shouldSeeFlashMessage', (message) => {
  // Check for toast notification
  cy.get('body').should('contain.text', message)
})

/**
 * Check for toast notification
 */
Cypress.Commands.add('shouldSeeToast', (message, type = 'success') => {
  cy.get('body').then(($body) => {
    const toastContainer = $body.find('[x-data*="toastNotifications"]');
    if (toastContainer.length > 0) {
      cy.get('body').should('contain.text', message)
      
      // Check toast type if specified
      if (type) {
        const toast = toastContainer.find(`[class*="${type}"]`);
        expect(toast.length).to.be.greaterThan(0)
      }
    } else {
      // Fallback: just check body contains message
      cy.get('body').should('contain.text', message)
    }
  })
})

/**
 * Check for error messages
 */
Cypress.Commands.add('shouldSeeError', (field, message) => {
  cy.get(`[name="${field}"]`).parent().contains(message).should('be.visible')
})

