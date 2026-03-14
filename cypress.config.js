import { defineConfig } from 'cypress'
import { execSync, spawn } from 'child_process'
import { resolve } from 'path'
import { readFileSync, existsSync, writeFileSync } from 'fs'

const TEST_PORT = 8001
let testServerPid = null

/**
 * Lee variables de entorno desde un archivo .env
 */
function loadEnvFile(filePath) {
  if (!existsSync(filePath)) {
    throw new Error(`❌ No se encuentra el archivo: ${filePath}`)
  }

  const envContent = readFileSync(filePath, 'utf8')
  const envVars = { ...process.env }

  envContent.split('\n').forEach((line) => {
    const trimmedLine = line.trim()
    if (!trimmedLine || trimmedLine.startsWith('#')) return

    const match = trimmedLine.match(/^([^#=]+)=(.*)$/)
    if (match) {
      const key = match[1].trim()
      let value = match[2].trim()
      if ((value.startsWith('"') && value.endsWith('"')) ||
          (value.startsWith("'") && value.endsWith("'"))) {
        value = value.slice(1, -1)
      }
      envVars[key] = value
    }
  })

  return envVars
}

/**
 * Espera a que el servidor esté disponible en el puerto dado
 */
function waitForServer(port, retries = 30) {
  for (let i = 0; i < retries; i++) {
    try {
      execSync(
        `node -e "require('http').get('http://127.0.0.1:${port}', r => process.exit(0)).on('error', () => process.exit(1))"`,
        { timeout: 2000, stdio: 'ignore' }
      )
      return true
    } catch {
      // Esperar 1 segundo antes de reintentar (ping localhost como sleep)
      try { execSync('ping 127.0.0.1 -n 2 > nul', { stdio: 'ignore', shell: true }) } catch {}
    }
  }
  return false
}

/**
 * Detiene el servidor de test por PID
 */
function stopTestServer() {
  if (testServerPid) {
    try {
      // Windows: taskkill mata el proceso y sus hijos
      execSync(`taskkill /PID ${testServerPid} /F /T`, { stdio: 'ignore' })
      console.log(`✅ Servidor de test (PID ${testServerPid}) detenido`)
    } catch {
      // El proceso ya terminó o no existe
    }
    testServerPid = null
  }
}

export default defineConfig({
  e2e: {
    baseUrl: `http://127.0.0.1:${TEST_PORT}`,
    supportFile: 'cypress/support/e2e.js',
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    viewportWidth: 1280,
    viewportHeight: 720,
    video: true,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000,
    setupNodeEvents(on, config) {

      on('before:run', () => {
        const projectRoot = resolve(__dirname)
        const envCypressPath = resolve(projectRoot, '.env.cypress')

        if (!existsSync(envCypressPath)) {
          throw new Error('❌ No se encuentra .env.cypress — créalo copiando .env y cambiando DB_DATABASE=agro365_test')
        }

        const testEnv = loadEnvFile(envCypressPath)
        console.log(`\n🔄 Preparando entorno de test → BD: ${testEnv.DB_DATABASE}`)

        // ── 1. Migrar y seedear la BD de test ────────────────────────────
        console.log('📦 Ejecutando migrate:fresh en BD de test...')
        execSync('php artisan migrate:fresh --force', {
          stdio: 'inherit', cwd: projectRoot, shell: true, env: testEnv,
        })

        console.log('🌱 Ejecutando seeders base...')
        execSync('php artisan db:seed --force', {
          stdio: 'inherit', cwd: projectRoot, shell: true, env: testEnv,
        })

        console.log('👤 Creando usuarios Cypress...')
        execSync('php artisan db:seed --class=CypressTestUserSeeder --force', {
          stdio: 'inherit', cwd: projectRoot, shell: true, env: testEnv,
        })

        console.log('✅ BD de test lista\n')

        // ── 2. Levantar servidor Laravel apuntando a BD de test ───────────
        // Parar cualquier servidor previo en el mismo puerto
        try {
          execSync(`for /f "tokens=5" %a in ('netstat -aon ^| find ":${TEST_PORT}" ^| find "LISTENING"') do taskkill /PID %a /F`, {
            shell: 'cmd.exe', stdio: 'ignore',
          })
        } catch { /* no había servidor */ }

        console.log(`🚀 Iniciando servidor de test en http://127.0.0.1:${TEST_PORT} ...`)
        const child = spawn('php', ['artisan', 'serve', `--port=${TEST_PORT}`], {
          cwd: projectRoot,
          env: testEnv,
          stdio: 'ignore',
          detached: true,
          windowsHide: true,
          shell: false,
        })
        child.unref()
        testServerPid = child.pid
        console.log(`   PID del servidor: ${testServerPid}`)

        // Esperar a que responda
        console.log('⏳ Esperando a que el servidor arranque...')
        const ready = waitForServer(TEST_PORT)
        if (!ready) {
          stopTestServer()
          throw new Error(`❌ El servidor no arrancó en http://127.0.0.1:${TEST_PORT} tras 30 intentos`)
        }
        console.log(`✅ Servidor listo en http://127.0.0.1:${TEST_PORT}\n`)
      })

      on('after:run', () => {
        // ── 3. Limpiar BD de test ─────────────────────────────────────────
        console.log('\n🧹 Limpiando BD de test...')
        try {
          const projectRoot = resolve(__dirname)
          const testEnv = loadEnvFile(resolve(projectRoot, '.env.cypress'))
          execSync('php artisan migrate:fresh --force', {
            stdio: 'inherit', cwd: projectRoot, shell: true, env: testEnv,
          })
          console.log('✅ BD de test limpiada')
        } catch (e) {
          console.error('⚠ No se pudo limpiar la BD de test:', e.message)
        }

        // ── 4. Parar servidor de test ─────────────────────────────────────
        stopTestServer()
      })
    },
  },
})
