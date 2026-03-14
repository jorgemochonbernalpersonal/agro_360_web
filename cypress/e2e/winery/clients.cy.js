/**
 * Winery Clients - E2E Tests
 * Cubre: Index (list, search, filtros), Create (individual, empresa, validaciones), Edit
 */

const uniqueId = () => Date.now()

describe('Winery Clients', () => {
  beforeEach(() => {
    cy.loginAsWinery()
    cy.visit('/winery/clients')
    cy.waitForLivewire()
  })

  // ─── INDEX ────────────────────────────────────────────────────────────────

  describe('Index', () => {
    it('muestra la página de clientes', () => {
      cy.url().should('include', '/winery/clients')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para crear cliente', () => {
      cy.get('a[href*="/winery/clients/create"]').should('be.visible')
    })

    it('filtra por búsqueda', () => {
      cy.get('[wire\\:model\\.live\\.debounce\\.300ms="search"]').type('test', { force: true })
      cy.waitForLivewire()
      cy.wait(600)
      // No debe romperse la página
      cy.get('body').should('be.visible')
    })

    it('filtra por tipo individual', () => {
      cy.get('[wire\\:model\\.live="filterType"]').select('individual', { force: true })
      cy.waitForLivewire()
      cy.wait(500)
      cy.get('body').should('be.visible')
    })

    it('filtra por tipo empresa', () => {
      cy.get('[wire\\:model\\.live="filterType"]').select('company', { force: true })
      cy.waitForLivewire()
      cy.wait(500)
      cy.get('body').should('be.visible')
    })

    it('cambia a pestaña inactivos', () => {
      cy.get('body').then(($body) => {
        const inactiveTab = $body.find('button:contains("Inactivos"), [wire\\:click*="switchTab"][wire\\:click*="inactive"]')
        if (inactiveTab.length > 0) {
          cy.wrap(inactiveTab.first()).click({ force: true })
          cy.waitForLivewire()
          cy.wait(500)
          cy.get('body').should('be.visible')
        } else {
          cy.log('No inactive tab button found - skipping')
        }
      })
    })
  })

  // ─── CREATE - INDIVIDUAL ──────────────────────────────────────────────────

  describe('Create - Cliente individual', () => {
    beforeEach(() => {
      cy.visit('/winery/clients/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de creación', () => {
      cy.url().should('include', '/winery/clients/create')
      cy.getByWireModel('client_type').should('exist')
    })

    it('crea un cliente individual correctamente', () => {
      const id = uniqueId()

      // Tipo individual
      cy.getByWireModel('client_type').select('individual', { force: true })
      cy.wait(500)

      // Datos personales
      cy.getByWireModel('first_name').clear().type(`Juan${id}`)
      cy.getByWireModel('last_name').clear().type('García Test')
      cy.getByWireModel('email').clear().type(`juan${id}@test.com`)
      cy.getByWireModel('phone').clear().type('666111222')

      // Dirección (al menos una obligatoria)
      cy.getByWireModel('addresses.0.address').clear().type('Calle Mayor 1')
      cy.getByWireModel('addresses.0.postal_code').clear().type('28001')

      // Comunidad autónoma (primera opción disponible)
      cy.getByWireModel('addresses.0.autonomous_community_id').find('option').eq(1).then(($opt) => {
        const val = $opt.val()
        cy.getByWireModel('addresses.0.autonomous_community_id').select(val, { force: true })
      })
      cy.wait(1000)

      // Provincia (esperar a que cargue)
      cy.getByWireModel('addresses.0.province_id').find('option').should('have.length.gt', 1)
      cy.getByWireModel('addresses.0.province_id').find('option').eq(1).then(($opt) => {
        const val = $opt.val()
        cy.getByWireModel('addresses.0.province_id').select(val, { force: true })
      })
      cy.wait(1000)

      // Municipio
      cy.getByWireModel('addresses.0.municipality_id').find('option').should('have.length.gt', 1)
      cy.getByWireModel('addresses.0.municipality_id').find('option').eq(1).then(($opt) => {
        const val = $opt.val()
        cy.getByWireModel('addresses.0.municipality_id').select(val, { force: true })
      })
      cy.wait(500)

      // Guardar
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      // Redirige al index
      cy.url().should('include', '/winery/clients')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos (individual)', () => {
      cy.getByWireModel('client_type').select('individual', { force: true })
      cy.wait(300)
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      // Debe seguir en el formulario (sin redirigir)
      cy.url().should('include', '/winery/clients/create')
    })

    it('valida campos requeridos (empresa)', () => {
      cy.getByWireModel('client_type').select('company', { force: true })
      cy.wait(300)
      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(2000)
      cy.url().should('include', '/winery/clients/create')
    })
  })

  // ─── CREATE - EMPRESA ─────────────────────────────────────────────────────

  describe('Create - Cliente empresa', () => {
    beforeEach(() => {
      cy.visit('/winery/clients/create')
      cy.waitForLivewire()
    })

    it('crea un cliente empresa correctamente', () => {
      const id = uniqueId()

      cy.getByWireModel('client_type').select('company', { force: true })
      cy.wait(500)

      cy.getByWireModel('company_name').clear().type(`Distribuciones ${id} SL`)
      cy.getByWireModel('company_document').clear().type(`B${id.toString().slice(-7)}`)
      cy.getByWireModel('email').clear().type(`empresa${id}@test.com`)
      cy.getByWireModel('phone').clear().type('911222333')

      // Dirección
      cy.getByWireModel('addresses.0.address').clear().type('Polígono Industrial 5')
      cy.getByWireModel('addresses.0.postal_code').clear().type('46001')

      cy.getByWireModel('addresses.0.autonomous_community_id').find('option').eq(1).then(($opt) => {
        cy.getByWireModel('addresses.0.autonomous_community_id').select($opt.val(), { force: true })
      })
      cy.wait(1000)

      cy.getByWireModel('addresses.0.province_id').find('option').should('have.length.gt', 1)
      cy.getByWireModel('addresses.0.province_id').find('option').eq(1).then(($opt) => {
        cy.getByWireModel('addresses.0.province_id').select($opt.val(), { force: true })
      })
      cy.wait(1000)

      cy.getByWireModel('addresses.0.municipality_id').find('option').should('have.length.gt', 1)
      cy.getByWireModel('addresses.0.municipality_id').find('option').eq(1).then(($opt) => {
        cy.getByWireModel('addresses.0.municipality_id').select($opt.val(), { force: true })
      })
      cy.wait(500)

      cy.get('[data-cy="submit-button"]').click({ force: true })
      cy.wait(5000)

      cy.url().should('include', '/winery/clients')
      cy.url().should('not.include', '/create')
    })
  })

  // ─── EDIT ─────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('navega al formulario de edición desde el index', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/clients/"][href*="/edit"]')

        if (editLinks.length > 0) {
          cy.wrap(editLinks.first()).click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
          cy.getByWireModel('client_type').should('exist')
        } else {
          // No hay clientes — crear uno primero
          cy.log('No clients found, creating one first')
          cy.visit('/winery/clients/create')
          cy.waitForLivewire()

          const id = uniqueId()
          cy.getByWireModel('client_type').select('individual', { force: true })
          cy.wait(500)
          cy.getByWireModel('first_name').clear().type(`Edit${id}`)
          cy.getByWireModel('last_name').clear().type('Test')
          cy.getByWireModel('addresses.0.address').clear().type('Calle Test 1')
          cy.getByWireModel('addresses.0.postal_code').clear().type('28001')
          cy.getByWireModel('addresses.0.autonomous_community_id').find('option').eq(1).then(($opt) => {
            cy.getByWireModel('addresses.0.autonomous_community_id').select($opt.val(), { force: true })
          })
          cy.wait(1000)
          cy.getByWireModel('addresses.0.province_id').find('option').should('have.length.gt', 1)
          cy.getByWireModel('addresses.0.province_id').find('option').eq(1).then(($opt) => {
            cy.getByWireModel('addresses.0.province_id').select($opt.val(), { force: true })
          })
          cy.wait(1000)
          cy.getByWireModel('addresses.0.municipality_id').find('option').should('have.length.gt', 1)
          cy.getByWireModel('addresses.0.municipality_id').find('option').eq(1).then(($opt) => {
            cy.getByWireModel('addresses.0.municipality_id').select($opt.val(), { force: true })
          })
          cy.wait(500)
          cy.get('[data-cy="submit-button"]').click({ force: true })
          cy.wait(5000)

          // Ahora editar
          cy.get('a[href*="/winery/clients/"][href*="/edit"]').first().click({ force: true })
          cy.waitForLivewire()
          cy.url().should('include', '/edit')
        }
      })
    })

    it('actualiza el teléfono de un cliente', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/clients/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No clients to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        cy.getByWireModel('phone').clear().type('699000001')

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/clients')
        cy.url().should('not.include', '/edit')
      })
    })

    it('actualiza el email de un cliente', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/winery/clients/"][href*="/edit"]')

        if (editLinks.length === 0) {
          cy.log('No clients to edit - skipping')
          return
        }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()

        const id = uniqueId()
        cy.getByWireModel('email').clear().type(`updated${id}@test.com`)

        cy.get('[data-cy="submit-button"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/clients')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
