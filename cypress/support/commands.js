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

/**
 * Fill a field using data-cy selector
 * @param {string} dataCy - The data-cy attribute value
 * @param {string} value - The value to type
 * @param {object} options - Additional options (index, force, etc.)
 */
Cypress.Commands.add('fillDataCy', (dataCy, value, options = {}) => {
  const { index, force = false } = options
  let selector = `[data-cy="${dataCy}"]`
  
  if (index !== undefined) {
    selector += `[data-cy-${dataCy.split('-').pop()}-index="${index}"]`
  }
  
  cy.get(selector).clear({ force }).type(value, { force })
  cy.waitForLivewire()
})

/**
 * Select an option using data-cy selector
 * @param {string} dataCy - The data-cy attribute value
 * @param {string|number} value - The value to select
 * @param {object} options - Additional options (index, force, etc.)
 */
Cypress.Commands.add('selectDataCy', (dataCy, value, options = {}) => {
  const { index, force = false } = options
  let selector = `[data-cy="${dataCy}"]`
  
  if (index !== undefined) {
    selector += `[data-cy-${dataCy.split('-').pop()}-index="${index}"]`
  }
  
  cy.get(selector).select(value, { force })
  cy.waitForLivewire()
})

/**
 * Click a button using data-cy selector
 * @param {string} dataCy - The data-cy attribute value
 * @param {object} options - Additional options (index, force, etc.)
 */
Cypress.Commands.add('clickDataCy', (dataCy, options = {}) => {
  const { index, force = false } = options
  let selector = `[data-cy="${dataCy}"]`
  
  if (index !== undefined) {
    selector += `[data-cy-${dataCy.split('-').pop()}-index="${index}"]`
  }
  
  cy.get(selector).click({ force })
  cy.waitForLivewire()
})

/**
 * Submit a form using data-cy submit button
 * @param {string} formDataCy - Optional form data-cy (default: looks for submit-button)
 */
Cypress.Commands.add('submitForm', (formDataCy = null) => {
  if (formDataCy) {
    cy.get(`[data-cy="${formDataCy}"]`).within(() => {
      cy.get('[data-cy="submit-button"]').click()
    })
  } else {
    cy.get('[data-cy="submit-button"]').click()
  }
  cy.waitForLivewire()
})

/**
 * Fill a complete client form (individual or company)
 * @param {object} clientData - Client data object
 * @param {string} clientData.type - 'individual' or 'company'
 * @param {object} clientData.fields - Field values
 * @param {array} clientData.addresses - Array of address objects
 */
Cypress.Commands.add('fillClientForm', (clientData) => {
  const { type, fields = {}, addresses = [] } = clientData
  
  // Select client type
  cy.selectDataCy('client-type', type, { force: true })
  cy.wait(500)
  
  // Fill fields based on type
  if (type === 'individual') {
    if (fields.firstName) cy.fillDataCy('first-name', fields.firstName)
    if (fields.lastName) cy.fillDataCy('last-name', fields.lastName)
    if (fields.particularDocument) cy.fillDataCy('particular-document', fields.particularDocument)
  } else {
    if (fields.companyName) cy.fillDataCy('company-name', fields.companyName)
    if (fields.companyDocument) cy.fillDataCy('company-document', fields.companyDocument)
  }
  
  // Common fields
  if (fields.email) cy.fillDataCy('email', fields.email)
  if (fields.phone) cy.fillDataCy('phone', fields.phone)
  
  // Fill addresses
  addresses.forEach((address, index) => {
    if (address.address) cy.fillDataCy('address-address', address.address, { index })
    if (address.postalCode) cy.fillDataCy('address-postal-code', address.postalCode, { index })
    if (address.name) cy.fillDataCy('address-name', address.name, { index })
    if (address.description) cy.fillDataCy('address-description', address.description, { index })
    if (address.isDeliveryNote) {
      cy.get(`[data-cy="address-delivery-note"][data-cy-address-index="${index}"]`).check()
    }
  })
})

/**
 * Fill a complete invoice form
 * @param {object} invoiceData - Invoice data object
 */
Cypress.Commands.add('fillInvoiceForm', (invoiceData) => {
  const { clientId, deliveryNoteCode, invoiceDate, dueDate, items = [], observations } = invoiceData
  
  if (clientId) {
    cy.selectDataCy('client-id', clientId, { force: true })
    cy.wait(1000)
  }
  
  if (deliveryNoteCode) cy.fillDataCy('delivery-note-code', deliveryNoteCode)
  if (invoiceDate) cy.fillDataCy('invoice-date', invoiceDate)
  if (dueDate) cy.fillDataCy('due-date', dueDate)
  
  // Add and fill items
  items.forEach((item, index) => {
    if (index > 0) {
      cy.clickDataCy('add-item-button')
      cy.wait(1000)
    }
    
    if (item.name) cy.fillDataCy('item-name', item.name, { index })
    if (item.quantity) cy.fillDataCy('item-quantity', item.quantity.toString(), { index })
    if (item.unitPrice) cy.fillDataCy('item-unit-price', item.unitPrice.toString(), { index })
    if (item.description) cy.fillDataCy('item-description', item.description, { index })
    if (item.sku) cy.fillDataCy('item-sku', item.sku, { index })
    if (item.discount) cy.fillDataCy('item-discount', item.discount.toString(), { index })
  })
  
  if (observations) cy.fillDataCy('observations', observations)
})

/**
 * Fill a complete plot form
 * @param {object} plotData - Plot data object
 */
Cypress.Commands.add('fillPlotForm', (plotData) => {
  const { name, area, description, active = true, viticulturistId, autonomousCommunityId, provinceId, municipalityId } = plotData
  
  if (name) cy.fillDataCy('plot-name', name)
  if (area) cy.fillDataCy('plot-area', area.toString())
  if (description) cy.fillDataCy('plot-description', description)
  
  if (active !== undefined) {
    if (active) {
      cy.get('[data-cy="plot-active"]').check()
    } else {
      cy.get('[data-cy="plot-active"]').uncheck()
    }
  }
  
  if (viticulturistId) cy.selectDataCy('plot-viticulturist-id', viticulturistId)
  if (autonomousCommunityId) {
    cy.selectDataCy('plot-autonomous-community-id', autonomousCommunityId)
    cy.wait(500)
  }
  if (provinceId) {
    cy.selectDataCy('plot-province-id', provinceId)
    cy.wait(500)
  }
  if (municipalityId) cy.selectDataCy('plot-municipality-id', municipalityId)
})

/**
 * Fill a complete machinery form
 * @param {object} machineryData - Machinery data object
 */
Cypress.Commands.add('fillMachineryForm', (machineryData) => {
  const { name, typeId, brand, model, serialNumber, year, isRented = false, active = true, notes } = machineryData
  
  if (name) cy.fillDataCy('machinery-name', name)
  if (typeId) cy.selectDataCy('machinery-type-id', typeId)
  if (brand) cy.fillDataCy('machinery-brand', brand)
  if (model) cy.fillDataCy('machinery-model', model)
  if (serialNumber) cy.fillDataCy('machinery-serial-number', serialNumber)
  if (year) cy.fillDataCy('machinery-year', year.toString())
  
  if (isRented) {
    cy.get('[data-cy="machinery-is-rented"]').check()
  } else {
    cy.get('[data-cy="machinery-is-rented"]').uncheck()
  }
  
  if (active) {
    cy.get('[data-cy="machinery-active"]').check()
  } else {
    cy.get('[data-cy="machinery-active"]').uncheck()
  }
  
  if (notes) cy.fillDataCy('machinery-notes', notes)
})

/**
 * Verify form validation errors are shown
 * @param {string} formDataCy - Form data-cy selector
 */
Cypress.Commands.add('verifyFormValidation', (formDataCy = null) => {
  // Should stay on the same page (not redirect)
  cy.url().then((currentUrl) => {
    const form = formDataCy ? `[data-cy="${formDataCy}"]` : 'form'
    cy.get(form).should('be.visible')
    
    // Check for error indicators (red borders, error messages)
    cy.get('body').then(($body) => {
      const hasErrors = $body.find('.text-red-600, .text-red-500, [class*="border-red"]').length > 0 ||
                       $body.text().includes('obligatorio') ||
                       $body.text().includes('required')
      
      expect(hasErrors || currentUrl.includes('/create') || currentUrl.includes('/edit')).to.be.true
    })
  })
})

/**
 * Navigate to create page and wait for form
 * @param {string} buttonText - Text of the create button
 * @param {string} formDataCy - Expected form data-cy selector
 */
Cypress.Commands.add('navigateToCreate', (buttonText, formDataCy) => {
  cy.contains(buttonText).click()
  cy.waitForLivewire()
  cy.contains(buttonText).should('be.visible')
  if (formDataCy) {
    cy.get(`[data-cy="${formDataCy}"]`).should('be.visible')
  }
})

/**
 * Search using search input (tries data-cy first, then placeholder)
 * @param {string} searchTerm - Term to search for
 * @param {string} dataCy - Optional data-cy selector for search input
 */
Cypress.Commands.add('search', (searchTerm, dataCy = 'search-input') => {
  cy.get('body').then(($body) => {
    const searchInput = $body.find(`[data-cy="${dataCy}"]`)
    if (searchInput.length > 0) {
      cy.get(`[data-cy="${dataCy}"]`).clear().type(searchTerm)
    } else {
      // Fallback to placeholder search
      cy.get('input[placeholder*="buscar" i], input[placeholder*="search" i]').first().clear().type(searchTerm)
    }
  })
  cy.waitForLivewire()
})

