param([int]$Port = 8090)
$ErrorActionPreference = 'Stop'
$envFile = Join-Path $PSScriptRoot '.env'
if (-not (Test-Path -LiteralPath $envFile)) {
    if (Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue) { throw "Port $Port is in use. Choose another -Port." }
    $rng = [Security.Cryptography.RandomNumberGenerator]::Create()
    function New-LocalSecret {
        $bytes = New-Object byte[] 32
        $rng.GetBytes($bytes)
        return [BitConverter]::ToString($bytes).Replace('-','').ToLowerInvariant()
    }
    $content = @(
        "FLX_PORT=$Port", "FLX_URL=http://localhost:$Port",
        ('FLX_DB_PASSWORD=' + (New-LocalSecret)), ('FLX_DB_ROOT_PASSWORD=' + (New-LocalSecret)),
        'FLX_ADMIN_USER=flx_admin', ('FLX_ADMIN_PASSWORD=' + (New-LocalSecret)),
        'FLX_ADMIN_EMAIL=admin@example.test', 'FLX_WORDPRESS_IMAGE=wordpress:php8.3-apache',
        'FLX_CLI_IMAGE=wordpress:cli-php8.3', 'FLX_DB_IMAGE=mariadb:11.4'
    )
    [IO.File]::WriteAllLines($envFile, $content, (New-Object Text.UTF8Encoding($false)))
    $rng.Dispose()
}
$compose = Join-Path $PSScriptRoot 'docker-compose.yml'
docker compose --env-file $envFile -f $compose config --quiet
if ($LASTEXITCODE -ne 0) { throw 'Compose validation failed' }
docker compose --env-file $envFile -f $compose up -d --wait wordpress
if ($LASTEXITCODE -ne 0) { throw 'Local stack start failed' }
docker compose --env-file $envFile -f $compose run --rm cli sh /flx-install.sh
if ($LASTEXITCODE -ne 0) { throw 'WordPress installation failed' }
Write-Output 'Local WordPress installed; credentials remain only in ignored local/.env.'
