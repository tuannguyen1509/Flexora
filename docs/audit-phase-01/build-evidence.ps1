$ErrorActionPreference = 'Stop'
$sourceRoot = (Resolve-Path (Join-Path $PSScriptRoot '../themes/themes')).Path
$textExtensions = @('.php','.js','.css','.cjs','.json','.md','.txt','.po','.svg','.gitignore')
$files = @(Get-ChildItem -LiteralPath $sourceRoot -Recurse -File | Sort-Object FullName)
$utf8 = New-Object System.Text.UTF8Encoding($false)
$documents = @{}
$data = New-Object System.Collections.Generic.List[string]
$hooks = New-Object System.Collections.Generic.List[string]
$assets = New-Object System.Collections.Generic.List[string]
$symbols = New-Object System.Collections.Generic.List[string]
$child = New-Object System.Collections.Generic.List[string]
$prefix = New-Object System.Collections.Generic.List[string]
$coverage = New-Object System.Collections.Generic.List[string]
$parentDefinitions = @{}
foreach ($file in $files) {
 if ($textExtensions -notcontains $file.Extension) { continue }
 $relative = $file.FullName.Substring($sourceRoot.Length + 1).Replace('\','/')
 $content = [IO.File]::ReadAllText($file.FullName)
 $documents[$relative] = $content
 if ($relative.StartsWith('az-theme/') -and $file.Extension -eq '.php' -and $relative -notmatch '/(tests|docs)/') {
  foreach ($match in [regex]::Matches($content,'\b(?:function|class)\s+((?:az_|az9s_|cp_|AZ_)[A-Za-z_0-9]+)')) {
   $parentDefinitions[$match.Groups[1].Value] = $relative
  }
 }
}
foreach ($relative in ($documents.Keys | Sort-Object)) {
 $content = $documents[$relative]
 $lines = $content -split '\r?\n'
 $coverage.Add("| $relative | $($lines.Count) | $([Text.Encoding]::UTF8.GetByteCount($content)) |")
 $production = $relative -notmatch '/(tests|docs)/' -and $relative -match '\.(php|js|css|cjs|json)$'
 for ($i=0; $i -lt $lines.Count; $i++) {
  $line = $lines[$i].Trim()
  $ref = $relative + ':' + ($i+1)
  if (!$production) { continue }
  if ($line -match '\b(?:get|add|update|delete)_(?:option|site_option|post_meta|term_meta|user_meta|theme_mod|transient)\s*\(|\b(?:set_transient|set_theme_mod|get_theme_mods|register_setting|wp_schedule_event|wp_clear_scheduled_hook)\s*\(|\$wpdb|CREATE TABLE|ALTER TABLE') { $data.Add($ref + ' ' + $line) }
  if ($line -match '\b(?:add_action|add_filter|remove_action|remove_filter|do_action|apply_filters|add_shortcode|register_rest_route)\s*\(') { $hooks.Add($ref + ' ' + $line) }
  if ($line -match '\bwp_(?:enqueue|register|dequeue|deregister|add_inline|localize)_(?:script|style)|https?://[^\s]+\.(?:js|css)|@import|@font-face') { $assets.Add($ref + ' ' + $line) }
  if ($line -match '\b(?:function|class)\s+[A-Za-z_]') { $symbols.Add($ref + ' ' + $line) }
  foreach ($m in [regex]::Matches($line,'[''"]((?:_?az_|az9s_|cp_|iaca_|theme_mods_)[A-Za-z0-9_.-]*)[''"]')) { $prefix.Add($m.Groups[1].Value + "`t" + $ref + "`t" + $line) }
 }
 if ($relative.StartsWith('az-theme-child/') -and $production) {
  foreach ($symbol in ($parentDefinitions.Keys | Sort-Object)) {
   $pattern = '\b' + [regex]::Escape($symbol) + '\s*(?:\(|::)'
   for ($i=0;$i -lt $lines.Count;$i++) {
    if ($lines[$i] -match $pattern) { $child.Add($symbol + "`t" + $relative + ':' + ($i+1) + "`t" + $parentDefinitions[$symbol] + "`t" + $lines[$i].Trim()) }
   }
  }
 }
}
foreach ($pair in @(@('DATA-CALLSITES.txt',$data),@('HOOKS-SHORTCODES-REST.txt',$hooks),@('ASSET-CALLSITES.txt',$assets),@('SYMBOLS.txt',$symbols),@('CHILD-PARENT-CALLS.tsv',$child),@('PREFIX-LITERALS.tsv',$prefix))) {
 [IO.File]::WriteAllLines((Join-Path $PSScriptRoot $pair[0]),[string[]]$pair[1],$utf8)
}
[IO.File]::WriteAllLines((Join-Path $PSScriptRoot 'TEXT-FILE-COVERAGE.md'),[string[]](@('# Text-file inventory','', 'Static scan covers complete text contents. This is a file inventory, not proof of manual review.','', '| File | Lines | UTF-8 bytes |','|---|---:|---:|') + $coverage),$utf8)
Write-Output "Files: $($files.Count); text files scanned: $($documents.Count); data lines: $($data.Count); hook lines: $($hooks.Count); child symbol callsites: $($child.Count)"

$cssReport = New-Object System.Collections.Generic.List[string]
$cssReport.Add('# CSS inventory')
$cssReport.Add('')
$cssReport.Add('Full-file structural scan; bytes are uncompressed source sizes, not network transfer sizes. Rule counts are approximate (regex, including nested blocks).')
$cssReport.Add('')
$cssReport.Add('| File | Bytes | Rules | !important | Breakpoints / motion |')
$cssReport.Add('|---|---:|---:|---:|---|')
$testsReport = New-Object System.Collections.Generic.List[string]
$docsReport = New-Object System.Collections.Generic.List[string]
foreach ($relative in ($documents.Keys | Sort-Object)) {
 $content = $documents[$relative]
 if ($relative.EndsWith('.css')) {
  $cssReport.Add('| ' + $relative + ' | ' + [Text.Encoding]::UTF8.GetByteCount($content) + ' | ' + [regex]::Matches($content,'[^{}]+\{[^{}]*\}').Count + ' | ' + [regex]::Matches($content,'!important').Count + ' | ' + (([regex]::Matches($content,'@media[^\{]+') | ForEach-Object { $_.Value.Replace('|','/').Trim() } | Sort-Object -Unique) -join '; ') + ' |')
 }
 if ($relative -match '/tests/|/docs/tools/') {
  $testsReport.Add('FILE ' + $relative)
  $testsReport.Add('NOTE: Source review only; no test execution in this audit.')
  $testsReport.AddRange([string[]]@(($content -split '\r?\n') | Where-Object { $_ -match 'require|include|assert|check\(|test\(|test:|playwright|puppeteer|fetch\(|localhost|process\.|writeFile|unlink|mkdir|file_put_contents' }))
 }
 if ($relative.EndsWith('.md')) {
  $docsReport.Add('FILE ' + $relative)
  $docsReport.AddRange([string[]]@(($content -split '\r?\n') | Where-Object { $_ -match '^#|not run|Not run|histor|snapshot|pending|Pending|Status|status|removed|Removed' }))
 }
}
[IO.File]::WriteAllLines((Join-Path $PSScriptRoot 'CSS-INVENTORY.md'),$cssReport,$utf8)
[IO.File]::WriteAllLines((Join-Path $PSScriptRoot 'TEST-SOURCE-INDEX.txt'),$testsReport,$utf8)
[IO.File]::WriteAllLines((Join-Path $PSScriptRoot 'DOC-SOURCE-INDEX.txt'),$docsReport,$utf8)
