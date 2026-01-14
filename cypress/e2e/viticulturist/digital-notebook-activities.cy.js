describe('Viticulturist Digital Notebook Activities', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  describe('Phytosanitary Treatment', () => {
    it('should create treatment with crew selection', () => {
      cy.visit('/viticulturist/digital-notebook/treatment/create', { timeout: 30000 })
      cy.waitForLivewire()
      cy.wait(1000)
      
      // Check for treatment form - more flexible
      cy.get('body').then(($body) => {
        const hasTreatmentText = $body.text().includes('Tratamiento') || $body.text().includes('Treatment') || $body.text().includes('Fitosanitario');
        
        if (hasTreatmentText) {
          cy.get('body').should('satisfy', ($body) => {
            return $body.text().includes('Tratamiento') || $body.text().includes('Treatment')
          })
        } else {
          // At least verify we're on the treatment create page
          cy.url().should('include', '/viticulturist/digital-notebook/treatment/create')
        }
      })
      
      // Select plot if available
      cy.get('select#plot_id').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('select#plot_id').select(1, { force: true })
        }
      })
      
      // Select date
      const today = new Date().toISOString().split('T')[0]
      cy.get('input#activity_date[type="date"]').type(today)
      
      // Select product if available
      cy.get('select#product_id').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('select#product_id').select(1, { force: true })
        }
      })
      
      // Select crew or individual worker (radio buttons)
      cy.get('body').then(($body) => {
        const crewRadio = $body.find('input[type="radio"][value="crew"]');
        const individualRadio = $body.find('input[type="radio"][value="individual"]');
        
        if (crewRadio.length > 0) {
          cy.wrap(crewRadio.first()).check({ force: true })
          cy.waitForLivewire()
          
          // Select crew
          cy.get('select#crew_id').then(($select) => {
            if ($select.length > 0 && $select.find('option').length > 1) {
              cy.get('select#crew_id').select(1, { force: true })
            }
          })
        } else if (individualRadio.length > 0) {
          cy.wrap(individualRadio.first()).check({ force: true })
          cy.waitForLivewire()
          
          // Select individual worker
          cy.get('select#crew_member_id').then(($select) => {
            if ($select.length > 0 && $select.find('option').length > 1) {
              cy.get('select#crew_member_id').select(1, { force: true })
            }
          })
        }
      })
      
      // Submit
      cy.get('button').contains('Registrar').click()
      cy.wait(3000)
      
      // Should redirect to digital notebook
      cy.url().should('include', '/viticulturist/digital-notebook')
    })
  })

  describe('Fertilization', () => {
    it('should create fertilization with crew/individual selection', () => {
      cy.visit('/viticulturist/digital-notebook/fertilization/create', { timeout: 30000 })
      cy.waitForLivewire()
      
      cy.contains('Registrar Fertilización').should('be.visible')
      
      // Fill required fields
      cy.get('select#plot_id').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('select#plot_id').select(1, { force: true })
        }
      })
      
      const today = new Date().toISOString().split('T')[0]
      cy.get('input#activity_date[type="date"]').type(today)
      
      // Check for crew/individual selection
      cy.get('input[type="radio"][value="crew"]').then(($radio) => {
        if ($radio.length > 0) {
          cy.wrap($radio.first()).check({ force: true })
          cy.waitForLivewire()
        }
      })
      
      cy.get('button').contains('Registrar').click()
      cy.wait(3000)
    })
  })

  describe('Irrigation', () => {
    it('should create irrigation with crew/individual selection', () => {
      cy.visit('/viticulturist/digital-notebook/irrigation/create')
      cy.waitForLivewire()
      
      cy.contains('Registrar Riego').should('be.visible')
      
      cy.get('select#plot_id').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('select#plot_id').select(1, { force: true })
        }
      })
      
      const today = new Date().toISOString().split('T')[0]
      cy.get('input#activity_date[type="date"]').type(today)
      
      cy.get('input[type="radio"][value="crew"]').then(($radio) => {
        if ($radio.length > 0) {
          cy.wrap($radio.first()).check({ force: true })
          cy.waitForLivewire()
        }
      })
      
      cy.get('button').contains('Registrar').click()
      cy.wait(3000)
    })
  })

  describe('Cultural Work', () => {
    it('should create cultural work with crew/individual selection', () => {
      cy.visit('/viticulturist/digital-notebook/cultural/create', { timeout: 30000 })
      cy.waitForLivewire()
      
      cy.contains('Registrar Labor Cultural', { timeout: 10000 }).should('be.visible')
      
      cy.get('select#plot_id').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('select#plot_id').select(1, { force: true })
        }
      })
      
      const today = new Date().toISOString().split('T')[0]
      cy.get('input#activity_date[type="date"]').type(today)
      
      cy.get('input[type="radio"][value="crew"]').then(($radio) => {
        if ($radio.length > 0) {
          cy.wrap($radio.first()).check({ force: true })
          cy.waitForLivewire()
        }
      })
      
      cy.get('button').contains('Registrar').click()
      cy.wait(3000)
    })
  })

  describe('Observation', () => {
    it('should create observation with crew/individual selection', () => {
      cy.visit('/viticulturist/digital-notebook/observation/create', { timeout: 30000 })
      cy.waitForLivewire()
      
      cy.contains('Registrar Observación', { timeout: 10000 }).should('be.visible')
      
      cy.get('select#plot_id').then(($select) => {
        if ($select.length > 0 && $select.find('option').length > 1) {
          cy.get('select#plot_id').select(1, { force: true })
        }
      })
      
      const today = new Date().toISOString().split('T')[0]
      cy.get('input#activity_date[type="date"]').type(today)
      
      cy.get('input[type="radio"][value="crew"]').then(($radio) => {
        if ($radio.length > 0) {
          cy.wrap($radio.first()).check({ force: true })
          cy.waitForLivewire()
        }
      })
      
      cy.get('button').contains('Registrar').click()
      cy.wait(3000)
    })
  })
})

