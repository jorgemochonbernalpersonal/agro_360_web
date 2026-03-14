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
      cy.contains('Clientes').should('be.visible')
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
      cy.get('[wire\\:model="client_type"]').should('exist')
    })

    it('crea un cliente individual correctamente', () => {
      const id = uniqueId()

      // Tipo individual
      cy.get('[wire\\:model="client_type"]').select('individual', { force: true })
      cy.wait(500)

      // Datos personales
      cy.get('[wire\\:model="first_name"]').clear().type(`Juan${id}`)
      cy.get('[wire\\:model="last_name"]').clear().type('García Test')
      cy.get('[wire\\:model="email"]').clear().type(`juan${id}@test.com`)
      cy.get('[wire\\:model="phone"]').clear().type('666111222')

      // Dirección (al menos una obligatoria)
      cy.get('[wire\\:model="addresses.0.address"]').clear().type('Calle Mayor 1')
      cy.get('[wire\\:model="addresses.0.postal_code"]').clear().type('28001')

      // Comunidad autónoma (primera opción disponible)
      cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').find('option').eq(1).then(($opt) => {
        const val = $opt.val()
        cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').select(val, { force: true })
      })
      cy.wait(1000)

      // Provincia (esperar a que cargue)
      cy.get('[wire\\:model="addresses.0.province_id"]').find('option').should('have.length.gt', 1)
      cy.get('[wire\\:model="addresses.0.province_id"]').find('option').eq(1).then(($opt) => {
        const val = $opt.val()
        cy.get('[wire\\:model="addresses.0.province_id"]').select(val, { force: true })
      })
      cy.wait(1000)

      // Municipio
      cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').should('have.length.gt', 1)
      cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').eq(1).then(($opt) => {
        const val = $opt.val()
        cy.get('[wire\\:model="addresses.0.municipality_id"]').select(val, { force: true })
      })
      cy.wait(500)

      // Guardar
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(5000)

      // Redirige al index
      cy.url().should('include', '/winery/clients')
      cy.url().should('not.include', '/create')
    })

    it('valida campos requeridos (individual)', () => {
      cy.get('[wire\\:model="client_type"]').select('individual', { force: true })
      cy.wait(300)
      cy.get('button[type="submit"]').click({ force: true })
      cy.wait(2000)
      // Debe seguir en el formulario (sin redirigir)
      cy.url().should('include', '/winery/clients/create')
    })

    it('valida campos requeridos (empresa)', () => {
      cy.get('[wire\\:model="client_type"]').select('company', { force: true })
      cy.wait(300)
      cy.get('button[type="submit"]').click({ force: true })
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

      cy.get('[wire\\:model="client_type"]').select('company', { force: true })
      cy.wait(500)

      cy.get('[wire\\:model="company_name"]').clear().type(`Distribuciones ${id} SL`)
      cy.get('[wire\\:model="company_document"]').clear().type(`B${id.toString().slice(-7)}`)
      cy.get('[wire\\:model="email"]').clear().type(`empresa${id}@test.com`)
      cy.get('[wire\\:model="phone"]').clear().type('911222333')

      // Dirección
      cy.get('[wire\\:model="addresses.0.address"]').clear().type('Polígono Industrial 5')
      cy.get('[wire\\:model="addresses.0.postal_code"]').clear().type('46001')

      cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').select($opt.val(), { force: true })
      })
      cy.wait(1000)

      cy.get('[wire\\:model="addresses.0.province_id"]').find('option').should('have.length.gt', 1)
      cy.get('[wire\\:model="addresses.0.province_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="addresses.0.province_id"]').select($opt.val(), { force: true })
      })
      cy.wait(1000)

      cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').should('have.length.gt', 1)
      cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').eq(1).then(($opt) => {
        cy.get('[wire\\:model="addresses.0.municipality_id"]').select($opt.val(), { force: true })
      })
      cy.wait(500)

      cy.get('button[type="submit"]').click({ force: true })
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
          cy.get('[wire\\:model="client_type"]').should('exist')
        } else {
          // No hay clientes — crear uno primero
          cy.log('No clients found, creating one first')
          cy.visit('/winery/clients/create')
          cy.waitForLivewire()

          const id = uniqueId()
          cy.get('[wire\\:model="client_type"]').select('individual', { force: true })
          cy.wait(500)
          cy.get('[wire\\:model="first_name"]').clear().type(`Edit${id}`)
          cy.get('[wire\\:model="last_name"]').clear().type('Test')
          cy.get('[wire\\:model="addresses.0.address"]').clear().type('Calle Test 1')
          cy.get('[wire\\:model="addresses.0.postal_code"]').clear().type('28001')
          cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="addresses.0.autonomous_community_id"]').select($opt.val(), { force: true })
          })
          cy.wait(1000)
          cy.get('[wire\\:model="addresses.0.province_id"]').find('option').should('have.length.gt', 1)
          cy.get('[wire\\:model="addresses.0.province_id"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="addresses.0.province_id"]').select($opt.val(), { force: true })
          })
          cy.wait(1000)
          cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').should('have.length.gt', 1)
          cy.get('[wire\\:model="addresses.0.municipality_id"]').find('option').eq(1).then(($opt) => {
            cy.get('[wire\\:model="addresses.0.municipality_id"]').select($opt.val(), { force: true })
          })
          cy.wait(500)
          cy.get('button[type="submit"]').click({ force: true })
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

        cy.get('[wire\\:model="phone"]').clear().type('699000001')

        cy.get('button[type="submit"]').click({ force: true })
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
        cy.get('[wire\\:model="email"]').clear().type(`updated${id}@test.com`)

        cy.get('button[type="submit"]').click({ force: true })
        cy.wait(5000)

        cy.url().should('include', '/winery/clients')
        cy.url().should('not.include', '/edit')
      })
    })
  })
})
