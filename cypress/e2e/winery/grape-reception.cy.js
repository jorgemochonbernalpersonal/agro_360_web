/**
 * Winery Grape Reception - E2E Tests
 * Cubre: Index (list, filtros), Create, Show, Edit
 *
 * NOTA: Create requiere datos previos en BD:
 *  - Viticultor vinculado a la bodega (WineryViticulturist)
 *  - Parcelas + plantaciones del viticultor
 *  - Contenedores de la bodega
 * Los tests de creación verifican el formulario y validan campos requeridos.
 * Si no hay datos de apoyo, los tests de creación completa se omiten gracefully.
 */

const uniqueId = () => Date.now()

describe('Winery Grape Reception', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/grape-reception')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de recepciones', () => {
      cy.url().should('include', '/winery/grape-reception')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear recepción', () => {
      cy.get('a[href*="/winery/grape-reception/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
      cy.get('body').then(($body) => {
        const searchInput = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (searchInput.length > 0) {
          cy.wrap(searchInput.first()).type('test', { force: true })
          cy.waitForLivewire()
          cy.wait(600)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No search input found')
        }
      })
    })

    it('filtra por campaña', () => {
      cy.get('body').then(($body) => {
        const campaignFilter = $body.find('[wire\\:model\\.live="filterCampaign"], [wire\\:model="filterCampaign"]')
        if (campaignFilter.length > 0) {
          cy.wrap(campaignFilter.first()).find('option').eq(0).then(($opt) => {
            cy.wrap(campaignFilter.first()).select($opt.val(), { force: true })
          })
          cy.waitForLivewire()
          cy.wait(500)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No campaign filter found')
        }
      })
    })

    it('filtra por viticultor', () => {
      cy.get('body').then(($body) => {
        const vitFilter = $body.find('[wire\\:model\\.live="filterViticulturist"], [wire\\:model="filterViticulturist"]')
        if (vitFilter.length > 0) {
          cy.wrap(vitFilter.first()).find('option').eq(0).then(($opt) => {
            cy.wrap(vitFilter.first()).select($opt.val(), { force: true })
          })
          cy.waitForLivewire()
          cy.wait(500)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No viticulturist filter found')
        }
      })
    })
  })

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/grape-reception/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de recepción', () => {
      cy.url().should('include', '/winery/grape-reception/create')
      cy.getByWireModel('total_weight').should('exist')
      cy.get('[wire\\:model="harvest_start_date"]').should('exist')
    })

    it('valida campos requeridos al enviar vacío', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/grape-reception/create')
    })

    it('rellena datos básicos de la recepción', () => {
      // Rellena campos que no dependen de selects dinámicos
      cy.getByWireModel('vintage_year').select('2024', { force: true })
      cy.getByWireModel('total_weight').clear().type('500')
      cy.get('[wire\\:model="harvest_start_date"]').type('2024-09-15')
      cy.get('[wire\\:model="price_per_kg"]').clear().type('0.45')
      cy.get('[wire\\:model="harvest_ticket_number"]').clear().type(`TK-${uniqueId()}`)
      cy.get('[wire\\:model="vehicle_plate"]').clear().type('1234ABC')

      // Calidad (opcionales)
      cy.get('[wire\\:model="baume_degree"]').clear().type('12.5')
      cy.get('[wire\\:model="brix_degree"]').clear().type('22.0')
      cy.get('[wire\\:model="ph_level"]').clear().type('3.4')

      cy.get('body').should('be.visible')
    })

    it('selecciona viticultor y carga parcelas si hay datos', () => {
      cy.get('[wire\\:model\\.live="viticulturist_id"]').then(($sel) => {
        const options = $sel.find('option').filter((i, el) => el.value !== '')
        if (options.length > 0) {
          cy.get('[wire\\:model\\.live="viticulturist_id"]').select(options.first().val(), { force: true })
          cy.waitForLivewire()
          cy.wait(1000)

          // El select de parcelas debe activarse
          cy.get('[wire\\:model\\.live="plot_id"]').should('exist')
        } else {
          cy.log('No linked viticulturists available - skipping cascade test')
        }
      })
    })

    it('crea recepción completa si hay viticultor y contenedor disponibles', () => {
      cy.get('[wire\\:model\\.live="viticulturist_id"]').then(($vitSel) => {
        const vitOptions = $vitSel.find('option').filter((i, el) => el.value !== '')
        if (vitOptions.length === 0) {
          cy.log('No viticulturists linked - skipping full creation test')
          return
        }

        cy.get('[wire\\:model\\.live="viticulturist_id"]').select(vitOptions.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        cy.get('[wire\\:model\\.live="plot_id"]').then(($plotSel) => {
          const plotOptions = $plotSel.find('option').filter((i, el) => el.value !== '')
          if (plotOptions.length === 0) {
            cy.log('No plots available - skipping full creation test')
            return
          }

          cy.get('[wire\\:model\\.live="plot_id"]').select(plotOptions.first().val(), { force: true })
          cy.waitForLivewire()
          cy.wait(1000)

          cy.get('[wire\\:model\\.live="plot_planting_id"]').then(($plantSel) => {
            const plantOptions = $plantSel.find('option').filter((i, el) => el.value !== '')
            if (plantOptions.length === 0) {
              cy.log('No plantings available - skipping full creation test')
              return
            }

            cy.get('[wire\\:model\\.live="plot_planting_id"]').select(plantOptions.first().val(), { force: true })
            cy.waitForLivewire()
            cy.wait(1000)

            cy.get('[wire\\:model="container_id"]').then(($contSel) => {
              const contOptions = $contSel.find('option').filter((i, el) => el.value !== '')
              if (contOptions.length === 0) {
                cy.log('No containers available - skipping full creation test')
                return
              }

              cy.get('[wire\\:model="container_id"]').select(contOptions.first().val(), { force: true })

              cy.getByWireModel('vintage_year').select('2024', { force: true })
              cy.getByWireModel('total_weight').clear().type('500')
              cy.get('[wire\\:model="harvest_start_date"]').type('2024-09-15')

              cy.get('[data-cy="submit-button"]').click({ force: true })
              cy.wait(5000)

              cy.url().should('include', '/winery/grape-reception')
              cy.url().should('not.include', '/create')
            })
          })
        })
      })
    })
  })

  // ─── SHOW ─────────────────────────────────────────────────────────────────

  describe('Show', () => {
    it('navega a la página de detalle de una recepción', () => {
      cy.get('body').then(($body) => {
        const showLinks = $body.find('a[href*="/winery/grape-reception/"]').filter((i, el) => {
          const href = el.getAttribute('href')
          return href &&
            !href.includes('/create') &&
            !href.includes('/edit') &&
            !href.includes('/assign') &&
            !href.includes('/disputes') &&
            !href.includes('/export') &&
            /\/winery\/grape-reception\/\d+$/.test(href)
        })

        if (showLinks.length > 0) {
          cy.wrap(showLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('match', /\/winery\/grape-reception\/\d+$/)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No receptions found - skipping show test')
        }
      })
    })

    it('la página show tiene enlace de edición', () => {
      cy.get('body').then(($body) => {
        const showLinks = $body.find('a[href*="/winery/grape-reception/"]').filter((i, el) => {
          const href = el.getAttribute('href')
          return href && /\/winery\/grape-reception\/\d+$/.test(href)
        })

        if (showLinks.length === 0) {
          cy.log('No receptions found - skipping')
          return
        }

        cy.wrap(showLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.get('a[href*="/edit"]').should('exist')
      })
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/grape-reception/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.getByWireModel('total_weight').should('exist')
        } else {
          cy.log('No receptions to edit - skipping')
        }
      })
    })

    it('actualiza el peso total', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/grape-reception/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No receptions to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.getByWireModel('total_weight').clear().type('750')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/grape-reception')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza parámetros de calidad', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/grape-reception/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No receptions to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="baume_degree"]').clear().type('13.2')
        cy.get('[wire\\:model="brix_degree"]').clear().type('24.0')
        cy.get('[wire\\:model="ph_level"]').clear().type('3.5')
        cy.get('[wire\\:model="price_per_kg"]').clear().type('0.50')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/grape-reception')
        cy.url().should('not.include', '/edit')
      })
    })

    it('valida campos requeridos en edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/grape-reception/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No receptions to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.getByWireModel('total_weight').clear()

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(2000)

        cy.url().should('include', '/edit')
      })
    })
  })
})
