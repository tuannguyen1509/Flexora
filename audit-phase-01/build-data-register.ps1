$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '../themes/themes')).Path
$records = New-Object System.Collections.Generic.List[object]
foreach ($file in (Get-ChildItem -LiteralPath $root -Recurse -Filter *.php -File)) {
 $relative = $file.FullName.Substring($root.Length+1).Replace('\','/')
 if ($relative -match '/(docs|tests)/') { continue }
 $s = [IO.File]::ReadAllText($file.FullName)
 foreach ($m in [regex]::Matches($s,'\b(get_option|update_option|add_option|delete_option|get_post_meta|update_post_meta|add_post_meta|delete_post_meta|get_term_meta|update_term_meta|delete_term_meta|get_user_meta|update_user_meta|delete_user_meta|get_theme_mod|set_theme_mod|get_transient|set_transient|delete_transient|register_setting)\s*\(')) {
  $args = New-Object System.Collections.Generic.List[string]
  $start = $m.Index + $m.Length
  $begin = $start; $depth = 0; $quote = [char]0; $escaped = $false
  for ($j=$start; $j -lt $s.Length; $j++) {
   $ch=$s[$j]
   if ($quote -ne [char]0) {
    if ($escaped) { $escaped=$false; continue }
    if ($ch -eq '\') { $escaped=$true; continue }
    if ($ch -eq $quote) { $quote=[char]0 }
    continue
   }
   if ($ch -eq "'" -or $ch -eq '"') { $quote=$ch; continue }
   if ($ch -eq '(' -or $ch -eq '[' -or $ch -eq '{') { $depth++; continue }
   if ($ch -eq ')' -and $depth -eq 0) { $args.Add($s.Substring($begin,$j-$begin).Trim()); break }
   if ($ch -eq ')' -or $ch -eq ']' -or $ch -eq '}') { $depth--; continue }
   if ($ch -eq ',' -and $depth -eq 0) { $args.Add($s.Substring($begin,$j-$begin).Trim()); $begin=$j+1 }
  }
  $api=$m.Groups[1].Value
  $index=0
  if ($api -match '_meta$' -or $api -eq 'register_setting') { $index=1 }
  if ($args.Count -le $index) { continue }
  $expr=$args[$index]
  $literal=[regex]::Match($expr,'^([''"])([^''"]+)\1$')
  $line=1+[regex]::Matches($s.Substring(0,$m.Index),"`n").Count
  $records.Add([pscustomobject]@{Key=if($literal.Success){$literal.Groups[2].Value}else{$expr}; Literal=$literal.Success; API=$api; Source=$relative+':'+$line; Child=$relative.StartsWith('az-theme-child/')})
 }
}
$out = New-Object System.Collections.Generic.List[string]
$out.Add('# Data key register - direct callsites')
$out.Add('')
$out.Add('Static argument extraction, not a PHP AST or runtime data dump. Includes dormant callsites. Dynamic keys and indirect arrays are documented separately in DATA-COMPATIBILITY.md and DATA-CALLSITES.txt. Read/write locations are source evidence, not proof that values exist in a database. A missing writer means none found among direct literal calls; register_setting, loop fields, migrations or external plugins may write it.')
$out.Add('')
$out.Add('| Storage / literal key | Read callsites | Write/delete/registration callsites | Direct Child dependency |')
$out.Add('|---|---|---|---|')
foreach ($group in ($records | Where-Object Literal | Group-Object Key | Sort-Object Name)) {
 $read=@($group.Group | Where-Object {$_.API.StartsWith('get_')} | ForEach-Object {$_.Source} | Sort-Object -Unique)
 $write=@($group.Group | Where-Object {!$_.API.StartsWith('get_')} | ForEach-Object {$_.API+' '+$_.Source} | Sort-Object -Unique)
 $out.Add('| `' + $group.Name.Replace('|','\|') + '` | ' + ($read -join '<br>') + ' | ' + ($write -join '<br>') + ' | ' + (@($group.Group|Where-Object Child).Count -gt 0) + ' |')
}
$out.Add('')
$out.Add('## Dynamic arguments / constants / bulk-meta calls')
$out.Add('')
foreach ($r in ($records | Where-Object {!$_.Literal} | Sort-Object Source)) { $out.Add('- ' + $r.Source + ' `' + $r.API + '(' + ($r.Key -replace '\s+',' ').Replace('`','') + ')`') }
$utf8=New-Object System.Text.UTF8Encoding($false)
[IO.File]::WriteAllLines((Join-Path $PSScriptRoot 'DATA-KEY-REGISTER.md'),$out,$utf8)
$records | ConvertTo-Json -Depth 5 | Set-Content -Encoding UTF8 (Join-Path $PSScriptRoot 'data-keys.json')
Write-Output ('Data call records: '+$records.Count+'; direct literal keys: '+@($records|Where-Object Literal|Group-Object Key).Count)
