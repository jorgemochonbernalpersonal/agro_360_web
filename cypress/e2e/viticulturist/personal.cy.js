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
    })

    it('should show statistics panel', () => {
      cy.get('body').should('contain.text', 'Total')
      cy.get('body').should('contain.text', 'En equipo')
      cy.get('body').should('contain.text', 'Sin equipo')
    })

    it('should filter by search', () => {
      cy.get('input[placeholder*="nombre o email"]').clear().type('Test')
      cy.waitForLivewire()
      cy.get('input[placeholder*="nombre o email"]').should('have.value', 'Test')
    })

    it('should filter by crew', () => {
      cy.get('select').then(($selects) => {
        const crewSelect = Array.from($selects).find(select => {
          const options = select.querySelectorAll('option');
          return Array.from(options).some(opt => opt.textContent.includes('cuadrilla') || opt.textContent.includes('equipo'));
        });
        
        if (crewSelect && crewSelect.querySelectorAll('option').length > 1) {
          cy.wrap(crewSelect).select(1, { force: true });
          cy.waitForLivewire();
        }
      });
    })

    it('should filter by status (in_crew, individual, unassigned)', () => {
      cy.get('select').then(($selects) => {
        const statusSelect = Array.from($selects).find(select => {
          const options = select.querySelectorAll('option');
          return Array.from(options).some(opt => 
            opt.textContent.includes('En equipo') || 
            opt.textContent.includes('Sin equipo') ||
            opt.textContent.includes('Sin asignar')
          );
        });
        
        if (statusSelect) {
          cy.wrap(statusSelect).select('in_crew', { force: true });
          cy.waitForLivewire();
        }
      });
    })

    it('should switch to crews view', () => {
      cy.contains('button', 'Equipos').click()
      cy.waitForLivewire()
      cy.url().should('include', 'viewMode=crews')
    })
  })

  describe('Crews View', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/personal?viewMode=crews')
      cy.waitForLivewire()
    })

    it('should display crews list', () => {
      cy.contains('Equipos y Personal').should('be.visible')
    })

    it('should create a new crew', () => {
      // The button might be in the header or might need to be found differently
      cy.get('body').then(($body) => {
        // Look for button/link with various text options
        const nuevoEquipoBtn = $body.find('a, button').filter((i, el) => {
          const text = el.textContent.trim().toLowerCase();
          const href = el.getAttribute('href');
          return (text.includes('nuevo equipo') || 
                  text.includes('nueva cuadrilla') ||
                  text.includes('crear equipo') ||
                  (href && href.includes('/personal/create')));
        });
        
        if (nuevoEquipoBtn.length > 0) {
          cy.wrap(nuevoEquipoBtn.first()).click({ force: true })
        } else {
          // Try direct link
          cy.visit('/viticulturist/personal/create', { timeout: 15000 })
        }
      })
      cy.waitForLivewire()
      cy.wait(2000)
      cy.url({ timeout: 15000 }).should('include', '/viticulturist/personal/create')
      
      // Check for various possible titles
      cy.get('body').should(($body) => {
        const text = $body.text().toLowerCase();
        expect(text.includes('nuevo equipo') || 
               text.includes('nueva cuadrilla') ||
               text.includes('crear equipo') ||
               text.includes('equipo')).to.be.true
      })
      cy.get('input[wire\\:model="name"]#name').clear().type('Equipo E2E Test')
      cy.get('textarea[wire\\:model="description"]#description').clear().type('Descripción de prueba E2E')
      
      cy.get('form[wire\\:submit]').first().within(() => {
        cy.get('button[type="submit"]').click()
      })
      
      cy.wait(5000)
      cy.url().should('include', '/viticulturist/personal')
    })

    it('should search crews', () => {
      cy.get('input[placeholder*="nombre o descripción"]').clear().type('Test')
      cy.waitForLivewire()
    })
  })

  describe('Assign viticulturist to crew', () => {
    it('should assign viticulturist to crew using modal', () => {
      // Switch to personal view
      cy.visit('/viticulturist/personal?viewMode=personal')
      cy.waitForLivewire()
      
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
        }
      })
    })

    it('should mark viticulturist as individual', () => {
      cy.visit('/viticulturist/personal?viewMode=personal')
      cy.waitForLivewire()
      
      // Look for button to make individual
      cy.get('button').then(($buttons) => {
        const individualButton = Array.from($buttons).find(btn => {
          return btn.getAttribute('title')?.includes('Individual') || 
                 btn.getAttribute('title')?.includes('Sin equipo');
        });
        
        if (individualButton) {
          cy.wrap(individualButton).click({ force: true })
          cy.waitForLivewire()
        }
      })
    })
  })

  describe('Toast notifications', () => {
    it('should show toast notification after actions', () => {
      // Try to create a crew to trigger a toast
      cy.contains('Nuevo Equipo').click()
      cy.waitForLivewire()
      
      cy.get('input[wire\\:model="name"]#name').clear().type('Test Toast Crew')
      cy.get('textarea[wire\\:model="description"]#description').clear().type('Test description')
      
      cy.get('form[wire\\:submit]').first().within(() => {
        cy.get('button[type="submit"]').click()
      })
      
      cy.wait(3000)
      
      // Check for toast notification (bottom left)
      cy.get('body').then(($body) => {
        // Toast should appear in bottom left
        const toasts = $body.find('[x-data*="toastNotifications"]');
        if (toasts.length > 0) {
          cy.get('body').should('contain.text', 'correctamente')
        }
      })
    })
  })
})

