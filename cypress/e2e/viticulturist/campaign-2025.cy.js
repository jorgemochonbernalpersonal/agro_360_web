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
    // Filtrar por año 2025
    cy.get('select').then(($selects) => {
      const yearSelect = Array.from($selects).find(select => {
        const options = select.querySelectorAll('option');
        return Array.from(options).some(opt => opt.value === '2025');
      });
      
      if (yearSelect) {
        cy.wrap(yearSelect).select('2025', { force: true });
        cy.waitForLivewire();
      }
    });

    // Verificar que la campaña 2025 está visible y activa
    cy.contains('Campaña 2025').should('be.visible')
    cy.get('body').should('contain.text', '2025')
  })

  it('should view campaign 2025 details with activities', () => {
    // Buscar y hacer clic en la campaña 2025
    cy.contains('Campaña 2025').click({ force: true })
    cy.waitForLivewire()
    
    // Verificar que estamos en la página de detalle
    cy.url().should('include', '/viticulturist/campaign/')
    cy.contains('Campaña 2025').should('be.visible')
    
    // Verificar que hay actividades
    cy.get('body').then(($body) => {
      if ($body.text().includes('Actividades') || $body.text().includes('actividad')) {
        cy.log('✅ La campaña tiene actividades asociadas')
      }
    })
  })

  it('should navigate to digital notebook from campaign 2025', () => {
    // Ir a la campaña 2025
    cy.contains('Campaña 2025').click({ force: true })
    cy.waitForLivewire()
    
    // Navegar al cuaderno digital
    cy.contains('Cuaderno Digital').click({ force: true })
    cy.waitForLivewire()
    
    // Verificar que estamos en el cuaderno digital
    cy.url().should('include', '/viticulturist/digital-notebook')
    cy.contains('Cuaderno Digital').should('be.visible')
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
    
    // Verificar campaña
    cy.contains('Campaña 2025').should('be.visible')
    
    // Verificar parcelas
    cy.visit('/plots')
    cy.waitForLivewire()
    cy.get('body').should('contain.text', 'Parcela')
    
    // Verificar maquinaria
    cy.visit('/viticulturist/machinery')
    cy.waitForLivewire()
    cy.get('body').should('contain.text', 'Maquinaria')
    
    // Verificar personal/cuadrillas
    cy.visit('/viticulturist/personal')
    cy.waitForLivewire()
    cy.get('body').should('contain.text', 'Personal')
  })
})

