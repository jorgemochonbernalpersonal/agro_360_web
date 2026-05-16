const cypress = require('cypress');
const fs = require('fs');

const specs = [
  'cypress/e2e/viticulturist/phytosanitary-treatments.cy.js',
  'cypress/e2e/viticulturist/irrigations.cy.js',
  'cypress/e2e/viticulturist/cultural-works.cy.js',
  'cypress/e2e/viticulturist/fertilizations.cy.js',
  'cypress/e2e/viticulturist/observations.cy.js',
  'cypress/e2e/viticulturist/pruning.cy.js',
  'cypress/e2e/viticulturist/post-harvest.cy.js',
  'cypress/e2e/viticulturist/estimated-yields.cy.js',
  'cypress/e2e/viticulturist/harvest.cy.js',
];

console.log('Starting Cypress run for Cuaderno de Campo...');

cypress.run({
  configFile: 'cypress.config.test.cjs',
  spec: specs.join(','),
  browser: 'electron',
  headed: false,
  quiet: false,
}).then((result) => {
  const summary = {
    totalSuites: result.totalSuites,
    totalTests: result.totalTests,
    totalPassed: result.totalPassed,
    totalFailed: result.totalFailed,
    totalPending: result.totalPending,
    status: result.status,
    runs: result.runs && result.runs.map(r => ({
      spec: r.spec && r.spec.name,
      stats: r.stats,
      failures: r.tests && r.tests.filter(t => t.state === 'failed').map(t => ({
        title: t.title,
        error: t.displayError && t.displayError.substring(0, 400),
      })),
    })),
  };

  fs.writeFileSync('/tmp/cypress_results.json', JSON.stringify(summary, null, 2));
  console.log('\n=== RESULTS ===');
  console.log('Total:', result.totalTests, '| Passed:', result.totalPassed, '| Failed:', result.totalFailed, '| Pending:', result.totalPending);
  console.log('Status:', result.status);

  process.exit(result.totalFailed > 0 ? 1 : 0);
}).catch((err) => {
  console.error('Cypress run error:', err.message);
  fs.writeFileSync('/tmp/cypress_results.json', JSON.stringify({ error: err.message }, null, 2));
  process.exit(1);
});
