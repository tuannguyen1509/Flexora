$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$compose = Join-Path $workspace 'local/docker-compose.yml'
$results = Join-Path $workspace 'local/results'
$fixture = & docker compose -f $compose run --rm cli wp eval-file /workspace/tests/header-wp.php
if ($LASTEXITCODE -ne 0) { throw 'Header WordPress integration failed' }
$json = $fixture -join "`n"; $data = $json | ConvertFrom-Json
[IO.File]::WriteAllText((Join-Path $results 'header-fixtures.json'), $json, (New-Object Text.UTF8Encoding($false)))
"PASS Header real WordPress: $($data.assertions) assertions"
& docker run --rm --network flx-development_default --mount "type=bind,source=$workspace,target=/workspace,readonly" --mount "type=bind,source=$results,target=/workspace/local/results" flx-development-browser:phase05 node /workspace/tests/browser/header.cjs
if ($LASTEXITCODE -ne 0) { throw 'Header browser failed' }
& docker run --rm --network flx-development_default --mount "type=bind,source=$workspace,target=/workspace,readonly" --mount "type=bind,source=$results,target=/workspace/local/results" flx-development-browser:phase05 node /workspace/tests/browser/header-regression.cjs
if ($LASTEXITCODE -ne 0) { throw 'Header reuse regression failed' }
try {
    & docker compose -f $compose run --rm cli wp theme mod set custom_logo $data.logo_id
    if ($LASTEXITCODE -ne 0) { throw 'Logo fixture selection failed' }
    & docker run --rm --network flx-development_default --mount "type=bind,source=$workspace,target=/workspace,readonly" --mount "type=bind,source=$results,target=/workspace/local/results" -e FLX_HEADER_LOGO=1 flx-development-browser:phase05 node /workspace/tests/browser/header.cjs
    if ($LASTEXITCODE -ne 0) { throw 'Custom logo browser failed' }
} finally {
    & docker compose -f $compose run --rm cli wp theme mod remove custom_logo
    if ($LASTEXITCODE -ne 0) { throw 'Logo fixture cleanup failed' }
}
