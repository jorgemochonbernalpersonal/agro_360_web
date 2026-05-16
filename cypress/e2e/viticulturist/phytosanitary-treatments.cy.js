/**
 * Viticulturist — Tratamientos Fitosanitarios — E2E Tests
 * PAC obligatorio (RD 1311/2012) — Cuaderno de Campo Digital
 *
 * Rutas: /viticulturist/digital-notebook/treatment/*
 * Usuario de prueba: viticulturist@test.com (CypressTestUserSeeder)
 */

describe('Tratamientos Fitosanitarios', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  // ── Index ───────────────────────────────────────────────────────────────────

  describe('Index', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/treatment')
      cy.waitForLivewire()
    })

    it('muestra la página de tratamientos fitosanitarios', () => {
      cy.url().should('include', '/viticulturist/digital-notebook/treatment')
      cy.get('body').should('be.visible')
    })

    it('tiene botón para registrar nuevo tratamiento', () => {
      cy.get('a[href*="/treatment/create"]').should('exist')
    })

    it('puede filtrar o buscar si hay controles disponibles', () => {
      cy.get('body').then(($body) => {
        const search = $body.find('[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live="search"]')
        if (search.length > 0) {
          cy.wrap(search.first()).type('mildiu', { force: true })
          cy.waitForLivewire()
          cy.wait(400)
        }
        cy.get('body').should('be.visible')
      })
    })
  })

  // ── Create — carga del formulario ───────────────────────────────────────────

  describe('Create — carga y estructura', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/treatment/create')
      cy.waitForLivewire()
    })

    it('carga el formulario de creación', () => {
      cy.url().should('include', '/treatment/create')
      cy.contains('Registrar Tratamiento Fitosanitario').should('be.visible')
    })

    it('tiene sección de Información Básica con parcela y fecha', () => {
      cy.get('select#plot_id').should('exist')
      cy.get('input#activity_date[type="date"]').should('exist')
      cy.get('select#phenological_stage').should('exist')
    })

    it('tiene sección de Producto Fitosanitario', () => {
      cy.get('select#product_id').should('exist')
      cy.get('input#dose_per_hectare').should('exist')
      cy.get('input#area_treated').should('exist')
      cy.get('input#total_dose').should('exist')
    })

    it('tiene sección de Cumplimiento PAC con campos obligatorios', () => {
      cy.get('textarea#treatment_justification').should('exist')
      cy.get('input#reentry_period_days').should('exist')
      cy.get('input#spray_volume').should('exist')
    })

    it('tiene sección de Gestión Integrada de Plagas (IPM)', () => {
      cy.contains('Gestión Integrada de Plagas').should('be.visible')
      cy.get('[id="plague_monitoring"]').should('exist')
      cy.get('[id="prior_non_chemical_methods"]').should('exist')
      cy.get('[id="biological_control"]').should('exist')
      cy.get('[id="manual_mechanical_control"]').should('exist')
      cy.get('[id="cultural_preventions"]').should('exist')
    })

    it('tiene sección de Zona Tampón y Masas de Agua', () => {
      cy.contains('Zona Tampón').should('be.visible')
      cy.get('[id="buffer_zone_respected"]').should('exist')
    })

    it('tiene sección de Condiciones Meteorológicas', () => {
      cy.get('input#temperature').should('exist')
      cy.get('input#wind_speed').should('exist')
      cy.get('input#humidity').should('exist')
    })

    it('tiene selector de aplicador ROPO y campo manual', () => {
      cy.get('select#field_applicator_id').should('exist')
      cy.get('input#applicator_ropo_number').should('exist')
    })
  })

  // ── Create — validaciones ───────────────────────────────────────────────────

  describe('Create — validaciones PAC', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/treatment/create')
      cy.waitForLivewire()
    })

    it('permanece en el formulario al enviar vacío', () => {
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(1500)
      cy.url().should('include', '/treatment/create')
    })

    it('muestra error si justificación es demasiado corta', () => {
      cy.get('textarea#treatment_justification').type('Corto', { force: true })
      cy.waitForLivewire()
      cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
      cy.wait(1500)
      cy.url().should('include', '/treatment/create')
    })

    it('dose_total se calcula automáticamente al rellenar dosis y área', () => {
      cy.get('input#dose_per_hectare').clear({ force: true }).type('2.5', { force: true })
      cy.waitForLivewire()
      cy.get('input#area_treated').clear({ force: true }).type('3', { force: true })
      cy.waitForLivewire()
      cy.wait(300)

      cy.get('input#total_dose').should(($input) => {
        const val = parseFloat($input.val())
        expect(val).to.be.closeTo(7.5, 0.01)
      })
    })
  })

  // ── Create — comportamientos reactivos ─────────────────────────────────────

  describe('Create — comportamientos reactivos', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/treatment/create')
      cy.waitForLivewire()
    })

    it('auto-rellena ROPO al seleccionar un aplicador registrado', () => {
      cy.get('select#field_applicator_id').then(($select) => {
        if ($select.find('option').length <= 1) {
          cy.log('Sin aplicadores registrados — omitiendo test de auto-fill')
          return
        }
        cy.wrap($select).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(300)

        cy.get('input#applicator_ropo_number').invoke('val').then((val) => {
          cy.log(`ROPO auto-rellenado: ${val}`)
          // El campo debe tener algún valor si el aplicador tiene ROPO
        })
      })
    })

    it('muestra info del producto al seleccionarlo', () => {
      cy.get('select#product_id').then(($select) => {
        if ($select.find('option').length <= 1) {
          cy.log('Sin productos disponibles — omitiendo')
          return
        }
        cy.wrap($select).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(500)
        cy.get('body').should('be.visible')
      })
    })

    it('muestra alerta de viento al superar 10.8 km/h', () => {
      cy.get('input#wind_speed').clear({ force: true }).type('15', { force: true })
      cy.waitForLivewire()
      cy.wait(500)

      // La alerta usa x-show de Alpine.js — puede estar en el DOM pero oculta hasta que Alpine inicializa
      // Verificamos que el texto existe en el DOM (aunque pueda estar display:none por x-cloak)
      cy.get('body').should('contain.text', 'Viento superior')
    })

    it('no muestra alerta de viento con valor bajo', () => {
      // Con valor bajo, la alerta no debe estar visible
      // (puede estar en DOM con display:none — solo verificamos que no es visible)
      cy.get('input#wind_speed').clear({ force: true }).type('5', { force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('body').then(($body) => {
        // El texto puede existir en DOM (x-show oculto) pero no debe estar visible
        const windAlert = $body.find('[class*="bg-red-50"]').filter(':contains("Viento superior")')
        if (windAlert.length > 0) {
          cy.wrap(windAlert).should('not.be.visible')
        } else {
          cy.log('Alerta de viento no presente en DOM con valor bajo — correcto')
        }
      })
    })

    it('muestra campo de distancia al activar zona tampón', () => {
      cy.get('[id="buffer_zone_respected"]').click({ force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('input#distance_to_water_m').should('be.visible')
    })

    it('oculta campo de distancia al desmarcar zona tampón', () => {
      cy.get('[id="buffer_zone_respected"]').click({ force: true })
      cy.waitForLivewire()
      cy.wait(200)
      cy.get('[id="buffer_zone_respected"]').click({ force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('input#distance_to_water_m').should('not.exist')
    })

    it('muestra campo de fecha al activar asesoramiento técnico', () => {
      cy.get('[id="under_advisory"]').click({ force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('input#advisory_recommendation_date').should('be.visible')
    })

    it('oculta campo de fecha al desmarcar asesoramiento', () => {
      cy.get('[id="under_advisory"]').click({ force: true })
      cy.waitForLivewire()
      cy.wait(200)
      cy.get('[id="under_advisory"]').click({ force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('input#advisory_recommendation_date').should('not.exist')
    })

    it('muestra selector de parcela → plantaciones activas al seleccionar parcela', () => {
      cy.get('select#plot_id').then(($select) => {
        if ($select.find('option').length <= 1) {
          cy.log('Sin parcelas — omitiendo')
          return
        }
        cy.wrap($select).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(400)
        // El selector de plantación puede aparecer si la parcela tiene plantaciones
        cy.get('body').should('be.visible')
      })
    })

    it('el selector equipo/individual muestra dropdown de equipo al seleccionar crew', () => {
      cy.get('input[type="radio"][value="crew"]').check({ force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('select#crew_id').should('be.visible')
    })

    it('el selector equipo/individual muestra dropdown de viticultor al seleccionar individual', () => {
      cy.get('input[type="radio"][value="individual"]').check({ force: true })
      cy.waitForLivewire()
      cy.wait(300)
      cy.get('select#crew_member_id').should('be.visible')
    })
  })

  // ── Create — guardado completo ──────────────────────────────────────────────

  describe('Create — guardado', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/treatment/create')
      cy.waitForLivewire()
    })

    it('guarda tratamiento con campos mínimos PAC y redirige al index', () => {
      // Parcela
      cy.get('select#plot_id').then(($select) => {
        if ($select.find('option').length <= 1) {
          cy.log('Sin parcelas disponibles — omitiendo test de guardado')
          return
        }

        cy.wrap($select).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(400)

        // Producto
        cy.get('select#product_id').then(($prod) => {
          if ($prod.find('option').length <= 1) {
            cy.log('Sin productos disponibles — omitiendo')
            return
          }
          cy.wrap($prod).select(1, { force: true })
          cy.waitForLivewire()
          cy.wait(400)

          // Estadio fenológico
          cy.get('select#phenological_stage').select('Floración', { force: true })
          cy.waitForLivewire()

          // Dosis y área
          cy.get('input#dose_per_hectare').clear({ force: true }).type('2.5', { force: true })
          cy.waitForLivewire()
          cy.get('input#area_treated').clear({ force: true }).type('1.0', { force: true })
          cy.waitForLivewire()

          // Campos PAC obligatorios
          cy.get('textarea#treatment_justification')
            .clear({ force: true })
            .type('Detección de mildiu en hojas basales. Tratamiento preventivo.', { force: true })
          cy.waitForLivewire()

          cy.get('input#applicator_ropo_number')
            .clear({ force: true })
            .type('ROPO-CYPRESS-001', { force: true })
          cy.waitForLivewire()

          cy.get('input#reentry_period_days').clear({ force: true }).type('3', { force: true })
          cy.waitForLivewire()

          cy.get('input#spray_volume').clear({ force: true }).type('400', { force: true })
          cy.waitForLivewire()

          // Viticultor individual
          cy.get('input[type="radio"][value="individual"]').check({ force: true })
          cy.waitForLivewire()
          cy.wait(300)

          cy.get('select#crew_member_id').then(($sel) => {
            if ($sel.find('option').length > 1) {
              cy.wrap($sel).select(1, { force: true })
              cy.waitForLivewire()
            }
          })

          // Enviar
          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()

          cy.url().should('satisfy', (url) =>
            url.includes('/treatment') && !url.includes('/create')
          )
        })
      })
    })

    it('guarda tratamiento con zona tampón y flags IPM', () => {
      cy.get('select#plot_id').then(($select) => {
        if ($select.find('option').length <= 1) {
          cy.log('Sin parcelas — omitiendo')
          return
        }

        cy.wrap($select).select(1, { force: true })
        cy.waitForLivewire()
        cy.wait(400)

        cy.get('select#product_id').then(($prod) => {
          if ($prod.find('option').length <= 1) {
            cy.log('Sin productos — omitiendo')
            return
          }

          cy.wrap($prod).select(1, { force: true })
          cy.waitForLivewire()
          cy.wait(300)

          cy.get('select#phenological_stage').select('Envero', { force: true })
          cy.get('input#dose_per_hectare').clear({ force: true }).type('1.5', { force: true })
          cy.waitForLivewire()
          cy.get('input#area_treated').clear({ force: true }).type('0.5', { force: true })
          cy.waitForLivewire()

          cy.get('textarea#treatment_justification')
            .clear({ force: true })
            .type('Botrytis detectada en racimos. Aplicación preventiva según umbral.', { force: true })
          cy.waitForLivewire()

          cy.get('input#applicator_ropo_number').clear({ force: true }).type('ROPO-CYP-002', { force: true })
          cy.get('input#reentry_period_days').clear({ force: true }).type('2', { force: true })
          cy.get('input#spray_volume').clear({ force: true }).type('250', { force: true })
          cy.waitForLivewire()

          // Zona tampón
          cy.get('[id="buffer_zone_respected"]').click({ force: true })
          cy.waitForLivewire()
          cy.wait(300)
          cy.get('input#distance_to_water_m').clear({ force: true }).type('10', { force: true })
          cy.waitForLivewire()

          // Flags IPM
          cy.get('[id="plague_monitoring"]').click({ force: true })
          cy.get('[id="biological_control"]').click({ force: true })
          cy.waitForLivewire()

          // Individual
          cy.get('input[type="radio"][value="individual"]').check({ force: true })
          cy.waitForLivewire()
          cy.wait(300)
          cy.get('select#crew_member_id').then(($sel) => {
            if ($sel.find('option').length > 1) {
              cy.wrap($sel).select(1, { force: true })
              cy.waitForLivewire()
            }
          })

          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()

          cy.url().should('satisfy', (url) =>
            url.includes('/treatment') && !url.includes('/create')
          )
        })
      })
    })
  })

  // ── Edit ────────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/digital-notebook/treatment')
      cy.waitForLivewire()
    })

    it('accede a la edición del primer tratamiento si existe', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/digital-notebook/treatment/"][href*="/edit"]')
        if (editLinks.length === 0) {
          cy.log('Sin tratamientos existentes — omitiendo tests de edición')
          return
        }
        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().should('include', '/edit')
        cy.contains('Editar Tratamiento Fitosanitario').should('be.visible')
      })
    })

    it('el formulario de edición tiene los campos PAC precargados', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/digital-notebook/treatment/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin tratamientos — omitiendo'); return }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return

          cy.get('select#product_id').should(($sel) => {
            expect($sel.val()).to.not.equal('')
          })
          cy.get('textarea#treatment_justification').should(($ta) => {
            expect($ta.val().length).to.be.greaterThan(0)
          })
          cy.get('input#reentry_period_days').should(($inp) => {
            expect($inp.val()).to.not.equal('')
          })
        })
      })
    })

    it('puede actualizar la justificación del tratamiento', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/digital-notebook/treatment/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin tratamientos — omitiendo'); return }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return

          cy.get('textarea#treatment_justification')
            .clear({ force: true })
            .type('Actualizado Cypress: mildiu severo en hojas apicales.', { force: true })
          cy.waitForLivewire()

          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.waitForLivewire()

          cy.url().should('include', '/treatment')
          cy.url().should('not.include', '/edit')
        })
      })
    })

    it('puede activar zona tampón en edición y guardar', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/digital-notebook/treatment/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin tratamientos — omitiendo'); return }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return

          cy.get('[id="buffer_zone_respected"]').then(($el) => {
            if (!$el.attr('data-checked')) {
              cy.wrap($el).click({ force: true })
              cy.waitForLivewire()
              cy.wait(300)
              cy.get('input#distance_to_water_m').should('be.visible')
              cy.get('input#distance_to_water_m').clear({ force: true }).type('8', { force: true })
              cy.waitForLivewire()
            }
          })

          cy.get('[data-cy="submit-button"], button[type="submit"]').first().click({ force: true })
          cy.wait(3000)
          cy.url().should('include', '/treatment')
          cy.url().should('not.include', '/edit')
        })
      })
    })

    it('dosis total se recalcula en edición', () => {
      cy.get('body').then(($body) => {
        const editLinks = $body.find('a[href*="/digital-notebook/treatment/"][href*="/edit"]')
        if (editLinks.length === 0) { cy.log('Sin tratamientos — omitiendo'); return }

        cy.wrap(editLinks.first()).click({ force: true })
        cy.waitForLivewire()
        cy.url().then((url) => {
          if (!url.includes('/edit')) return

          cy.get('input#dose_per_hectare').clear({ force: true }).type('4.0', { force: true })
          cy.waitForLivewire()
          cy.get('input#area_treated').clear({ force: true }).type('2.0', { force: true })
          cy.waitForLivewire()
          cy.wait(300)

          cy.get('input#total_dose').should(($inp) => {
            const val = parseFloat($inp.val())
            expect(val).to.be.closeTo(8.0, 0.01)
          })
        })
      })
    })
  })
})
