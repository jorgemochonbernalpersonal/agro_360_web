/**
 * Comandos de Cypress para crear datos de prueba dinámicamente
 * Estos comandos se ejecutan contra la BD de test
 */

/**
 * Crear un usuario de prueba mediante API/Artisan
 * Nota: Esto requiere que Laravel tenga una ruta API o se ejecute via artisan
 */
Cypress.Commands.add('createTestUser', (userData = {}) => {
  const defaultUser = {
    name: 'Test User',
    email: `test-${Date.now()}@test.com`,
    password: 'password',
    role: 'viticulturist',
  };

  const user = { ...defaultUser, ...userData };

  // Ejecutar comando artisan para crear usuario
  cy.exec(`php artisan tinker --execute="
    \\$user = \\App\\Models\\User::create([
      'name' => '${user.name}',
      'email' => '${user.email}',
      'password' => bcrypt('${user.password}'),
      'role' => '${user.role}',
      'email_verified_at' => now(),
      'can_login' => true,
      'password_must_reset' => false,
    ]);
    echo \\$user->id;
  "`).then((result) => {
    cy.log(`Usuario creado: ${user.email}`);
    return cy.wrap({ ...user, id: result.stdout.trim() });
  });
});

/**
 * Limpiar datos de prueba creados durante los tests
 */
Cypress.Commands.add('cleanupTestData', () => {
  // Eliminar usuarios de prueba (excepto los genéricos)
  cy.exec(`php artisan tinker --execute="
    \\App\\Models\\User::where('email', 'like', 'test-%@test.com')
      ->orWhere('email', 'like', 'cypress-%@test.com')
      ->delete();
  "`);
});

