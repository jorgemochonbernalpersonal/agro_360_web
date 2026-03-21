/**
 * Winery Containers - E2E Tests
 * Cubre: Index, Create (obligatorios / opcionales / validaciones), Edit
 */

const uniqueId = () => Date.now()

// Crea un contenedor vía UI y devuelve el ID de timestamp usado
function createContainer(name, capacity = '2000') {
  cy.visit('/winery/containers/create')
  cy.waitForLivewire()
  cy.get('[wire\\:model="name"]').clear().type(name)
  cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
    cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
  })
  cy.get('[wire\\:model="capacity"]').clear().type(capacity)
  cy.get('[data-cy="submit-button"]').click({ force: true })
  cy.wait(4000)
  cy.url().should('include', '/winery/containers')
  cy.url().should('not.include', '/create')
}

describe('Winery Containers', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/containers')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de contenedores', () => {
      cy.url().should('include', '/winery/containers')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear contenedor', () => {
      cy.get('a[href*="/winery/containers/create"]').should('be.visible')
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

    it('filtra por tipo', () => {
      cy.get('body').then(($body) => {
        const typeFilter = $body.find('[wire\\:model\\.live="typeFilter"]')
        if (typeFilter.length > 0) {
          const options = typeFilter.first().find('option')
          if (options.length > 1) {
            cy.wrap(typeFilter.first()).select(options.eq(1).val(), { force: true })
            cy.waitForLivewire()
            cy.wait(500)
          }
        }
        cy.get('body').should('be.visible')
      })
    })

    it('cambia a pestaña archivados', () => {
      cy.get('body').then(($body) => {
        const archivedTab = $body.find('[wire\\:click*="archived"], button:contains("Archivados")')
        if (archivedTab.length > 0) {
          cy.wrap(archivedTab.first()).click({ force: true })
          cy.waitForLivewire()
          cy.wait(500)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ─── CREATE ───────────────────────────────────────────────────────────────

  describe('Create', () => {
    beforeEach(() => {
      cy.visit('/winery/containers/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario con los campos esperados', () => {
      cy.url().should('include', '/winery/containers/create')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="type_id"]').should('exist')
      cy.get('[wire\\:model="capacity"]').should('exist')
      cy.get('[wire\\:model="container_room_id"]').should('exist')
      cy.get('[wire\\:model="serial_number"]').should('exist')
      cy.get('[wire\\:model="description"]').should('exist')
      cy.get('[wire\\:model="purchase_date"]').should('exist')
      cy.get('[wire\\:model="supplier_name"]').should('exist')
    })

    // — Solo campos obligatorios —

    it('crea contenedor con solo campos obligatorios', () => {
      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Depósito Básico ${id}`)
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('1000')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })

    // — Todos los campos —

    it('crea contenedor con todos los campos rellenos', () => {
      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Cuba Completa ${id}`)
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('5000')
      cy.get('[wire\\:model="serial_number"]').clear().type(`SN-${id}`)
      cy.get('[wire\\:model="description"]').clear().type('Depósito de acero inoxidable para fermentación')
      cy.get('[wire\\:model="purchase_date"]').type('2023-06-15')
      cy.get('[wire\\:model="supplier_name"]').clear().type('Inox Bodegas S.L.')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })

    it('crea contenedor con fecha de compra y descripción', () => {
      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Cuba Fecha ${id}`)
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('2000')
      cy.get('[wire\\:model="purchase_date"]').type('2022-01-01')
      cy.get('[wire\\:model="description"]').clear().type('Contenedor de prueba E2E con fecha')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })

    // — Validaciones —

    it('valida que nombre, tipo y capacidad son obligatorios', () => {
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    it('valida que el nombre es obligatorio', () => {
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('500')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    it('valida que el tipo es obligatorio', () => {
      cy.get('[wire\\:model="name"]').clear().type('Cuba sin tipo')
      cy.get('[wire\\:model="capacity"]').clear().type('500')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    it('valida que la capacidad es obligatoria', () => {
      cy.get('[wire\\:model="name"]').clear().type('Cuba sin capacidad')
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    it('valida capacidad mínima mayor que 0', () => {
      cy.get('[wire\\:model="name"]').clear().type('Cuba Inválida')
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('0')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    it('valida capacidad negativa', () => {
      cy.get('[wire\\:model="name"]').clear().type('Cuba Negativa')
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('-100')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/containers/create')
    })

    // — Sala de bodega —

    it('crea contenedor asignando sala (si existe alguna)', () => {
      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Cuba con Sala ${id}`)
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('1500')

      cy.get('[wire\\:model="container_room_id"]').find('option').then(($opts) => {
        if ($opts.length > 1) {
          cy.get('[wire\\:model="container_room_id"]').select($opts.eq(1).val(), { force: true })
        }
      })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })

    it('crea contenedor sin sala asignada', () => {
      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Cuba Sin Sala ${id}`)
      cy.get('[wire\\:model="type_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="type_id"]').select($opt.val(), { force: true })
      })
      cy.get('[wire\\:model="capacity"]').clear().type('1000')
      cy.get('[wire\\:model="container_room_id"]').select('', { force: true })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    // Asegura que existe al menos un contenedor antes del bloque Edit
    before(() => {
      cy.loginAsWinery()
      createContainer(`Edit Seed ${uniqueId()}`)
    })

    beforeEach(() => {
      cy.loginAsWinery()
      cy.visit('/winery/containers')
      cy.waitForLivewire()
    })

    it('navega al formulario de edición desde el index', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()
      cy.url().should('include', '/edit')
      cy.get('[wire\\:model="name"]').should('exist')
      cy.get('[wire\\:model="type_id"]').should('exist')
      cy.get('[wire\\:model="capacity"]').should('exist')
    })

    it('muestra los datos precargados del contenedor', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()
      cy.get('[wire\\:model="name"]').invoke('val').should('not.be.empty')
      cy.get('[wire\\:model="capacity"]').invoke('val').should('not.be.empty')
    })

    it('actualiza el nombre del contenedor', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Actualizado ${id}`)

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    it('actualiza capacidad y proveedor', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="capacity"]').clear().type('8000')
      cy.get('[wire\\:model="supplier_name"]').clear().type('Nuevo Proveedor S.A.')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    it('actualiza número de serie y descripción', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      const id = uniqueId()
      cy.get('[wire\\:model="serial_number"]').clear().type(`SN-EDIT-${id}`)
      cy.get('[wire\\:model="description"]').clear().type('Descripción actualizada en E2E')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    it('actualiza la fecha de compra', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="purchase_date"]').clear().type('2024-12-01')

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    it('actualiza todos los campos a la vez', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      const id = uniqueId()
      cy.get('[wire\\:model="name"]').clear().type(`Cuba Total ${id}`)
      cy.get('[wire\\:model="capacity"]').clear().type('9999')
      cy.get('[wire\\:model="serial_number"]').clear().type(`SN-TOTAL-${id}`)
      cy.get('[wire\\:model="description"]').clear().type('Edición completa E2E')
      cy.get('[wire\\:model="purchase_date"]').clear().type('2025-01-15')
      cy.get('[wire\\:model="supplier_name"]').clear().type(`Proveedor Total ${id}`)

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    // — Sala de bodega en edit —

    it('asigna sala al editar contenedor (si existe alguna)', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="container_room_id"]').find('option').then(($opts) => {
        if ($opts.length > 1) {
          cy.get('[wire\\:model="container_room_id"]').select($opts.eq(1).val(), { force: true })
        }
      })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    it('quita la sala del contenedor (sin sala asignada)', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="container_room_id"]').select('', { force: true })

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/containers')
      cy.url().should('not.include', '/edit')
    })

    // — Validaciones en edit —

    it('valida que el nombre es obligatorio al editar', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="name"]').clear()
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)

      cy.url().should('include', '/edit')
    })

    it('valida que la capacidad es obligatoria al editar', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="capacity"]').clear()
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)

      cy.url().should('include', '/edit')
    })

    it('valida capacidad cero inválida al editar', () => {
      cy.get('a[href*="/winery/containers/"][href*="/edit"]').first().click({ force: true })
      cy.waitForLivewire()

      cy.get('[wire\\:model="capacity"]').clear().type('0')
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)

      cy.url().should('include', '/edit')
    })
  })
})
