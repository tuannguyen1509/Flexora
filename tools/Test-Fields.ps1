param([string]$Php = '')
$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$compose = Join-Path $workspace 'local/docker-compose.yml'
$results = Join-Path $workspace 'local/results'
New-Item -ItemType Directory -Path $results -Force | Out-Null
if ($Php) {
    & $Php -n (Join-Path $workspace 'tests/fields.php')
    if ($LASTEXITCODE -ne 0) { throw 'Portable field contract failed' }
}
# This fixture is explicitly gated to localhost:8090 + active Child Starter.
# It temporarily tests the settings option and restores its previous value in finally.
$fixture = & docker compose -f $compose run --rm cli wp eval-file /workspace/tests/fields-wp.php
if ($LASTEXITCODE -ne 0) { throw 'Real WordPress field contract failed' }
$json = $fixture -join "`n"
$summary = $json | ConvertFrom-Json
[IO.File]::WriteAllText((Join-Path $results 'fields-fixtures.json'), $json, (New-Object Text.UTF8Encoding($false)))
"PASS real WordPress field/storage: $($summary.assertions) assertions"
& docker run --rm --network flx-development_default --mount "type=bind,source=$workspace,target=/workspace,readonly" --mount "type=bind,source=$results,target=/workspace/local/results" flx-development-browser:phase05 node /workspace/tests/browser/advanced.cjs
if ($LASTEXITCODE -ne 0) { throw 'Advanced browser acceptance failed' }
