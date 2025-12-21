import { defineConfig } from 'cypress'
import { execSync } from 'child_process'
import { resolve } from 'path'
import { readFileSync, existsSync } from 'fs'

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
    // Ignorar comentarios y líneas vacías
    const trimmedLine = line.trim()
    if (!trimmedLine || trimmedLine.startsWith('#')) {
      return
    }

    // Parsear KEY=VALUE
    const match = trimmedLine.match(/^([^#=]+)=(.*)$/)
    if (match) {
      const key = match[1].trim()
      let value = match[2].trim()

      // Remover comillas si existen
      if ((value.startsWith('"') && value.endsWith('"')) || 
          (value.startsWith("'") && value.endsWith("'"))) {
        value = value.slice(1, -1)
      }

      envVars[key] = value
    }
  })

  return envVars
}

export default defineConfig({
  e2e: {
    baseUrl: 'http://127.0.0.1:8000',
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
      // Limpiar BD antes de ejecutar todos los tests
      on('before:run', (details) => {
        console.log('\n🔄 Configurando base de datos de test (agro365_test)...')
        try {
          const projectRoot = resolve(__dirname)
          const envCypressPath = resolve(projectRoot, '.env.cypress')
          const envPath = resolve(projectRoot, '.env')
          const fs = require('fs')

          // IMPORTANTE: Configurar .env del servidor para que use BD de test
          if (fs.existsSync(envCypressPath)) {
            // Guardar .env actual si existe
            if (fs.existsSync(envPath)) {
              const backupPath = resolve(projectRoot, '.env.backup')
              fs.copyFileSync(envPath, backupPath)
              console.log('💾 .env guardado como .env.backup')
            }
            
            // Copiar .env.cypress a .env para que el servidor use BD de test
            fs.copyFileSync(envCypressPath, envPath)
            console.log('✅ Servidor configurado para usar BD de test')
          } else {
            throw new Error('No se encuentra .env.cypress')
          }

          // Cargar variables de entorno desde .env.cypress
          const env = loadEnvFile(envCypressPath)
          console.log(`✅ Usando BD de test: ${env.DB_DATABASE}`)

          // Ejecutar migrate:fresh en BD de test
          console.log('📦 Ejecutando migraciones en BD de test...')
          execSync('php artisan migrate:fresh --force', {
            stdio: 'inherit',
            cwd: projectRoot,
            shell: true,
            env: env
          })
          console.log('✅ Migraciones ejecutadas')

          // Ejecutar seeders base primero
          console.log('🌱 Ejecutando seeders base...')
          execSync('php artisan db:seed --force', {
            stdio: 'inherit',
            cwd: projectRoot,
            shell: true,
            env: env
          })
          
          // Crear usuarios de prueba genéricos para Cypress
          console.log('👤 Creando usuarios de prueba para Cypress...')
          execSync('php artisan db:seed --class=CypressTestUserSeeder --force', {
            stdio: 'inherit',
            cwd: projectRoot,
            shell: true,
            env: env
          })
          
          // Ejecutar seeder completo para tener todos los datos de prueba (opcional, solo si se necesita)
          // console.log('🌱 Ejecutando seeder completo...')
          // execSync('php artisan db:seed --class=CompleteTestUserSeeder --force', {
          //   stdio: 'inherit',
          //   cwd: projectRoot,
          //   shell: true,
          //   env: env
          // })
          
          console.log('✅ Datos de prueba creados')
          console.log('✅ Base de datos lista para los tests\n')
        } catch (error) {
          console.error('❌ Error configurando BD:', error.message)
          console.error('\n💡 Asegúrate de:')
          console.error('   1. Crear la BD: CREATE DATABASE agro365_test;')
          console.error('   2. Verificar que .env.cypress existe')
          throw error
        }
      })

      // Limpiar BD después de ejecutar todos los tests
      on('after:run', (results) => {
        console.log('\n🧹 Limpiando base de datos de test...')
        try {
          const projectRoot = resolve(__dirname)
          const envCypressPath = resolve(projectRoot, '.env.cypress')
          const envPath = resolve(projectRoot, '.env')
          const backupPath = resolve(projectRoot, '.env.backup')
          const fs = require('fs')

          // Cargar variables de entorno desde .env.cypress
          const env = loadEnvFile(envCypressPath)

          execSync('php artisan migrate:fresh --force', {
            stdio: 'inherit',
            cwd: projectRoot,
            shell: true,
            env: env
          })
          console.log('✅ Base de datos de test limpiada')

          // Restaurar .env original si existe backup
          if (fs.existsSync(backupPath)) {
            fs.copyFileSync(backupPath, envPath)
            fs.unlinkSync(backupPath)
            console.log('✅ .env restaurado a configuración original\n')
          } else {
            console.log('⚠️  No se encontró .env.backup para restaurar\n')
          }
        } catch (error) {
          console.error('❌ Error limpiando BD:', error.message)
          // No lanzar error aquí para no afectar el resultado de los tests
        }
      })
    },
  },
})

