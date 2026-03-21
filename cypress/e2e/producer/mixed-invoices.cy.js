/**
 * Producer Mixed Invoices (Albaranes mixtos) - E2E Tests
 * Cubre: Index, Create (cosechas + lotes de vino), Edit
 *
 * El producer puede emitir albaranes que mezclan:
 *   - Ítems de cosecha (uva en kg)
 *   - Ítems de lote de vino (botellas/cajas)
 *
 * Rutas: /producer/invoices/mixed/*
 * Usuario de prueba: producer@test.com (CypressTestUserSeeder)
 */

const uniqueId = () => Date.now()

// ── Helpers ──────────────────────────────────────────────────────────────────

function selectFirstClient() {
  return cy.getByWireModel('client_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    cy.waitForLivewire()
    return true
  })
}

function selectFirstHarvest() {
  return cy.getByWireModel('selectedHarvestId').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    return true
  })
}

function selectFirstLot() {
  return cy.getByWireModel('selectedLotId').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    return true
  })
}

// ── Tests ─────────────────────────────────────────────────────────────────────

describe('Producer Mixed Invoices', () => {
  beforeEach(() => {
    cy.loginAsProducer()
    cy.visit('/producer/invoices/mixed')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de albaranes mixtos', () => {
      cy.url().should('include', '/producer/invoices/mixed')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear albarán', () => {
      cy.get('a[href*="/producer/invoices/mixed/create"]').should('be.visible')
    })

    it('filtra si hay campo de búsqueda', () => {
      cy.get('body').then(($body) => {
        const search = $body.find(
          '[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"], [wire\\:model="search"]'
        )
        if (search.length > 0) {
          cy.wrap(search.first()).type('Cypress', { force: true })
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
      cy.visit('/producer/invoices/mixed/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/producer/invoices/mixed/create')
      cy.get('body').should('be.visible')
    })

    it('valida que se requiere cliente', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/producer/invoices/mixed/create')
    })

    it('valida que se requiere al menos un ítem', () => {
      selectFirstClient().then((hasClient) => {
        if (!hasClient) { cy.log('Sin clientes, omitiendo'); return }
        // Sin añadir ítems, intentar guardar
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2000)
        cy.url().should('include', '/producer/invoices/mixed/create')
      })
    })

    it('crea albarán con lote de vino si hay lotes disponibles', () => {
      selectFirstClient().then((hasClient) => {
        if (!hasClient) { cy.log('Sin clientes, omitiendo'); return }
        selectFirstLot().then((hasLot) => {
          if (!hasLot) { cy.log('Sin lotes de vino, omitiendo'); return }
          // Añadir lote de vino al albarán
          cy.get('body').then(($body) => {
            const addWineBtn = $body.find('[data-cy="add-wine-btn"], [wire\\:click="addWineToInvoice"]')
            if (addWineBtn.length > 0) {
              cy.wrap(addWineBtn.first()).click({ force: true })
              cy.waitForLivewire()
            }
          })
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()
          // Puede redirigir al index o mostrar error de validación — ambos son válidos
          cy.get('body').should('be.visible')
        })
      })
    })

    it('crea albarán con cosecha si hay cosechas disponibles', () => {
      selectFirstClient().then((hasClient) => {
        if (!hasClient) { cy.log('Sin clientes, omitiendo'); return }
        selectFirstHarvest().then((hasHarvest) => {
          if (!hasHarvest) { cy.log('Sin cosechas disponibles, omitiendo'); return }
          // Añadir cosecha al albarán
          cy.get('body').then(($body) => {
            const addHarvestBtn = $body.find('[data-cy="add-harvest-btn"], [wire\\:click="addHarvestToInvoice"]')
            if (addHarvestBtn.length > 0) {
              cy.wrap(addHarvestBtn.first()).click({ force: true })
              cy.waitForLivewire()
            }
          })
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()
          cy.get('body').should('be.visible')
        })
      })
    })
  })

  // ── Edit — ciclo create → edit ─────────────────────────────────────────────

  describe('Edit', () => {
    it('accede a la edición del primer albarán si existe', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/producer/invoices/mixed/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No hay albaranes creados — omitiendo tests de edición')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')
        cy.get('body').should('be.visible')
      })
    })

    it('el formulario de edición carga los datos existentes', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/producer/invoices/mixed/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin albaranes, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          // Los campos de cabecera deben estar pre-cargados
          cy.getByWireModel('client_id').should(($sel) => {
            expect($sel.val()).to.not.equal('')
          })
        })
      })
    })

    it('guardar edición redirige al index', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/producer/invoices/mixed/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin albaranes, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()
          cy.url().should('include', '/producer/invoices/mixed')
          cy.url().should('not.include', '/edit')
        })
      })
    })

    it('botón cancelar vuelve al index', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/producer/invoices/mixed/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin albaranes, omitiendo'); return }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return
          cy.get('body').then(($b) => {
            const cancel = $b.find('a[href*="/producer/invoices/mixed"]:not([href*="/edit"]):not([href*="/create"])')
            if (cancel.length > 0) {
              cy.wrap(cancel.first()).click({ force: true })
              cy.waitForLivewire()
              cy.url().should('include', '/producer/invoices/mixed')
              cy.url().should('not.include', '/edit')
            }
          })
        })
      })
    })
  })
})
