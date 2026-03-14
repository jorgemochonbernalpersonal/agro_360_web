/**
 * Winery Harvest Forecasts (Previsiones de Vendimia) - E2E Tests
 * Cubre: Index, Create, Edit
 * Requiere: viticultor vinculado + parcela + plantación + campaña activa
 */

const uniqueId = () => Date.now()

describe('Winery Harvest Forecasts', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/harvest-forecasts')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de previsiones', () => {
      cy.url().should('include', '/winery/harvest-forecasts')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear previsión', () => {
      cy.get('a[href*="/winery/harvest-forecasts/create"]').should('be.visible')
    })

    it('filtra por búsqueda o campaña', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('test', { force: true })
          cy.waitForLivewire()
          cy.wait(600)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/harvest-forecasts/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/harvest-forecasts/create')
      cy.get('[wire\\:model="estimated_kg"]').should('exist')
      cy.get('[wire\\:model="estimation_date"]').should('exist')
    })

    it('valida campos requeridos', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/harvest-forecasts/create')
    })

    it('selecciona viticultor y carga datos si hay disponibles', () => {
      cy.get('[wire\\:model\\.live="viticulturist_id"]').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No linked viticulturists - skipping cascade test')
          return
        }

        cy.get('[wire\\:model\\.live="viticulturist_id"]').select(opts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        cy.get('[wire\\:model\\.live="plot_id"]').should('exist')
      })
    })

    it('crea previsión completa si hay datos de apoyo', () => {
      cy.get('[wire\\:model\\.live="viticulturist_id"]').then(($vitSel) => {
        const vitOpts = $vitSel.find('option').filter((i, el) => el.value !== '')
        if (vitOpts.length === 0) {
          cy.log('No viticulturists linked - skipping')
          return
        }

        cy.get('[wire\\:model\\.live="viticulturist_id"]').select(vitOpts.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        cy.get('[wire\\:model\\.live="plot_id"]').then(($plotSel) => {
          const plotOpts = $plotSel.find('option').filter((i, el) => el.value !== '')
          if (plotOpts.length === 0) {
            cy.log('No plots - skipping')
            return
          }

          cy.get('[wire\\:model\\.live="plot_id"]').select(plotOpts.first().val(), { force: true })
          cy.waitForLivewire()
          cy.wait(1000)

          cy.get('[wire\\:model\\.live="plot_planting_id"]').then(($plantSel) => {
            const plantOpts = $plantSel.find('option').filter((i, el) => el.value !== '')
            if (plantOpts.length === 0) {
              cy.log('No plantings - skipping')
              return
            }

            cy.get('[wire\\:model\\.live="plot_planting_id"]').select(plantOpts.first().val(), { force: true })
            cy.waitForLivewire()
            cy.wait(500)

            cy.getByWireModel('campaign_id').then(($campSel) => {
              const campOpts = $campSel.find('option').filter((i, el) => el.value !== '')
              if (campOpts.length === 0) {
                cy.log('No campaigns - skipping')
                return
              }

              cy.getByWireModel('campaign_id').select(campOpts.first().val(), { force: true })
              cy.waitForLivewire()
              cy.wait(500)

              cy.get('[wire\\:model="estimated_kg"]').clear().type('8000')
              cy.get('[wire\\:model="estimation_date"]').type('2024-07-15')
              cy.get('[wire\\:model="status"]').select('draft', { force: true })

              cy.get('[data-cy="submit-button"]').click({ force: true })
              cy.wait(5000)

              cy.url().should('include', '/winery/harvest-forecasts')
              cy.url().should('not.include', '/create')
            })
          })
        })
      })
    })
  })

  describe('Edit', () => {
    it('actualiza kg estimados y estado', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/harvest-forecasts/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No forecasts to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="estimated_kg"]').clear().type('9500')
        cy.get('[wire\\:model="status"]').select('confirmed', { force: true })

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/harvest-forecasts')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza fecha y notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/harvest-forecasts/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No forecasts to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="estimation_date"]').clear().type('2024-08-01')
        cy.get('[wire\\:model="notes"]').clear().type('Actualizado en test E2E')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/harvest-forecasts')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
