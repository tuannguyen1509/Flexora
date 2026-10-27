$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$compose = Join-Path $workspace 'local/docker-compose.yml'
$urlLine = Get-Content (Join-Path $workspace 'local/.env') | Where-Object { $_.StartsWith('FLX_URL=') }
$url = $urlLine.Substring('FLX_URL='.Length)
docker build -f (Join-Path $workspace 'local/browser.Dockerfile') -t flx-development-browser:phase05 (Join-Path $workspace 'local')
if ($LASTEXITCODE -ne 0) { throw 'Browser tool image failed to build' }
foreach ($theme in @('flexora','flexora-child-starter')) {
    docker compose -f $compose run --rm cli wp theme activate $theme
    if ($LASTEXITCODE -ne 0) { throw "Activation failed: $theme" }
    docker run --rm --network flx-development_default --mount "type=bind,source=$workspace,target=/workspace,readonly" -e "FLX_TEST_THEME=$theme" -e "FLX_URL=$url" flx-development-browser:phase05 node /workspace/tests/browser/local-wordpress.cjs
    if ($LASTEXITCODE -ne 0) { throw "Browser failed: $theme" }
}
docker run --rm --mount "type=bind,source=$workspace,target=/workspace,readonly" flx-development-browser:phase05 node /workspace/tests/skip-link.test.js
if ($LASTEXITCODE -ne 0) { throw 'JS fixture failed' }
