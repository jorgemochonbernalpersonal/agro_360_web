describe('Viticulturist Dashboard', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
    cy.visit('/viticulturist/dashboard')
    cy.waitForLivewire()
  })

  describe('Dashboard Display', () => {
    it('should display dashboard correctly', () => {
      cy.url().should('include', '/viticulturist/dashboard')
      cy.contains('Dashboard').should('be.visible')
      cy.contains('Gestión operativa de tu viñedo').should('be.visible')
    })

    it('should display all KPI cards', () => {
      cy.get('[data-cy="dashboard-kpi-cards"]').should('be.visible')
      cy.get('[data-cy="kpi-total-plots"]').should('be.visible')
      cy.get('[data-cy="kpi-total-area"]').should('be.visible')
      cy.get('[data-cy="kpi-activities-month"]').should('be.visible')
      cy.get('[data-cy="kpi-total-harvested"]').should('be.visible')
    })

    it('should display operational KPIs', () => {
      cy.get('[data-cy="dashboard-operational-kpis"]').should('be.visible')
      cy.get('[data-cy="kpi-active-treatments"]').should('be.visible')
      cy.get('[data-cy="kpi-available-containers"]').should('be.visible')
      cy.get('[data-cy="dashboard-financial-stats-link"]').should('be.visible')
    })

    it('should display KPI values', () => {
      cy.get('[data-cy="kpi-total-plots"]').should('contain.text', 'Total Parcelas')
      cy.get('[data-cy="kpi-total-area"]').should('contain.text', 'Área Total')
      cy.get('[data-cy="kpi-activities-month"]').should('contain.text', 'Actividades')
      cy.get('[data-cy="kpi-total-harvested"]').should('contain.text', 'Cosechado')
    })

    it('should display charts section', () => {
      cy.get('[data-cy="dashboard-charts"]').should('be.visible')
      cy.get('[data-cy="chart-variety-distribution"]').should('be.visible')
      cy.get('[data-cy="recent-activities-section"]').should('be.visible')
    })

    it('should display recent activities section', () => {
      cy.get('[data-cy="recent-activities-section"]').should('be.visible')
      cy.get('[data-cy="recent-activities-section"]').should('contain.text', 'Actividades Recientes')
      cy.get('[data-cy="view-all-activities-link"]').should('be.visible')
    })

    it('should display recent harvests section', () => {
      cy.get('[data-cy="recent-harvests-section"]').should('be.visible')
      cy.get('[data-cy="recent-harvests-section"]').should('contain.text', 'Cosechas Recientes')
      cy.get('[data-cy="view-all-harvests-link"]').should('be.visible')
    })
  })

  describe('Dashboard Navigation', () => {
    it('should navigate to digital notebook from activities link', () => {
      cy.get('[data-cy="view-all-activities-link"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/digital-notebook')
    })

    it('should navigate to digital notebook from harvests link', () => {
      cy.get('[data-cy="view-all-harvests-link"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/digital-notebook')
    })

    it('should navigate to financial stats', () => {
      cy.get('[data-cy="dashboard-financial-stats-link"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/invoices')
    })
  })

  describe('Dashboard Alerts', () => {
    it('should display alerts if they exist', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="dashboard-alerts"]').length > 0) {
          cy.get('[data-cy="dashboard-alerts"]').should('be.visible')
          cy.get('[data-cy="dashboard-alert-warning"]').should('exist')
        }
      })
    })

    it('should handle alert links', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="dashboard-alerts"]').length > 0) {
          cy.get('[data-cy="dashboard-alerts"]').within(() => {
            cy.get('a').first().then(($link) => {
              if ($link.length > 0) {
                cy.wrap($link).click()
                cy.waitForLivewire()
              }
            })
          })
        }
      })
    })
  })

  describe('Sidebar Navigation', () => {
    it('should display sidebar', () => {
      cy.get('#sidebar').should('be.visible')
      cy.get('[data-cy="sidebar-logo-link"]').should('be.visible')
    })

    it('should have main navigation items', () => {
      cy.get('[data-cy="sidebar-main-section"]').should('be.visible')
      cy.get('[data-cy="sidebar-nav-dashboard"]').should('be.visible')
      cy.get('[data-cy="sidebar-nav-parcelas"]').should('be.visible')
      cy.get('[data-cy="sidebar-nav-cuaderno-digital"]').should('be.visible')
    })

    it('should navigate to dashboard from logo', () => {
      cy.get('[data-cy="sidebar-logo-link"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/dashboard')
    })

    it('should navigate to plots from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-parcelas"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/plots')
    })

    it('should navigate to campaigns from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-campaña"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/campaign')
    })

    it('should navigate to digital notebook from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-cuaderno-digital"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/digital-notebook')
    })

    it('should navigate to personal from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-equipos-y-personal"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/personal')
    })

    it('should navigate to machinery from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-maquinaria"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/machinery')
    })

    it('should navigate to clients from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-clientes"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/clients')
    })

    it('should navigate to invoices from sidebar', () => {
      cy.get('[data-cy="sidebar-nav-facturación"]').click()
      cy.waitForLivewire()
      cy.url().should('include', '/viticulturist/invoices')
    })
  })

  describe('Sidebar Submenus', () => {
    it('should expand submenu when clicking on parent item', () => {
      cy.get('[data-cy="sidebar-nav-parcelas"]').click()
      cy.waitForLivewire()
      
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="sidebar-submenu-parcelas"]').length > 0) {
          cy.get('[data-cy="sidebar-submenu-parcelas"]').should('be.visible')
        }
      })
    })

    it('should navigate to create plot from submenu', () => {
      cy.get('[data-cy="sidebar-nav-parcelas"]').click()
      cy.waitForLivewire()
      
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="sidebar-submenu-item-crear-parcela"]').length > 0) {
          cy.get('[data-cy="sidebar-submenu-item-crear-parcela"]').click()
          cy.waitForLivewire()
          cy.url().should('include', '/plots/create')
        }
      })
    })

    it('should navigate to digital notebook submenu items', () => {
      cy.get('[data-cy="sidebar-nav-cuaderno-digital"]').click()
      cy.waitForLivewire()
      
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="sidebar-submenu-cuaderno-digital"]').length > 0) {
          cy.get('[data-cy="sidebar-submenu-item-registrar-tratamiento"]').should('be.visible')
          cy.get('[data-cy="sidebar-submenu-item-registrar-fertilización"]').should('be.visible')
          cy.get('[data-cy="sidebar-submenu-item-registrar-riego"]').should('be.visible')
        }
      })
    })

    it('should navigate to create treatment from submenu', () => {
      cy.get('[data-cy="sidebar-nav-cuaderno-digital"]').click()
      cy.waitForLivewire()
      
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="sidebar-submenu-item-registrar-tratamiento"]').length > 0) {
          cy.get('[data-cy="sidebar-submenu-item-registrar-tratamiento"]').click()
          cy.waitForLivewire()
          cy.url().should('include', '/viticulturist/digital-notebook/treatment/create')
        }
      })
    })
  })

  describe('Sidebar Toggle', () => {
    it('should toggle sidebar on desktop', () => {
      cy.viewport(1280, 720)
      cy.get('[data-cy="sidebar-toggle-desktop"]').should('be.visible')
      
      cy.get('#sidebar').then(($sidebar) => {
        const initialWidth = $sidebar.width()
        cy.get('[data-cy="sidebar-toggle-desktop"]').click()
        cy.wait(400)
        
        cy.get('#sidebar').then(($sidebarAfter) => {
          const afterWidth = $sidebarAfter.width()
          expect(afterWidth).not.to.equal(initialWidth)
        })
      })
    })

    it('should toggle sidebar on mobile', () => {
      cy.viewport(375, 667)
      cy.get('[data-cy="sidebar-toggle-mobile"]').should('be.visible')
      
      cy.get('#sidebar').should('have.class', '-translate-x-full')
      cy.get('[data-cy="sidebar-toggle-mobile"]').click()
      cy.wait(300)
      cy.get('#sidebar').should('not.have.class', '-translate-x-full')
    })
  })

  describe('Recent Activities Display', () => {
    it('should display recent activities if they exist', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="recent-activities-list"]').length > 0) {
          cy.get('[data-cy="recent-activities-list"]').should('be.visible')
          cy.get('[data-cy="recent-activity-item"]').should('have.length.at.least', 1)
        } else {
          cy.get('[data-cy="recent-activities-section"]').should('contain.text', 'No hay actividades registradas')
        }
      })
    })
  })

  describe('Recent Harvests Display', () => {
    it('should display recent harvests if they exist', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="recent-harvests-list"]').length > 0) {
          cy.get('[data-cy="recent-harvests-list"]').should('be.visible')
          cy.get('[data-cy="recent-harvest-item"]').should('have.length.at.least', 1)
        } else {
          cy.get('[data-cy="recent-harvests-section"]').should('contain.text', 'No hay cosechas registradas')
        }
      })
    })
  })

  describe('Variety Distribution Chart', () => {
    it('should display variety distribution chart', () => {
      cy.get('[data-cy="chart-variety-distribution"]').should('be.visible')
      cy.get('[data-cy="chart-variety-distribution"]').should('contain.text', 'Distribución por Variedad')
    })

    it('should show variety data if available', () => {
      cy.get('body').then(($body) => {
        if ($body.find('[data-cy="chart-variety-distribution"]').length > 0) {
          cy.get('[data-cy="chart-variety-distribution"]').within(() => {
            // Should either show data or empty state
            cy.get('body').should('satisfy', ($body) => {
              return $body.text().includes('variedad') || $body.text().includes('No hay datos')
            })
          })
        }
      })
    })
  })
})
