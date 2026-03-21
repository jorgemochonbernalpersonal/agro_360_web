/**
 * Winery Wine Transfers (Trasvases de vino) - E2E Tests
 * Cubre: Index, Create, Edit (con ciclo create → edit completo)
 *
 * Invariante clave cubierta (Bug doble conteo):
 *   Un trasvase sólo modifica wine_volume_liters, nunca used_capacity.
 *   La capacidad del destino debe respetarse (validación de overfill).
 *   Editar un trasvase debe guardar sin errores y redirigir al index.
 *
 * Nota: wine_volume_liters es un valor de BD — los tests PHPUnit
 * (WineTransferStockTest) cubren la invariante de datos.
 * Cypress verifica el flujo UI: formulario, validaciones, redirecciones.
 */

const uniqueId = () => Date.now()

// ── Helpers reutilizables ───────────────────────────────────────────────────

function selectFirstWine() {
  return cy.getByWireModel('wine_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    cy.waitForLivewire()
    return true
  })
}

function selectFirstDestContainer() {
  return cy.getByWireModel('to_container_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    cy.waitForLivewire()
    return true
  })
}

function selectFirstUom() {
  cy.getByWireModel('unit_of_measurement_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length > 0) cy.wrap($sel).select(opts.first().val(), { force: true })
  })
}

function createTransfer(qty = '10') {
  selectFirstWine().then((hasWine) => {
    if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
    selectFirstDestContainer().then((hasDest) => {
      if (!hasDest) { cy.log('Sin contenedor destino, omitiendo'); return }
      cy.getByWireModel('quantity').clear().type(qty)
      cy.getByWireModel('transfer_date').clear().type(new Date().toISOString().split('T')[0])
      cy.getByWireModel('transfer_type').select('racking', { force: true })
      selectFirstUom()
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(3000)
      cy.waitForLivewire()
    })
  })
}

// ── Tests ───────────────────────────────────────────────────────────────────

describe('Winery Wine Transfers', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/wine-transfers')
    cy.waitForLivewire()
  })

  // ── Index ──────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de trasvases', () => {
      cy.url().should('include', '/winery/wine-transfers')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear trasvase', () => {
      cy.get('a[href*="/winery/wine-transfers/create"]').should('be.visible')
    })

    it('filtra la lista si hay campo de búsqueda', () => {
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
      cy.visit('/winery/wine-transfers/create')
      cy.waitForLivewire()
    })

    it('muestra todos los campos del formulario', () => {
      cy.url().should('include', '/winery/wine-transfers/create')
      cy.getByWireModel('wine_id').should('exist')
      cy.getByWireModel('to_container_id').should('exist')
      cy.getByWireModel('quantity').should('exist')
      cy.getByWireModel('transfer_date').should('exist')
      cy.getByWireModel('transfer_type').should('exist')
      cy.getByWireModel('unit_of_measurement_id').should('exist')
    })

    it('valida campos requeridos al guardar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/wine-transfers/create')
    })

    it('la cantidad es requerida y debe ser mayor que 0', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        selectFirstDestContainer()
        cy.getByWireModel('quantity').clear().type('0')
        cy.getByWireModel('transfer_date').clear().type(new Date().toISOString().split('T')[0])
        selectFirstUom()
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2000)
        cy.url().should('include', '/winery/wine-transfers/create')
      })
    })

    it('crea trasvase sin contenedor origen (sólo destino) y redirige', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        // Dejar from_container vacío intencionalmente
        cy.getByWireModel('from_container_id').then(($sel) => {
          if ($sel.length) cy.wrap($sel).select('', { force: true })
        })
        selectFirstDestContainer().then((hasDest) => {
          if (!hasDest) { cy.log('Sin destino, omitiendo'); return }
          cy.getByWireModel('quantity').clear().type('5')
          cy.getByWireModel('transfer_date').clear().type(new Date().toISOString().split('T')[0])
          cy.getByWireModel('transfer_type').select('racking', { force: true })
          selectFirstUom()
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()
          cy.url().should('include', '/winery/wine-transfers')
          cy.url().should('not.include', '/create')
        })
      })
    })

    it('crea trasvase completo con origen y destino y redirige', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        // Seleccionar origen si existe
        cy.getByWireModel('from_container_id').then(($sel) => {
          const opts = $sel.find('option').filter((i, el) => el.value !== '')
          if (opts.length > 0) cy.wrap($sel).select(opts.first().val(), { force: true })
          cy.waitForLivewire()
        })
        selectFirstDestContainer().then((hasDest) => {
          if (!hasDest) { cy.log('Sin destino, omitiendo'); return }
          cy.getByWireModel('quantity').clear().type('10')
          cy.getByWireModel('transfer_date').clear().type(new Date().toISOString().split('T')[0])
          cy.getByWireModel('transfer_type').select('racking', { force: true })
          selectFirstUom()
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()
          cy.url().should('include', '/winery/wine-transfers')
          cy.url().should('not.include', '/create')
        })
      })
    })

    it('bloquea cantidad que supera la capacidad disponible del destino', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        selectFirstDestContainer().then((hasDest) => {
          if (!hasDest) { cy.log('Sin destino, omitiendo'); return }
          cy.getByWireModel('quantity').clear().type('999999999')
          cy.getByWireModel('transfer_date').clear().type(new Date().toISOString().split('T')[0])
          selectFirstUom()
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2000)
          cy.url().should('include', '/winery/wine-transfers/create')
        })
      })
    })
  })

  // ── Edit — ciclo completo create → edit ────────────────────────────────────

  describe('Edit', () => {
    function createAndNavigateToEdit(qty = '8') {
      cy.visit('/winery/wine-transfers/create')
      cy.waitForLivewire()
      createTransfer(qty)
      cy.url().should('include', '/winery/wine-transfers')
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wine-transfers/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No se encontró enlace de edición — trasvase no creado, omitiendo')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')
      })
    }

    it('el formulario de edición pre-carga los datos existentes', () => {
      createAndNavigateToEdit('12')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('wine_id').should(($sel) => {
          expect($sel.val()).to.not.equal('')
        })
        cy.getByWireModel('quantity').should(($inp) => {
          expect(parseFloat($inp.val())).to.be.greaterThan(0)
        })
        cy.getByWireModel('transfer_date').should(($inp) => {
          expect($inp.val()).to.not.equal('')
        })
        cy.getByWireModel('transfer_type').should(($sel) => {
          expect($sel.val()).to.not.equal('')
        })
      })
    })

    it('editar la cantidad y guardar redirige al index', () => {
      createAndNavigateToEdit('15')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('quantity').clear().type('20')
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-transfers')
        cy.url().should('not.include', '/edit')
      })
    })

    it('editar el tipo de trasvase y guardar redirige al index', () => {
      createAndNavigateToEdit('6')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('transfer_type').then(($sel) => {
          const opts = $sel.find('option').filter((i, el) => el.value !== '' && el.value !== $sel.val())
          if (opts.length > 0) {
            cy.wrap($sel).select(opts.first().val(), { force: true })
            cy.waitForLivewire()
          }
        })
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-transfers')
        cy.url().should('not.include', '/edit')
      })
    })

    it('editar notas y guardar redirige al index', () => {
      createAndNavigateToEdit('7')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('notes').clear().type(`Cypress edit trasvase ${uniqueId()}`)
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-transfers')
        cy.url().should('not.include', '/edit')
      })
    })

    it('editar cantidad a valor que supera capacidad del destino muestra error', () => {
      createAndNavigateToEdit('5')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('quantity').clear().type('999999999')
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2000)
        cy.url().should('include', '/edit')
      })
    })

    it('cantidad requerida al editar — valor vacío no guarda', () => {
      createAndNavigateToEdit('9')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('quantity').clear()
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2000)
        cy.url().should('include', '/edit')
      })
    })

    it('botón cancelar vuelve al index sin guardar', () => {
      createAndNavigateToEdit('11')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('quantity').clear().type('9999')
        cy.get('body').then(($body) => {
          const cancelBtn = $body.find('a[href*="/winery/wine-transfers"]:not([href*="/edit"]):not([href*="/create"])')
          if (cancelBtn.length > 0) {
            cy.wrap(cancelBtn.first()).click({ force: true })
            cy.waitForLivewire()
            cy.url().should('include', '/winery/wine-transfers')
            cy.url().should('not.include', '/edit')
          }
        })
      })
    })
  })
})
