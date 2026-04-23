# ============================================
# Create Deployment Package
# Dashboard de Métricas IT
# ============================================

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  CREAR PAQUETE DE DESPLIEGUE" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = Split-Path -Parent $PSScriptRoot
$deployDir = "$PSScriptRoot"
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$packageName = "metricas-it-deploy-$timestamp.zip"
$packagePath = Join-Path $deployDir $packageName

# Archivos y carpetas a EXCLUIR del paquete
$excludePatterns = @(
    "*.git*",
    "node_modules",
    ".vscode",
    ".idea",
    "*.log",
    "deploy\*.zip",
    "deploy\*.ps1",
    "storage\cache\*",
    "logs\*",
    "uploads\*",
    ".env",
    "config.local.php"
)

Write-Host "Preparando archivos para comprimir..." -ForegroundColor Yellow
Write-Host ""

# Crear carpeta temporal
$tempDir = Join-Path $env:TEMP "metricas-it-deploy-temp"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

Write-Host "Copiando archivos..." -ForegroundColor Yellow

# Copiar todo excepto exclusiones
Get-ChildItem -Path $baseDir -Recurse | ForEach-Object {
    $relativePath = $_.FullName.Substring($baseDir.Length + 1)

    # Verificar si debe excluirse
    $shouldExclude = $false
    foreach ($pattern in $excludePatterns) {
        if ($relativePath -like $pattern) {
            $shouldExclude = $true
            break
        }
    }

    if (-not $shouldExclude) {
        $destPath = Join-Path $tempDir $relativePath

        if ($_.PSIsContainer) {
            if (-not (Test-Path $destPath)) {
                New-Item -ItemType Directory -Path $destPath -Force | Out-Null
            }
        } else {
            $destDir = Split-Path $destPath -Parent
            if (-not (Test-Path $destDir)) {
                New-Item -ItemType Directory -Path $destDir -Force | Out-Null
            }
            Copy-Item $_.FullName -Destination $destPath -Force
        }
    }
}

# Crear directorios vacíos necesarios
@("logs", "storage\cache", "uploads", "uploads\avatars") | ForEach-Object {
    $dir = Join-Path $tempDir $_
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        # Crear .gitkeep
        New-Item -ItemType File -Path (Join-Path $dir ".gitkeep") -Force | Out-Null
    }
}

Write-Host "  ✓ Archivos copiados" -ForegroundColor Green
Write-Host ""

# Comprimir
Write-Host "Comprimiendo paquete..." -ForegroundColor Yellow
Compress-Archive -Path "$tempDir\*" -DestinationPath $packagePath -Force

# Limpiar temporal
Remove-Item $tempDir -Recurse -Force

Write-Host "  ✓ Paquete creado" -ForegroundColor Green
Write-Host ""

# Información del paquete
$zipSize = (Get-Item $packagePath).Length / 1MB
$zipSize = [math]::Round($zipSize, 2)

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  PAQUETE CREADO EXITOSAMENTE" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Ubicación: " -NoNewline
Write-Host $packagePath -ForegroundColor White
Write-Host "Tamaño:    " -NoNewline
Write-Host "$zipSize MB" -ForegroundColor White
Write-Host ""

Write-Host "SIGUIENTE PASO:" -ForegroundColor Cyan
Write-Host "1. Transferir el archivo ZIP al servidor Windows" -ForegroundColor White
Write-Host "2. Descomprimir en C:\inetpub\wwwroot\metricas-it" -ForegroundColor White
Write-Host "3. Ejecutar: " -NoNewline
Write-Host ".\deploy-iis.bat" -ForegroundColor Yellow
Write-Host ""

# Copiar también script de despliegue al paquete
Write-Host "Creando script de despliegue en el servidor..." -ForegroundColor Yellow

$deployBatPath = Join-Path $deployDir "deploy-iis.bat"
if (Test-Path $deployBatPath) {
    Copy-Item $deployBatPath -Destination (Join-Path (Split-Path $packagePath) "deploy-iis.bat") -Force
    Write-Host "  ✓ deploy-iis.bat copiado junto al ZIP" -ForegroundColor Green
}

Write-Host ""
Write-Host "✓ Todo listo para despliegue!" -ForegroundColor Green
