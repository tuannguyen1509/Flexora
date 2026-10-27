param([string]$Workspace = (Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference = 'Stop'
$baselineRoot = Join-Path $Workspace 'references/az'
$manifest = Get-Content (Join-Path $Workspace 'docs/audit-phase-01/source-manifest.json') -Raw | ConvertFrom-Json
$expected = @{}
$mismatches = @()
foreach ($entry in $manifest) {
    $expected[$entry.Path] = $true
    $path = Join-Path $baselineRoot $entry.Path
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) { $mismatches += "Missing: $($entry.Path)"; continue }
    $file = Get-Item -LiteralPath $path
    if ($file.Length -ne $entry.Bytes -or (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash -ne $entry.SHA256) { $mismatches += "Changed: $($entry.Path)" }
}
$themePaths = @((Join-Path $baselineRoot 'az-theme'), (Join-Path $baselineRoot 'az-theme-child'))
$actual = @(Get-ChildItem -LiteralPath $themePaths -File -Recurse -Force)
foreach ($file in $actual) {
    $relative = $file.FullName.Substring($baselineRoot.Length + 1).Replace('\','/')
    if (-not $expected.ContainsKey($relative)) { $mismatches += "Added: $relative" }
}
if ($mismatches.Count -gt 0) { $mismatches; throw 'Baseline integrity FAILED' }
"PASS source integrity: $($manifest.Count) expected / $($actual.Count) actual; 0 changed, 0 missing, 0 added."
