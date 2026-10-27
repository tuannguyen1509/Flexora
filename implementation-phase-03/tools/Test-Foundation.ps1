param([Parameter(Mandatory=$true)][string]$Php)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$linted = 0
foreach ($file in Get-ChildItem -LiteralPath $root -Recurse -Filter *.php -File) {
    $output = & $Php -n -l $file.FullName
    if ($LASTEXITCODE -ne 0) { $output; throw "Lint failed: $($file.FullName)" }
    $linted++
}
"PASS PHP lint: $linted files"
foreach ($profile in @('flexora-native','az-bridge')) {
    & $Php -n (Join-Path $root 'tests/foundation.php') $profile
    if ($LASTEXITCODE -ne 0) { throw "Foundation failed: $profile" }
    & $Php -n (Join-Path $root 'tests/component-preflight.php') $profile
    if ($LASTEXITCODE -ne 0) { throw "Component preflight failed: $profile" }
}
foreach ($test in @('registration','assets','compatibility')) {
    & $Php -n (Join-Path $root "tests/$test.php")
    if ($LASTEXITCODE -ne 0) { throw "Test failed: $test" }
}
foreach ($scope in @('admin','ajax','rest','cron','cli')) {
    & $Php -n (Join-Path $root 'tests/scope.php') $scope
    if ($LASTEXITCODE -ne 0) { throw "Scope failed: $scope" }
}
& (Join-Path $PSScriptRoot 'Verify-SourceIntegrity.ps1')
