param([Parameter(Mandatory=$true)][string]$Php, [string]$Node = '')
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$linted = 0
foreach ($file in Get-ChildItem -LiteralPath (Join-Path $root 'flexora'),(Join-Path $root 'flexora-child-starter'),(Join-Path $root 'tests') -Recurse -Filter *.php -File) {
    $output = & $Php -n -l $file.FullName
    if ($LASTEXITCODE -ne 0) { $output; throw "Lint failed: $($file.FullName)" }
    $linted++
}
"PASS PHP lint: $linted files"
foreach ($profile in @('native','reentry')) {
    & $Php -n (Join-Path $root 'tests/foundation.php') $profile
    if ($LASTEXITCODE -ne 0) { throw "Foundation failed: $profile" }
    & $Php -n (Join-Path $root 'tests/component-preflight.php') $profile
    if ($LASTEXITCODE -ne 0) { throw "Component preflight failed: $profile" }
}
foreach ($test in @('registration','assets','compatibility','head-enqueue','template-maps')) {
    & $Php -n (Join-Path $root "tests/$test.php")
    if ($LASTEXITCODE -ne 0) { throw "Test failed: $test" }
}
foreach ($scope in @('admin','ajax','rest','cron','cli')) {
    & $Php -n (Join-Path $root 'tests/scope.php') $scope
    if ($LASTEXITCODE -ne 0) { throw "Scope failed: $scope" }
}
foreach ($mode in @('enabled','disabled','admin','component')) {
    & $Php -n (Join-Path $root 'tests/accessibility.php') $mode
    if ($LASTEXITCODE -ne 0) { throw "Accessibility failed: $mode" }
}
& $Php -n (Join-Path $root 'tests/naming-css.php')
if ($LASTEXITCODE -ne 0) { throw 'Naming/CSS source contracts failed' }
& (Join-Path $PSScriptRoot 'Verify-SourceIntegrity.ps1')
if ($Node) {
    & $Node (Join-Path $root 'tests/skip-link.test.js')
    if ($LASTEXITCODE -ne 0) { throw 'Skip link JavaScript failed' }
} else {
    'SKIP host Node runner: supply -Node, or use tools/Test-Browser.ps1 for the Node fixture in Docker.'
}
