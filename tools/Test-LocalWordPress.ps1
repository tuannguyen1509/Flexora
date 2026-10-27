param([string]$Url = 'http://localhost:8090')
$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$compose = Join-Path $workspace 'local/docker-compose.yml'
$script:checks = 0
function Confirm($condition, [string]$message) {
    if (-not $condition) { throw "Integration FAILED: $message" }
    $script:checks++
}
$discovered = & docker compose -f $compose run --rm cli wp theme list --format=json
if ($LASTEXITCODE -ne 0) { throw 'Theme discovery failed' }
$themes = ($discovered -join "`n") | ConvertFrom-Json
Confirm (@($themes | Where-Object { $_.name -eq 'flexora' }).Count -eq 1) 'Parent discovery'
Confirm (@($themes | Where-Object { $_.name -eq 'flexora-child-starter' }).Count -eq 1) 'Child discovery'
foreach ($theme in @('flexora','flexora-child-starter')) {
    & docker compose -f $compose run --rm cli wp theme activate $theme
    if ($LASTEXITCODE -ne 0) { throw "Activation failed: $theme" }
    $response = Invoke-WebRequest ($Url + '/?flx_probe=1') -UseBasicParsing
    Confirm ($response.StatusCode -eq 200) "$theme HTTP200"
    $html = $response.Content
    Confirm ($html -notmatch 'Fatal error|Uncaught Error|Warning:|Notice:') "$theme no rendered PHP failures"
    Confirm (([regex]::Matches($html,'class="flx-skip-link"')).Count -eq 1) "$theme skip link once"
    Confirm ($html.Contains('<main id="flx-main-content"')) "$theme real main target"
    $match = [regex]::Match($html,'FLX_LOCAL_PROBE ([A-Za-z0-9+/=]+)')
    Confirm ($match.Success) "$theme local probe"
    $probe = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($match.Groups[1].Value)) | ConvertFrom-Json
    Confirm ($probe.stylesheet -eq $theme -and $probe.template -eq 'flexora') "$theme inheritance identity"
    Confirm (-not $probe.theme_errors) "$theme not broken"
    foreach ($hook in @('after_setup_theme','wp','wp_enqueue_scripts','wp_head','wp_body_open','wp_footer')) {
        Confirm ($probe.hooks.$hook -eq 1) "$theme actual $hook"
    }
    Confirm ($probe.hook_class -eq 'WP_Hook') "$theme WP_Hook"
    Confirm ($probe.styles_class -eq 'WP_Styles') "$theme WP_Styles"
    Confirm ($probe.scripts_class -eq 'WP_Scripts') "$theme WP_Scripts"
    Confirm ($probe.styles_done -contains 'flx-accessibility-skip-link') "$theme CSS printed"
    Confirm ($probe.styles_done -contains 'flx-shell') "$theme shell CSS printed"
    Confirm ($probe.scripts_done -contains 'flx-accessibility-skip-link-js') "$theme JS printed"
    Confirm ($probe.body_callback_priority -eq 1) "$theme body callback priority"
    Confirm ($probe.canonical_api) "$theme canonical runtime loaded"
    Confirm ($probe.api_version -eq '0.1.0') "$theme preview API"
    Confirm ($probe.module.available -and $probe.module.booted -contains 'frontend') "$theme module boot"
    Confirm (@($probe.diagnostics).Count -eq 0) "$theme no FLX diagnostic errors"
    Confirm ($probe.nested_preflight -eq $true) "$theme enqueue inside wp_head preflight"
    foreach ($asset in @($probe.style_source,$probe.script_source)) {
        Confirm ($asset.StartsWith($Url + '/wp-content/themes/flexora/assets/')) "$theme Parent owns asset URL"
        Confirm ((Invoke-WebRequest $asset -UseBasicParsing).StatusCode -eq 200) "$theme asset HTTP200"
    }
    Write-Output "PASS real WordPress theme $theme"
}
# Reversible proof that editing canonical source immediately changes served bytes.
$cssPath = Join-Path $workspace 'flexora/assets/css/shell.css'
$original = [IO.File]::ReadAllBytes($cssPath)
$marker = 'flx-bind-' + [Guid]::NewGuid().ToString('N')
try {
    [IO.File]::AppendAllText($cssPath, "`n/* $marker */`n")
    $served = Invoke-WebRequest ($Url + '/wp-content/themes/flexora/assets/css/shell.css?bind=' + $marker) -UseBasicParsing
    Confirm ($served.Content.Contains($marker)) 'host save immediately visible through bind mount'
} finally { [IO.File]::WriteAllBytes($cssPath,$original) }
Write-Output "PASS real WordPress integration: $script:checks assertions; Parent then Child; final active Child."
