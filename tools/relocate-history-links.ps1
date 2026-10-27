param([string]$Workspace = (Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference = 'Stop'
$mapping = @(
    @('audit-phase-01','docs/audit-phase-01'),
    @('architecture-phase-02','docs/architecture-phase-02'),
    @('implementation-phase-03','docs/implementation-history/phase-03'),
    @('themes/themes/az-theme','references/az/az-theme'),
    @('themes/themes/az-theme-child','references/az/az-theme-child')
)
$utf8 = New-Object Text.UTF8Encoding($false)
$changed = 0
foreach ($row in $mapping[0..2]) {
    $newRoot = [IO.Path]::GetFullPath((Join-Path $Workspace $row[1]))
    $oldRoot = [IO.Path]::GetFullPath((Join-Path $Workspace $row[0]))
    foreach ($file in Get-ChildItem -LiteralPath $newRoot -Recurse -Filter *.md -File) {
        $oldFile = $oldRoot + $file.FullName.Substring($newRoot.Length)
        $oldDirectory = Split-Path -Parent $oldFile
        $newDirectory = $file.DirectoryName
        $text = [IO.File]::ReadAllText($file.FullName)
        $updated = [regex]::Replace($text,'\]\(([^)\r\n]+)\)',[Text.RegularExpressions.MatchEvaluator]{
            param($match)
            $href = $match.Groups[1].Value
            if ($href -match '^(https?://|#|[a-zA-Z]:)') { return $match.Value }
            $pieces = $href.Split('#',2)
            $path = $pieces[0].Trim('<','>')
            if (-not $path) { return $match.Value }
            $resolved = [IO.Path]::GetFullPath((Join-Path $oldDirectory ([uri]::UnescapeDataString($path))))
            $target = $resolved
            foreach ($relocation in $mapping) {
                $from = [IO.Path]::GetFullPath((Join-Path $Workspace $relocation[0]))
                if ($resolved -eq $from -or $resolved.StartsWith($from + '\')) {
                    $target = [IO.Path]::GetFullPath((Join-Path $Workspace $relocation[1])) + $resolved.Substring($from.Length)
                    break
                }
            }
            if (-not (Test-Path -LiteralPath $target)) { return $match.Value }
            $relative = ([uri]($newDirectory + '\')).MakeRelativeUri([uri]$target).ToString()
            if ((Get-Item -LiteralPath $target).PSIsContainer) { $relative = $relative.TrimEnd('/') + '/' }
            if ($pieces.Length -gt 1) { $relative += '#' + $pieces[1] }
            return '](' + $relative + ')'
        })
        if ($updated -ne $text) {
            [IO.File]::WriteAllText($file.FullName,$updated,$utf8)
            $changed++
        }
    }
}
"Updated relative links in $changed moved Markdown files."
