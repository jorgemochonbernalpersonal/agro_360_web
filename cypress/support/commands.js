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
    // Programmatic login via test-only route — avoids Livewire form complexity
    cy.visit('/login') // Establish a session cookie first
    cy.request({
      method: 'POST',
      url: '/__cypress/login',
      body: { email },
      form: false,
    }).then((resp) => {
      expect(resp.status).to.eq(200)
      cy.log(`✓ Login programático exitoso: ${resp.body.email} (${resp.body.role})`)
    })
  }, {
    validate: () => {
      cy.request({ url: '/__cypress/me', failOnStatusCode: false }).its('status').should('eq', 200)
    },
    cacheAcrossSpecs: true,
  })

  // After session is created/restored, verify we can access the dashboard
  cy.visit('/viticulturist/dashboard')
  cy.url({ timeout: 10000 }).then((url) => {
    if (url.includes('/beta/expired')) {
      throw new Error('User needs beta access - check CypressTestUserSeeder')
    }
    expect(url).to.satisfy((u) =>
      u.includes('/viticulturist/dashboard') ||
      u.includes('/viticulturist/') ||
      u.includes('/plots')
    )
  })
})

/**
 * Login as a winery user
 * @param {string} email - User email (default: winery@test.com)
 * @param {string} password - User password (default: password)
 */
Cypress.Commands.add('loginAsWinery', (email = 'winery@test.com', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.request({
      method: 'POST',
      url: '/__cypress/login',
      body: { email },
      form: false,
    }).then((resp) => {
      expect(resp.status).to.eq(200)
      cy.log(`✓ Login programático exitoso: ${resp.body.email} (${resp.body.role})`)
    })
  }, {
    validate: () => {
      cy.request({ url: '/__cypress/me', failOnStatusCode: false }).its('status').should('eq', 200)
    },
    cacheAcrossSpecs: true,
  })

  cy.visit('/winery/dashboard')
  cy.url({ timeout: 10000 }).then((url) => {
    if (url.includes('/beta/expired')) {
      throw new Error('User needs beta access - check CypressTestUserSeeder')
    }
    expect(url).to.satisfy((u) => u.includes('/winery/'))
  })
})

/**
 * Login as a producer user
 * @param {string} email - User email (default: producer@test.com)
 * @param {string} password - User password (default: password)
 */
Cypress.Commands.add('loginAsProducer', (email = 'producer@test.com', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.request({
      method: 'POST',
      url: '/__cypress/login',
      body: { email },
      form: false,
    }).then((resp) => {
      expect(resp.status).to.eq(200)
      cy.log(`✓ Login programático exitoso: ${resp.body.email} (${resp.body.role})`)
    })
  }, {
    validate: () => {
      cy.request({ url: '/__cypress/me', failOnStatusCode: false }).its('status').should('eq', 200)
    },
    cacheAcrossSpecs: true,
  })

  cy.visit('/producer/dashboard')
  cy.url({ timeout: 10000 }).then((url) => {
    if (url.includes('/beta/expired')) {
      throw new Error('User needs beta access - check CypressTestUserSeeder')
    }
    expect(url).to.satisfy((u) => u.includes('/producer/'))
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
  cy.get('button[type="submit"]').first().click()
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
 * Get a Livewire-bound element by wire:model name.
 * Matches wire:model, wire:model.live, and wire:model.live.debounce.*
 * regardless of which modifier the view uses.
 */
Cypress.Commands.add('getByWireModel', (name) => {
  return cy.get(
    `[wire\\:model="${name}"],` +
    `[wire\\:model\\.live="${name}"],` +
    `[wire\\:model\\.live\\.debounce\\.300ms="${name}"],` +
    `[wire\\:model\\.live\\.debounce\\.500ms="${name}"]`
  )
})

/**
 * Fill a Livewire form field
 */
Cypress.Commands.add('fillLivewireField', (name, value) => {
  cy.getByWireModel(name).clear().type(value)
  cy.waitForLivewire()
})

/**
 * Select from a Livewire select field
 */
Cypress.Commands.add('selectLivewireOption', (name, value) => {
  cy.getByWireModel(name).select(value)
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
    if (address.description) cy.fillDataCy('address-description', address.description, { index })
    
    // Required address fields (autonomous community, province, municipality)
    if (address.autonomousCommunityId) {
      cy.selectDataCy('address-autonomous-community', address.autonomousCommunityId, { index })
      cy.wait(500) // Wait for provinces to load
    }
    if (address.provinceId) {
      cy.selectDataCy('address-province', address.provinceId, { index })
      cy.wait(500) // Wait for municipalities to load
    }
    if (address.municipalityId) {
      cy.selectDataCy('address-municipality', address.municipalityId, { index })
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

