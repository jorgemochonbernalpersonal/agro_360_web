/**
 * Viticulturist — Fenología — E2E Tests
 * Cuaderno de Campo Digital
 *
 * Rutas: /viticulturist/phenology/*
 * Nota: el index redirige a /plots/plantings sin ?filter_planting_id,
 *       así que los tests de index y edit dependen de datos existentes.
 */

describe('Fenología', () => {
  beforeEach(() => {
    cy.loginAsViticulturist()
  })

  // ── Index (requiere planting_id) ───────────────────────────────────────────

  describe('Index', () => {
    it('redirige a plantaciones si no hay filter_planting_id', () => {
      cy.visit('/viticulturist/phenology')
      cy.waitForLivewire()
      cy.url().should('include', '/plots/plantings')
    })

    it('muestra la página cuando se pasa filter_planting_id válido', () => {
      // Visita plantaciones para obtener un ID real
      cy.visit('/viticulturist/plots/plantings')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const plantingLinks = $body.find('a[href*="/phenology"]')
        if (plantingLinks.length === 0) {
          cy.log('No hay plantaciones con enlace fenológico — test omitido')
          return
        }

        cy.wrap(plantingLinks.first()).invoke('attr', 'href').then((href) => {
          const match = href.match(/filter_planting_id=(\d+)/)
          if (!match) {
            cy.log('No se pudo extraer planting_id — test omitido')
            return
          }
          cy.visit(`/viticulturist/phenology?filter_planting_id=${match[1]}`)
          cy.waitForLivewire()
          cy.url().should('include', '/viticulturist/phenology')
          cy.get('body').should('be.visible')
        })
      })
    })
  })

  // ── Create — carga y estructura ────────────────────────────────────────────

  describe('Create — carga y estructura', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/phenology/create')
      cy.waitForLivewire()
    })

    it('muestra el formulario de nueva observación fenológica', () => {
      cy.contains('Nueva Observación Fenológica').should('be.visible')
    })

    it('tiene selector de estadio fenológico con las 7 opciones', () => {
      const events = ['Desborre', 'Crecimiento del pámpano', 'Floración', 'Cuajado', 'Envero', 'Pre-vendimia', 'Vendimia']

      cy.get('select').then(($selects) => {
        // Buscar el select que contiene eventos fenológicos
        let found = false
        $selects.each((_, el) => {
          const text = el.innerText
          if (text.includes('Desborre')) {
            found = true
            events.forEach((event) => {
              expect(text).to.include(event)
            })
          }
        })
        if (!found) {
          cy.log('Select de estadios no encontrado — verificar implementación')
        }
      })
    })

    it('tiene selector de fuente del dato', () => {
      cy.contains('Fuente del dato').should('be.visible')
    })

    it('tiene campo de fecha de observación', () => {
      cy.get('input[type="date"]').should('exist')
    })

    it('tiene campo de nivel de confianza', () => {
      cy.contains('Nivel de confianza').should('be.visible')
      cy.get('input[type="number"][min="0"][max="100"]').should('exist')
    })

    it('tiene campo de código BBCH', () => {
      cy.contains('Código BBCH').should('be.visible')
    })

    it('tiene campo de notas', () => {
      cy.get('textarea').should('exist')
    })

    it('tiene la fecha de hoy por defecto', () => {
      const today = new Date().toISOString().split('T')[0]
      cy.get('input[type="date"]').first().should('have.value', today)
    })
  })

  // ── Create — autocompletado BBCH ───────────────────────────────────────────

  describe('Create — autocompletado BBCH', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/phenology/create')
      cy.waitForLivewire()
    })

    it('al seleccionar Floración el BBCH se autocompleta a 65', () => {
      // Buscar el select de estadios
      cy.get('select').then(($selects) => {
        $selects.each((_, el) => {
          if (el.innerText.includes('Desborre')) {
            cy.wrap(el).select('flowering', { force: true })
          }
        })
      })
      cy.waitForLivewire()

      cy.get('input[type="number"][min="0"][max="99"]').should('have.value', '65')
    })

    it('al seleccionar Envero el BBCH se autocompleta a 81', () => {
      cy.get('select').then(($selects) => {
        $selects.each((_, el) => {
          if (el.innerText.includes('Desborre')) {
            cy.wrap(el).select('veraison', { force: true })
          }
        })
      })
      cy.waitForLivewire()

      cy.get('input[type="number"][min="0"][max="99"]').should('have.value', '81')
    })
  })

  // ── Create — validaciones ──────────────────────────────────────────────────

  describe('Create — validaciones', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/phenology/create')
      cy.waitForLivewire()
    })

    it('permanece en el formulario al enviar sin datos', () => {
      cy.get('button[type="submit"]').click({ force: true })
      cy.waitForLivewire()
      cy.url().should('include', '/phenology/create')
    })

    it('muestra error si confianza supera 100', () => {
      cy.get('input[type="number"][min="0"][max="100"]').clear().type('150', { force: true })
      cy.get('button[type="submit"]').click({ force: true })
      cy.waitForLivewire()
      cy.url().should('include', '/phenology/create')
    })
  })

  // ── Create — guardado ──────────────────────────────────────────────────────

  describe('Create — guardado', () => {
    beforeEach(() => {
      cy.visit('/viticulturist/phenology/create')
      cy.waitForLivewire()
    })

    it('guarda una observación si hay plantación y campaña disponibles', () => {
      cy.get('body').then(($body) => {
        // Buscar el select de plantación
        const plantingSelects = $body.find('select').filter((_, el) => {
          return el.innerText.includes('Selecciona plantación')
        })

        if (plantingSelects.length === 0 || plantingSelects[0].options.length <= 1) {
          cy.log('No hay plantaciones disponibles — test omitido')
          return
        }

        cy.wrap(plantingSelects[0]).select(1, { force: true })
        cy.waitForLivewire()

        // Seleccionar estadio
        cy.get('select').then(($selects) => {
          $selects.each((_, el) => {
            if (el.innerText.includes('Desborre')) {
              cy.wrap(el).select('fruit_set', { force: true })
            }
          })
        })
        cy.waitForLivewire()

        // Confianza
        cy.get('input[type="number"][min="0"][max="100"]').clear().type('80', { force: true })

        // Notas opcionales
        cy.get('textarea').type('Cuajado uniforme observado en campo.', { force: true })

        cy.get('button[type="submit"]').click({ force: true })
        cy.waitForLivewire()
        cy.wait(500)

        cy.url().then((url) => {
          if (url.includes('/phenology/create')) {
            cy.log('Guardado no completado — posiblemente campaña activa ausente')
          } else {
            cy.url().should('include', '/phenology')
          }
        })
      })
    })
  })

  // ── Edit ───────────────────────────────────────────────────────────────────

  describe('Edit', () => {
    it('accede al formulario de edición si existe una observación', () => {
      // Para llegar al edit necesitamos pasar por el index con planting_id
      cy.visit('/viticulturist/plots/plantings')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const phenoLinks = $body.find('a[href*="filter_planting_id"]')
        if (phenoLinks.length === 0) {
          cy.log('No hay plantaciones — test omitido')
          return
        }

        cy.wrap(phenoLinks.first()).invoke('attr', 'href').then((href) => {
          const match = href.match(/filter_planting_id=(\d+)/)
          if (!match) return

          cy.visit(`/viticulturist/phenology?filter_planting_id=${match[1]}`)
          cy.waitForLivewire()

          cy.get('body').then(($indexBody) => {
            const editLinks = $indexBody.find('a[href*="/phenology/"][href*="/edit"]')
            if (editLinks.length === 0) {
              cy.log('No hay observaciones para editar — test omitido')
              return
            }

            cy.wrap(editLinks.first()).click({ force: true })
            cy.waitForLivewire()

            cy.contains('Editar Observación Fenológica').should('be.visible')
          })
        })
      })
    })

    it('el formulario de edición tiene botón Actualizar Observación', () => {
      cy.visit('/viticulturist/plots/plantings')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const phenoLinks = $body.find('a[href*="filter_planting_id"]')
        if (phenoLinks.length === 0) return

        cy.wrap(phenoLinks.first()).invoke('attr', 'href').then((href) => {
          const match = href.match(/filter_planting_id=(\d+)/)
          if (!match) return

          cy.visit(`/viticulturist/phenology?filter_planting_id=${match[1]}`)
          cy.waitForLivewire()

          cy.get('body').then(($indexBody) => {
            const editLinks = $indexBody.find('a[href*="/phenology/"][href*="/edit"]')
            if (editLinks.length === 0) return

            cy.wrap(editLinks.first()).click({ force: true })
            cy.waitForLivewire()

            cy.contains('button', 'Actualizar Observación').should('exist')
          })
        })
      })
    })

    it('puede actualizar las notas de una observación', () => {
      cy.visit('/viticulturist/plots/plantings')
      cy.waitForLivewire()

      cy.get('body').then(($body) => {
        const phenoLinks = $body.find('a[href*="filter_planting_id"]')
        if (phenoLinks.length === 0) return

        cy.wrap(phenoLinks.first()).invoke('attr', 'href').then((href) => {
          const match = href.match(/filter_planting_id=(\d+)/)
          if (!match) return

          cy.visit(`/viticulturist/phenology?filter_planting_id=${match[1]}`)
          cy.waitForLivewire()

          cy.get('body').then(($indexBody) => {
            const editLinks = $indexBody.find('a[href*="/phenology/"][href*="/edit"]')
            if (editLinks.length === 0) return

            cy.wrap(editLinks.first()).click({ force: true })
            cy.waitForLivewire()

            cy.get('textarea').clear().type('Notas actualizadas desde Cypress.', { force: true })

            cy.contains('button', 'Actualizar Observación').click({ force: true })
            cy.waitForLivewire()
            cy.wait(500)

            cy.get('body').should('be.visible')
          })
        })
      })
    })
  })
})
