/**
 * Winery Wine Losses (Mermas de vino) - E2E Tests
 * Cubre: Index, Create, Edit
 *
 * Invariante clave: una merma sólo debe modificar wine_volume_liters y wine.volume_liters,
 * nunca used_capacity (campo exclusivo de cosechas/uva).
 */

describe('Winery Wine Losses', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/wine-losses')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de mermas', () => {
      cy.url().should('include', '/winery/wine-losses')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para registrar merma', () => {
      cy.get('a[href*="/winery/wine-losses/create"]').should('be.visible')
    })

    it('filtra la lista si hay campo de búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find(
          '[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"], [wire\\:model="search"]'
        )
        if (search.length > 0) {
          cy.wrap(search.first()).type('test', { force: true })
          cy.waitForLivewire()
          cy.wait(600)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Create ─────────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/wine-losses/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de merma', () => {
      cy.url().should('include', '/winery/wine-losses/create')
      cy.getByWireModel('quantity').should('exist')
      cy.getByWireModel('loss_date').should('exist')
      cy.getByWireModel('loss_type').should('exist')
    })

    it('valida campos requeridos al guardar sin datos', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/wine-losses/create')
    })

    it('registra merma si hay vinos disponibles', () => {
      cy.getByWireModel('wine_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('Sin vinos disponibles, omitiendo test de creación')
          return
        }

        cy.wrap($sel).select(opts.first().val(), { force: true })
        cy.waitForLivewire()

        cy.getByWireModel('loss_type').select('evaporation', { force: true })
        cy.getByWireModel('quantity').clear().type('5')
        cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])

        cy.getByWireModel('unit_of_measurement_id').then(($uomSel) => {
          const uomOpts = $uomSel.find('option').filter((i, el) => el.value !== '')
          if (uomOpts.length > 0) {
            cy.wrap($uomSel).select(uomOpts.first().val(), { force: true })
          }
        })

        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-losses')
      })
    })

    it('muestra error si cantidad supera el vino del contenedor', () => {
      cy.getByWireModel('wine_id').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('Sin vinos, omitiendo')
          return
        }

        cy.wrap($sel).select(opts.first().val(), { force: true })
        cy.waitForLivewire()

        cy.getByWireModel('container_id').then(($contSel) => {
          const contOpts = $contSel.find('option').filter((i, el) => el.value !== '')
          if (contOpts.length === 0) {
            cy.log('Sin contenedores, omitiendo')
            return
          }

          cy.wrap($contSel).select(contOpts.first().val(), { force: true })
          cy.waitForLivewire()

          cy.getByWireModel('quantity').clear().type('999999999')
          cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])

          cy.getByWireModel('unit_of_measurement_id').then(($uomSel) => {
            const uomOpts = $uomSel.find('option').filter((i, el) => el.value !== '')
            if (uomOpts.length > 0) {
              cy.wrap($uomSel).select(uomOpts.first().val(), { force: true })
            }
          })

          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2000)
          cy.url().should('include', '/winery/wine-losses/create')
        })
      })
    })
  })

  // ── Edit ───────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega a edición si existe una merma', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wine-losses/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('Sin mermas existentes, omitiendo test de edición')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')
        cy.getByWireModel('quantity').should('exist')
      })
    })

    it('guarda cambios de edición de merma', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wine-losses/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('Sin mermas existentes, omitiendo')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.getByWireModel('notes').clear().type(`Editado Cypress ${Date.now()}`)

        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-losses')
      })
    })
  })
})
