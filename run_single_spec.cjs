const cypress = require('cypress');
const fs = require('fs');

const specArg = process.argv[2] || 'cypress/e2e/viticulturist/phytosanitary-treatments.cy.js';

cypress.run({
  configFile: 'cypress.config.test.cjs',
  spec: specArg,
  browser: 'electron',
  headed: false,
  quiet: false,
}).then((result) => {
  // Print detailed failures
  if (result.runs) {
    result.runs.forEach(run => {
      if (run.tests) {
        run.tests.filter(t => t.state === 'failed').forEach(t => {
          console.log('\n=== FAILED:', t.title.join(' > '));
          console.log(t.displayError ? t.displayError.substring(0, 600) : 'no error');
        });
      }
    });
  }
  console.log('\nTotal:', result.totalTests, '| Passed:', result.totalPassed, '| Failed:', result.totalFailed);
  fs.writeFileSync('/tmp/single_spec_result.json', JSON.stringify({
    totalTests: result.totalTests,
    totalPassed: result.totalPassed,
    totalFailed: result.totalFailed,
    runs: result.runs && result.runs.map(r => ({
      spec: r.spec && r.spec.name,
      tests: r.tests && r.tests.map(t => ({ title: t.title, state: t.state, error: t.displayError && t.displayError.substring(0, 500) })),
    })),
  }, null, 2));
  process.exit(result.totalFailed > 0 ? 1 : 0);
}).catch(err => {
  console.error('Error:', err.message);
  process.exit(1);
});
