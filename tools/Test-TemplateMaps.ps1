param([string]$Php = '')
$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
if ($Php) { & $Php -n (Join-Path $workspace 'tests/template-maps.php'); if ($LASTEXITCODE -ne 0) { throw 'Template registry contract failed' } }
& docker compose -f (Join-Path $workspace 'local/docker-compose.yml') run --rm cli wp eval-file /workspace/tests/template-maps-wp.php
if ($LASTEXITCODE -ne 0) { throw 'Real WP Template Maps failed' }
$results = Join-Path $workspace 'local/results'
if (-not (Test-Path (Join-Path $results 'fields-fixtures.json'))) { throw 'Run Test-Fields.ps1 first for image and credential fixtures' }
& docker run --rm --network flx-development_default --mount "type=bind,source=$workspace,target=/workspace,readonly" --mount "type=bind,source=$results,target=/workspace/local/results" flx-development-browser:phase05 node /workspace/tests/browser/template-maps.cjs
if ($LASTEXITCODE -ne 0) { throw 'Template Maps browser failed' }
