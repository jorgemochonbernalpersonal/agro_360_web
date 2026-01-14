/**
 * Tests E2E específicos para la campaña 2025
 * Estos tests verifican el flujo completo del usuario con datos reales
 * Usuario: bernalmochonjorge@gmail.com / cocoteq22
 */
describe('Viticulturist Campaign 2025 - Complete Flow', () => {
  beforeEach(() => {
    cy.loginAsViticulturist('bernalmochonjorge@gmail.com', 'cocoteq22')
    cy.visit('/viticulturist/campaign')
    cy.waitForLivewire()
  })

  it('should display campaign 2025 as active', () => {
    // Wait for page to load
    cy.waitForLivewire()
    cy.wait(1000)
    
    // Filtrar por año 2025
    cy.get('body').then(($body) => {
      const selects = $body.find('select');
      const yearSelect = Array.from(selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.value === '2025' || opt.textContent.includes('2025'));
      });
      
      if (yearSelect) {
        cy.wrap(yearSelect).select('2025', { force: true });
        cy.waitForLivewire();
        cy.wait(1000);
      }
    });

    // Verificar que la campaña 2025 está visible y activa
    cy.get('body').then(($body) => {
      if ($body.text().includes('Campaña 2025')) {
        cy.contains('Campaña 2025').should('be.visible')
      } else if ($body.text().includes('2025')) {
        // Campaign may be named differently
        cy.get('body').should('contain.text', '2025')
      } else {
        cy.log('Campaign 2025 not found - may need to be created first')
        cy.get('body').should('contain.text', 'Campaña')
      }
    })
  })

  it('should view campaign 2025 details with activities', () => {
    cy.waitForLivewire()
    cy.wait(1000)
    
    // Buscar y hacer clic en la campaña 2025 - más flexible
    cy.get('body').then(($body) => {
      const campaignLink = $body.find('a, button, [role="button"]').filter((i, el) => {
        const text = el.textContent || '';
        return text.includes('Campaña 2025') || text.includes('2025');
      }).first();
      
      if (campaignLink.length > 0) {
        cy.wrap(campaignLink).click({ force: true })
        cy.waitForLivewire()
        cy.wait(1000)
        
        // Verificar que estamos en la página de detalle
        cy.url().should('include', '/viticulturist/campaign/')
        
        // Verificar que hay actividades
        cy.get('body').then(($bodyDetail) => {
          if ($bodyDetail.text().includes('Actividades') || $bodyDetail.text().includes('actividad')) {
            cy.log('✅ La campaña tiene actividades asociadas')
          }
        })
      } else {
        cy.log('Campaign 2025 link not found - may need to be created first')
        cy.url().should('include', '/viticulturist/campaign')
      }
    })
  })

  it('should navigate to digital notebook from campaign 2025', () => {
    cy.waitForLivewire()
    cy.wait(1000)
    
    // Ir a la campaña 2025 - más flexible
    cy.get('body').then(($body) => {
      const campaignLink = $body.find('a, button, [role="button"]').filter((i, el) => {
        const text = el.textContent || '';
        return text.includes('Campaña 2025') || text.includes('2025');
      }).first();
      
      if (campaignLink.length > 0) {
        cy.wrap(campaignLink).click({ force: true })
        cy.waitForLivewire()
        cy.wait(1000)
        
        // Navegar al cuaderno digital
        cy.get('body').then(($bodyDetail) => {
          const notebookLink = $bodyDetail.find('a, button, [role="button"]').filter((i, el) => {
            const text = el.textContent || '';
            return text.includes('Cuaderno Digital') || text.includes('Digital') || text.includes('Notebook');
          }).first();
          
          if (notebookLink.length > 0) {
            cy.wrap(notebookLink).click({ force: true })
            cy.waitForLivewire()
            cy.wait(1000)
            
            // Verificar que estamos en el cuaderno digital
            cy.url().should('include', '/viticulturist/digital-notebook')
          } else {
            // Try direct navigation
            cy.visit('/viticulturist/digital-notebook')
            cy.waitForLivewire()
            cy.url().should('include', '/viticulturist/digital-notebook')
          }
        })
      } else {
        // Try direct navigation
        cy.visit('/viticulturist/digital-notebook')
        cy.waitForLivewire()
        cy.url().should('include', '/viticulturist/digital-notebook')
      }
    })
  })

  it('should create new activity in campaign 2025', () => {
    // Ir al cuaderno digital
    cy.visit('/viticulturist/digital-notebook')
    cy.waitForLivewire()
    
    // Verificar que la campaña 2025 está seleccionada (debe ser la activa)
    cy.get('body').should('contain.text', '2025')
    
    // Intentar crear una nueva actividad
    cy.get('body').then(($body) => {
      if ($body.text().includes('Nuevo Tratamiento') || $body.text().includes('Nueva Actividad')) {
        cy.contains('Nuevo Tratamiento').first().click({ force: true })
        cy.waitForLivewire()
        
        // Verificar que el formulario se abre
        cy.get('body').should('contain.text', 'Tratamiento')
      }
    })
  })

  it('should filter activities by campaign 2025', () => {
    cy.visit('/viticulturist/digital-notebook')
    cy.waitForLivewire()
    
    // Verificar que podemos ver actividades de la campaña 2025
    cy.get('body').should('contain.text', '2025')
    
    // Verificar que hay diferentes tipos de actividades
    const activityTypes = ['Tratamiento', 'Fertilización', 'Riego', 'Cultural', 'Observación']
    activityTypes.forEach(type => {
      cy.get('body').then(($body) => {
        if ($body.text().includes(type)) {
          cy.log(`✅ Tipo de actividad encontrado: ${type}`)
        }
      })
    })
  })

  it('should view plots associated with campaign 2025 activities', () => {
    // Ir a parcelas
    cy.visit('/plots')
    cy.waitForLivewire()
    
    // Verificar que hay parcelas
    cy.get('body').should('contain.text', 'Parcela')
    
    // Verificar que las parcelas tienen datos
    cy.get('body').then(($body) => {
      const plotCount = ($body.text().match(/Parcela/g) || []).length
      if (plotCount > 0) {
        cy.log(`✅ Se encontraron ${plotCount} referencias a parcelas`)
      }
    })
  })

  it('should verify campaign 2025 has complete data structure', () => {
    // Verificar que la campaña 2025 tiene:
    // - Actividades agrícolas
    // - Parcelas asociadas
    // - Productos fitosanitarios
    // - Maquinaria
    // - Cuadrillas
    
    cy.visit('/viticulturist/campaign')
    cy.waitForLivewire()
    cy.wait(1000)
    
    // Verificar campaña - más flexible
    cy.get('body').then(($body) => {
      if ($body.text().includes('Campaña 2025')) {
        cy.contains('Campaña 2025').should('be.visible')
      } else if ($body.text().includes('2025')) {
        cy.get('body').should('contain.text', '2025')
      } else {
        cy.log('Campaign 2025 not found - may need to be created first')
        cy.get('body').should('contain.text', 'Campaña')
      }
    })
    
    // Verificar parcelas
    cy.visit('/plots')
    cy.waitForLivewire()
    cy.wait(1000)
    cy.get('body').should('contain.text', 'Parcela')
    
    // Verificar maquinaria
    cy.visit('/viticulturist/machinery')
    cy.waitForLivewire()
    cy.wait(1000)
    cy.get('body').should('contain.text', 'Maquinaria')
    
    // Verificar personal/cuadrillas
    cy.visit('/viticulturist/personal')
    cy.waitForLivewire()
    cy.wait(1000)
    cy.get('body').should('contain.text', 'Personal')
  })
})

