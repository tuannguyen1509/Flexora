param([switch]$ParentOnly)
$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$dist = Join-Path $workspace 'dist'
New-Item -ItemType Directory -Path $dist -Force | Out-Null
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.IO.Compression
$packages = @('flexora')
if (-not $ParentOnly) { $packages += 'flexora-child-starter' }
foreach ($package in $packages) {
    $source = (Resolve-Path (Join-Path $workspace $package)).Path
    $style = [IO.File]::ReadAllText((Join-Path $source 'style.css'))
    $version = [regex]::Match($style,'(?m)^Version:\s*([0-9]+\.[0-9]+\.[0-9]+)\s*$').Groups[1].Value
    if (-not $version) { throw "No valid preview version in $package" }
    $destination = [IO.Path]::GetFullPath((Join-Path $dist "$package-$version.zip"))
    if (-not $destination.StartsWith($dist + '\')) { throw 'ZIP target escaped dist' }
    $stream = [IO.File]::Open($destination,[IO.FileMode]::Create)
    $archive = New-Object IO.Compression.ZipArchive($stream,[IO.Compression.ZipArchiveMode]::Create)
    $count = 0
    try {
        foreach ($file in Get-ChildItem -LiteralPath $source -Recurse -File) {
            $relative = $file.FullName.Substring($source.Length + 1).Replace('\','/')
            if ($relative -match '(^|/)(docs|tests|references|local|node_modules|\.git)(/|$)|(^|/)\.') { throw "Disallowed package path: $relative" }
            if ($file.Extension -notin '.php','.css','.js','.json','.svg','.png','.jpg','.jpeg','.webp','.avif','.ico','.woff','.woff2','.ttf','.otf','.po','.mo','.pot') { continue }
            [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive,$file.FullName,"$package/$relative",[IO.Compression.CompressionLevel]::Optimal) | Out-Null
            $count++
        }
    } finally { $archive.Dispose(); $stream.Dispose() }
    $check = [IO.Compression.ZipFile]::OpenRead($destination)
    try {
        foreach ($entry in $check.Entries) {
            if (-not $entry.FullName.StartsWith($package + '/') -or $entry.FullName.Contains('../')) { throw 'Invalid ZIP structure' }
        }
        if ($check.Entries.FullName -notcontains "$package/style.css" -or $check.Entries.FullName -notcontains "$package/functions.php") { throw 'ZIP missing required theme entries' }
        if ($package -eq 'flexora' -and $check.Entries.FullName -notcontains 'flexora/index.php') { throw 'Parent ZIP missing fallback template' }
    } finally { $check.Dispose() }
    "PASS build $package-$version.zip ($count production files; SHA256 $((Get-FileHash -LiteralPath $destination -Algorithm SHA256).Hash))"
}
