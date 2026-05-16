cd 'C:\Users\jorge\Desktop\agro365'
$specs = 'cypress/e2e/viticulturist/phytosanitary-treatments.cy.js,cypress/e2e/viticulturist/fertilizations.cy.js,cypress/e2e/viticulturist/irrigations.cy.js,cypress/e2e/viticulturist/cultural-works.cy.js,cypress/e2e/viticulturist/observations.cy.js,cypress/e2e/viticulturist/pruning.cy.js,cypress/e2e/viticulturist/post-harvest.cy.js,cypress/e2e/viticulturist/estimated-yields.cy.js,cypress/e2e/viticulturist/harvest.cy.js'
$p = Start-Process -FilePath 'node_modules\.bin\cypress.cmd' -ArgumentList "run --config-file cypress.config.test.cjs --spec $specs" -Wait -PassThru -RedirectStandardOutput 'cypress_ps_run.txt' -RedirectStandardError 'cypress_ps_err.txt' -NoNewWindow
Write-Host "ExitCode: $($p.ExitCode)"
