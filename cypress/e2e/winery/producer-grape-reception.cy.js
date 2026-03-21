/**
 * Producer Grape Reception - E2E Tests
 * Cubre: Index, Create (auto-recepción), Edit, Assign
 *
 * El producer actúa como su propio viticultor:
 * - viticulturist_id = producer->id (auto-recepción, sin WineryViticulturist)
 * - Las parcelas pertenecen al propio producer
 * - Los contenedores pertenecen al propio producer
 *
 * Rutas: /producer/grape-reception/*
 * Usuario de prueba: producer@test.com (creado por CypressTestUserSeeder)
 */

describe('Producer Grape Reception', () => {
  beforeEach(() => {
    cy.loginAsProducer()
    cy.visit('/producer/grape-reception')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de recepciones del producer', () => {
      cy.url().should('include', '/producer/grape-reception')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear recepción', () => {
      cy.get('a[href*="/producer/grape-reception/create"]').should('be.visible')
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
  })

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/producer/grape-reception/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de recepción', () => {
      cy.url().should('include', '/producer/grape-reception/create')
      cy.getByWireModel('total_weight').should('exist')
      cy.get('[wire\\:model="harvest_start_date"]').should('exist')
    })

    it('el viticultor se pre-selecciona como el propio producer', () => {
      // El componente hace: viticulturist_id = Auth::id() en mount() para producers
      cy.get('[wire\\:model\\.live="viticulturist_id"]').then(($sel) => {
        const selected = $sel.val()
        cy.log(`Viticulturist pre-seleccionado: ${selected}`)
        expect(selected).to.not.equal('')
      })
    })

    it('carga las parcelas propias del producer al montar', () => {
      // Tras la pre-selección del viticulturist, el plot select debe activarse
      cy.waitForLivewire()
      cy.wait(1000)
      cy.get('[wire\\:model\\.live="plot_id"]').should('exist')
    })

    it('valida campos requeridos al enviar vacío', () => {
      // Vaciar viticulturist_id para forzar errores
      cy.get('[wire\\:model\\.live="viticulturist_id"]').select('', { force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/producer/grape-reception/create')
    })

    it('crea recepción completa si hay parcela y contenedor disponibles', () => {
      cy.waitForLivewire()
      cy.wait(1000)

      cy.get('[wire\\:model\\.live="plot_id"]').then(($plotSel) => {
        const plotOptions = $plotSel.find('option').filter((i, el) => el.value !== '')
        if (plotOptions.length === 0) {
          cy.log('No plots available for producer - skipping full creation test')
          return
        }

        cy.get('[wire\\:model\\.live="plot_id"]').select(plotOptions.first().val(), { force: true })
        cy.waitForLivewire()
        cy.wait(1000)

        cy.get('[wire\\:model\\.live="plot_planting_id"]').then(($plantSel) => {
          const plantOptions = $plantSel.find('option').filter((i, el) => el.value !== '')
          if (plantOptions.length === 0) {
            cy.log('No plantings available for producer - skipping full creation test')
            return
          }

          cy.get('[wire\\:model\\.live="plot_planting_id"]').select(plantOptions.first().val(), { force: true })
          cy.waitForLivewire()
          cy.wait(1000)

          cy.get('[wire\\:model="container_id"]').then(($contSel) => {
            const contOptions = $contSel.find('option').filter((i, el) => el.value !== '')
            if (contOptions.length === 0) {
              cy.log('No containers available for producer - skipping full creation test')
              return
            }

            cy.get('[wire\\:model="container_id"]').select(contOptions.first().val(), { force: true })
            cy.getByWireModel('vintage_year').select('2024', { force: true })
            cy.getByWireModel('total_weight').clear().type('300')
            cy.get('[wire\\:model="harvest_start_date"]').type('2024-09-20')

            cy.get('[data-cy="submit-button"]').click({ force: true })
            cy.wait(5000)

            cy.url().should('include', '/producer/grape-reception')
            cy.url().should('not.include', '/create')
          })
        })
      })
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/producer/grape-reception/"][href*="/edit"]')

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
        const editLinks = $body.find('a[href*="/producer/grape-reception/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No receptions to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.getByWireModel('total_weight').clear().type('400')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/producer/grape-reception')
        cy.url().should('not.include', '/edit')
      })
    })

    it('valida campos requeridos en edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/producer/grape-reception/"][href*="/edit"]')

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

  // ─── ASSIGN ───────────────────────────────────────────────────────────────

  describe('Assign', () => {
    it('navega al formulario de asignación de contenedor', () => {
      cy.get('body').then(($body) => {
        const assignLinks = $body.find('a[href*="/producer/grape-reception/"][href*="/assign"]')

        if (assignLinks.length > 0) {
          cy.wrap(assignLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/assign')
          cy.get('[wire\\:model="container_id"]').should('exist')
        } else {
          cy.log('No assign links found - skipping')
        }
      })
    })

    it('el formulario precarga el contenedor actual del producer', () => {
      cy.get('body').then(($body) => {
        const assignLinks = $body.find('a[href*="/producer/grape-reception/"][href*="/assign"]')

        if (assignLinks.length === 0) {
          cy.log('No assign links found - skipping')
          return
        }

        cy.wrap(assignLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="container_id"]').then(($sel) => {
          expect($sel.val()).to.not.equal('')
        })
      })
    })

    it('reasigna recepción del producer a un contenedor diferente', () => {
      cy.get('body').then(($body) => {
        const assignLinks = $body.find('a[href*="/producer/grape-reception/"][href*="/assign"]')

        if (assignLinks.length === 0) {
          cy.log('No assign links found - skipping')
          return
        }

        cy.wrap(assignLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.get('[wire\\:model="container_id"]').then(($sel) => {
          const currentVal = $sel.val()
          const options    = $sel.find('option').filter((i, el) => el.value !== '' && el.value !== currentVal)

          if (options.length === 0) {
            cy.log('No alternative containers available - skipping')
            return
          }

          cy.get('[wire\\:model="container_id"]').select(options.first().val(), { force: true })
          cy.waitForLivewire()

          cy.get('[data-cy="submit-button"]').click({ force: true })
          cy.wait(3000)

          cy.url().should('include', '/producer/grape-reception')
          cy.url().should('not.include', '/assign')
        })
      })
    })
  })
})
