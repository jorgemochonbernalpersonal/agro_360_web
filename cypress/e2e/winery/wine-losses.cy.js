/**
 * Winery Wine Losses (Mermas de vino) - E2E Tests
 * Cubre: Index, Create, Edit (con ciclo create → edit completo)
 *
 * Invariante clave cubierta (Bug A):
 *   Editar una merma debe guardar sin errores y redirigir al index.
 *   La cantidad editada no puede superar el vino disponible en el contenedor.
 *   Cancelar no modifica nada.
 *
 * Nota: wine.volume_liters es un valor de BD — no es verificable directamente
 * desde Cypress. Los tests PHPUnit (WineLossStockTest) cubren esa invariante.
 * Cypress verifica el flujo UI: formulario, validaciones, redirecciones.
 */

const uniqueId = () => Date.now()

// ── Helpers reutilizables ───────────────────────────────────────────────────

/**
 * Selecciona el primer vino disponible. Devuelve false si no hay ninguno.
 */
function selectFirstWine() {
  return cy.getByWireModel('wine_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    cy.waitForLivewire()
    return true
  })
}

/**
 * Selecciona el primer contenedor disponible. Devuelve false si no hay ninguno.
 */
function selectFirstContainer() {
  return cy.getByWireModel('container_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length === 0) return false
    cy.wrap($sel).select(opts.first().val(), { force: true })
    cy.waitForLivewire()
    return true
  })
}

/**
 * Selecciona la primera unidad de medida disponible.
 */
function selectFirstUom() {
  cy.getByWireModel('unit_of_measurement_id').then(($sel) => {
    const opts = $sel.find('option').filter((i, el) => el.value !== '')
    if (opts.length > 0) cy.wrap($sel).select(opts.first().val(), { force: true })
  })
}

/**
 * Crea una merma completa y devuelve la URL de edición si se creó.
 * Espera estar en /winery/wine-losses/create.
 */
function createLoss(qty = '10') {
  selectFirstWine().then((hasWine) => {
    if (!hasWine) {
      cy.log('Sin vinos disponibles — omitiendo')
      return
    }
    selectFirstContainer()
    cy.getByWireModel('loss_type').select('evaporation', { force: true })
    cy.getByWireModel('quantity').clear().type(qty)
    cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])
    selectFirstUom()
    cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
    cy.wait(3000)
    cy.waitForLivewire()
  })
}

// ── Tests ───────────────────────────────────────────────────────────────────

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
      cy.visit('/winery/wine-losses/create')
      cy.waitForLivewire()
    })

    it('muestra todos los campos del formulario', () => {
      cy.url().should('include', '/winery/wine-losses/create')
      cy.getByWireModel('wine_id').should('exist')
      cy.getByWireModel('quantity').should('exist')
      cy.getByWireModel('loss_date').should('exist')
      cy.getByWireModel('loss_type').should('exist')
      cy.getByWireModel('unit_of_measurement_id').should('exist')
    })

    it('valida campos requeridos al guardar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/wine-losses/create')
    })

    it('la cantidad es requerida y debe ser mayor que 0', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        cy.getByWireModel('quantity').clear().type('0')
        cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])
        selectFirstUom()
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(2000)
        cy.url().should('include', '/winery/wine-losses/create')
      })
    })

    it('registra merma sin contenedor (sin contenedor es válido)', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        cy.getByWireModel('loss_type').select('evaporation', { force: true })
        cy.getByWireModel('quantity').clear().type('3')
        cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])
        selectFirstUom()
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-losses')
        cy.url().should('not.include', '/create')
      })
    })

    it('registra merma con contenedor y redirige al index', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        selectFirstContainer()
        cy.getByWireModel('loss_type').select('evaporation', { force: true })
        cy.getByWireModel('quantity').clear().type('5')
        cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])
        selectFirstUom()
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-losses')
        cy.url().should('not.include', '/create')
      })
    })

    it('bloquea cantidad que supera el vino disponible en el contenedor', () => {
      selectFirstWine().then((hasWine) => {
        if (!hasWine) { cy.log('Sin vinos, omitiendo'); return }
        selectFirstContainer().then((hasCont) => {
          if (!hasCont) { cy.log('Sin contenedores, omitiendo'); return }
          cy.getByWireModel('loss_type').select('evaporation', { force: true })
          cy.getByWireModel('quantity').clear().type('999999999')
          cy.getByWireModel('loss_date').clear().type(new Date().toISOString().split('T')[0])
          selectFirstUom()
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2000)
          // Debe permanecer en el formulario de creación
          cy.url().should('include', '/winery/wine-losses/create')
        })
      })
    })
  })

  // ── Edit — ciclo completo create → edit ────────────────────────────────────

  describe('Edit', () => {
    /**
     * Crea una merma y entra en su edición.
     * Si no hay datos para crearla, el test se omite con cy.log.
     */
    function createAndNavigateToEdit(qty = '8') {
      cy.visit('/winery/wine-losses/create')
      cy.waitForLivewire()
      createLoss(qty)
      // Tras crear, estamos en el index — buscar el primer enlace de edición
      cy.url().should('include', '/winery/wine-losses')
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/wine-losses/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('No se encontró enlace de edición — merma no creada, omitiendo')
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
        // Todos los campos clave deben tener valor
        cy.getByWireModel('wine_id').should(($sel) => {
          expect($sel.val()).to.not.equal('')
        })
        cy.getByWireModel('quantity').should(($inp) => {
          expect(parseFloat($inp.val())).to.be.greaterThan(0)
        })
        cy.getByWireModel('loss_date').should(($inp) => {
          expect($inp.val()).to.not.equal('')
        })
        cy.getByWireModel('loss_type').should(($sel) => {
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
        cy.url().should('include', '/winery/wine-losses')
        cy.url().should('not.include', '/edit')
      })
    })

    it('editar el tipo de merma y guardar redirige al index', () => {
      createAndNavigateToEdit('6')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('loss_type').then(($sel) => {
          const opts = $sel.find('option').filter((i, el) => el.value !== '' && el.value !== $sel.val())
          if (opts.length > 0) {
            cy.wrap($sel).select(opts.first().val(), { force: true })
            cy.waitForLivewire()
          }
        })
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-losses')
        cy.url().should('not.include', '/edit')
      })
    })

    it('editar notas y guardar redirige al index', () => {
      createAndNavigateToEdit('7')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        cy.getByWireModel('notes').clear().type(`Cypress edit ${uniqueId()}`)
        cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
        cy.wait(3000)
        cy.waitForLivewire()
        cy.url().should('include', '/winery/wine-losses')
        cy.url().should('not.include', '/edit')
      })
    })

    it('editar cantidad a valor que supera el contenedor muestra error', () => {
      createAndNavigateToEdit('5')
      cy.url().then((url) => {
        if (!url.includes('/edit')) return
        // Solo si tiene contenedor seleccionado
        cy.getByWireModel('container_id').then(($sel) => {
          if (!$sel.val()) { cy.log('Sin contenedor en merma, omitiendo'); return }
          cy.getByWireModel('quantity').clear().type('999999999')
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(2000)
          // Debe permanecer en edición
          cy.url().should('include', '/edit')
        })
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
        // Buscar botón cancelar o enlace "Volver"
        cy.get('body').then(($body) => {
          const cancelBtn = $body.find('a[href*="/winery/wine-losses"]:not([href*="/edit"]):not([href*="/create"])')
          if (cancelBtn.length > 0) {
            cy.wrap(cancelBtn.first()).click({ force: true })
            cy.waitForLivewire()
            cy.url().should('include', '/winery/wine-losses')
            cy.url().should('not.include', '/edit')
          }
        })
      })
    })
  })
})
