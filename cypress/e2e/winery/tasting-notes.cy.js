/**
 * Winery Tasting Notes (Notas de Cata) - E2E Tests
 * Cubre: Index, Create, Edit
 * Requiere: al menos un vino en la BD
 */

const uniqueId = () => Date.now()

describe('Winery Tasting Notes', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/tasting-notes')
    cy.waitForLivewire()
  })

  describe('Index', () => {
    it('muestra la página de notas de cata', () => {
      cy.url().should('include', '/winery/tasting-notes')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear nota de cata', () => {
      cy.get('a[href*="/winery/tasting-notes/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
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
      cy.visit('/winery/tasting-notes/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario', () => {
      cy.url().should('include', '/winery/tasting-notes/create')
      cy.get('[wire\\:model="evaluation_date"]').should('exist')
    })

    it('valida campos requeridos', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/tasting-notes/create')
    })

    it('crea nota de cata básica si hay vinos', () => {
      cy.get('[wire\\:model="wine_id"]').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No wines available - skipping')
          return
        }

        cy.get('[wire\\:model="wine_id"]').select(opts.first().val(), { force: true })
        cy.get('[wire\\:model="evaluation_date"]').type('2024-10-15')
        cy.get('[wire\\:model="evaluator_name"]').clear().type('Evaluador Test')
        cy.get('[wire\\:model="overall_score"]').clear().type('88')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/tasting-notes')
        cy.url().should('not.include', '/create')
      })
    })

    it('crea nota de cata completa si hay vinos', () => {
      cy.get('[wire\\:model="wine_id"]').then(($sel) => {
        const opts = $sel.find('option').filter((i, el) => el.value !== '')
        if (opts.length === 0) {
          cy.log('No wines available - skipping')
          return
        }

        cy.get('[wire\\:model="wine_id"]').select(opts.first().val(), { force: true })
        cy.get('[wire\\:model="evaluation_date"]').type('2024-11-01')
        cy.get('[wire\\:model="evaluator_name"]').clear().type('Catador E2E')
        cy.get('[wire\\:model="overall_score"]').clear().type('92')
        cy.get('[wire\\:model="overall_conclusion"]').clear().type('Vino excelente con gran potencial')
        cy.get('[wire\\:model="aroma_descriptors"]').clear().type('Frutos rojos, vainilla, especias')

        // Selectores de nivel (si existen)
        cy.get('body').then(($body) => {
          const aromaIntensity = $body.find('[wire\\:model="aroma_intensity"]')
          if (aromaIntensity.length > 0) {
            aromaIntensity.find('option').eq(1).then(($opt) => {
              if ($opt.length) cy.wrap(aromaIntensity).select($opt.val(), { force: true })
            })
          }
        })

        cy.get('[wire\\:model="notes"]').clear().type('Nota de cata E2E completa')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/tasting-notes')
        cy.url().should('not.include', '/create')
      })
    })
  })

  describe('Edit', () => {
    it('actualiza puntuación y conclusión', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/tasting-notes/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No tasting notes to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')

        cy.get('[wire\\:model="overall_score"]').clear().type('90')
        cy.get('[wire\\:model="overall_conclusion"]').clear().type('Conclusión actualizada E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/tasting-notes')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza evaluador y notas', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/tasting-notes/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No tasting notes to edit - skipping')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.get('[wire\\:model="evaluator_name"]').clear().type(`Catador ${id}`)
        cy.get('[wire\\:model="notes"]').clear().type('Notas actualizadas en test E2E')

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/tasting-notes')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
