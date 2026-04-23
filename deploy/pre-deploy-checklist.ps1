# ============================================
# Pre-Deployment Checklist
# Dashboard de Métricas IT
# ============================================

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  CHECKLIST PRE-DESPLIEGUE" -ForegroundColor Cyan
Write-Host "  Sistema de Métricas IT" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = Split-Path -Parent $PSScriptRoot
$errors = @()
$warnings = @()
$passed = 0
$failed = 0

# Función helper
function Test-Item {
    param(
        [string]$Name,
        [scriptblock]$Test,
        [string]$ErrorMessage,
        [string]$SuccessMessage,
        [switch]$Critical
    )

    Write-Host "Verificando: " -NoNewline
    Write-Host $Name -ForegroundColor Yellow -NoNewline
    Write-Host "..." -NoNewline

    try {
        $result = & $Test
        if ($result) {
            Write-Host " ✓" -ForegroundColor Green
            if ($SuccessMessage) {
                Write-Host "  └─ $SuccessMessage" -ForegroundColor Gray
            }
            $script:passed++
            return $true
        } else {
            Write-Host " ✗" -ForegroundColor Red
            Write-Host "  └─ $ErrorMessage" -ForegroundColor Red
            if ($Critical) {
                $script:errors += $ErrorMessage
            } else {
                $script:warnings += $ErrorMessage
            }
            $script:failed++
            return $false
        }
    } catch {
        Write-Host " ✗" -ForegroundColor Red
        Write-Host "  └─ Error: $($_.Exception.Message)" -ForegroundColor Red
        if ($Critical) {
            $script:errors += $_.Exception.Message
        } else {
            $script:warnings += $_.Exception.Message
        }
        $script:failed++
        return $false
    }
}

Write-Host "1. VALIDACIÓN DE ARCHIVOS CRÍTICOS" -ForegroundColor Cyan
Write-Host "-----------------------------------" -ForegroundColor Cyan

Test-Item -Name "config.php existe" -Critical -Test {
    Test-Path "$baseDir\config.php"
} -ErrorMessage "Falta config.php" -SuccessMessage "Archivo encontrado"

Test-Item -Name "includes/db.php" -Critical -Test {
    Test-Path "$baseDir\includes\db.php"
} -ErrorMessage "Falta includes/db.php"

Test-Item -Name "includes/functions.php" -Critical -Test {
    Test-Path "$baseDir\includes\functions.php"
} -ErrorMessage "Falta includes/functions.php"

Test-Item -Name "public/index.php" -Critical -Test {
    Test-Path "$baseDir\public\index.php"
} -ErrorMessage "Falta public/index.php"

Test-Item -Name "public/login.php" -Critical -Test {
    Test-Path "$baseDir\public\login.php"
} -ErrorMessage "Falta public/login.php"

Write-Host ""
Write-Host "2. VALIDACIÓN DE SINTAXIS PHP" -ForegroundColor Cyan
Write-Host "------------------------------" -ForegroundColor Cyan

$phpPath = "C:\xampp\php\php.exe"
if (-not (Test-Path $phpPath)) {
    $phpPath = "php" # Intentar desde PATH
}

$phpFiles = Get-ChildItem -Path $baseDir -Recurse -Filter "*.php" -Exclude "vendor"

$syntaxErrors = 0
foreach ($file in $phpFiles | Select-Object -First 10) {
    $relativePath = $file.FullName.Replace($baseDir, "").TrimStart('\')

    $result = & $phpPath -l $file.FullName 2>&1
    if ($result -match "No syntax errors detected") {
        Write-Host "  ✓ $relativePath" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  ✗ $relativePath" -ForegroundColor Red
        Write-Host "    └─ $result" -ForegroundColor Red
        $errors += "Error de sintaxis en $relativePath"
        $syntaxErrors++
        $failed++
    }
}

if ($syntaxErrors -eq 0) {
    Write-Host "  └─ Primeros 10 archivos validados correctamente" -ForegroundColor Gray
}

Write-Host ""
Write-Host "3. CONFIGURACIÓN DE ENTORNO" -ForegroundColor Cyan
Write-Host "---------------------------" -ForegroundColor Cyan

Test-Item -Name "ENVIRONMENT = 'production'" -Critical -Test {
    $configContent = Get-Content "$baseDir\config.php" -Raw
    $configContent -match "define\('ENVIRONMENT',\s*'production'\)"
} -ErrorMessage "ENVIRONMENT debe ser 'production' en config.php" -SuccessMessage "Configurado correctamente"

Test-Item -Name "display_errors configurado" -Test {
    $configContent = Get-Content "$baseDir\config.php" -Raw
    $configContent -match "ini_set\('display_errors',\s*0\)"
} -ErrorMessage "display_errors debe estar en 0 para producción"

Test-Item -Name "log_errors habilitado" -Test {
    $configContent = Get-Content "$baseDir\config.php" -Raw
    $configContent -match "ini_set\('log_errors',\s*1\)"
} -ErrorMessage "log_errors debe estar en 1"

Write-Host ""
Write-Host "4. ESTRUCTURA DE DIRECTORIOS" -ForegroundColor Cyan
Write-Host "-----------------------------" -ForegroundColor Cyan

Test-Item -Name "Carpeta public/" -Critical -Test {
    Test-Path "$baseDir\public"
} -ErrorMessage "Falta carpeta public/"

Test-Item -Name "Carpeta includes/" -Critical -Test {
    Test-Path "$baseDir\includes"
} -ErrorMessage "Falta carpeta includes/"

Test-Item -Name "Carpeta src/" -Critical -Test {
    Test-Path "$baseDir\src"
} -ErrorMessage "Falta carpeta src/"

Test-Item -Name "Carpeta database/" -Test {
    Test-Path "$baseDir\database"
} -ErrorMessage "Falta carpeta database/"

Test-Item -Name "Carpeta logs/ (será creada)" -Test {
    if (-not (Test-Path "$baseDir\logs")) {
        New-Item -ItemType Directory -Path "$baseDir\logs" -Force | Out-Null
        return $true
    }
    return $true
} -SuccessMessage "Carpeta creada o existe"

Test-Item -Name "Carpeta storage/cache/ (será creada)" -Test {
    if (-not (Test-Path "$baseDir\storage\cache")) {
        New-Item -ItemType Directory -Path "$baseDir\storage\cache" -Force | Out-Null
        return $true
    }
    return $true
} -SuccessMessage "Carpeta creada o existe"

Write-Host ""
Write-Host "5. ARCHIVOS DE CONFIGURACIÓN" -ForegroundColor Cyan
Write-Host "-----------------------------" -ForegroundColor Cyan

Test-Item -Name "vendor/autoload.php" -Critical -Test {
    Test-Path "$baseDir\vendor\autoload.php"
} -ErrorMessage "Falta vendor/autoload.php - Ejecutar 'composer install'"

Test-Item -Name "database/schema.sql" -Test {
    Test-Path "$baseDir\database\schema.sql"
} -ErrorMessage "Falta database/schema.sql"

Test-Item -Name "web.config preparado" -Test {
    Test-Path "$baseDir\public\web.config"
} -ErrorMessage "Falta public/web.config para IIS"

Write-Host ""
Write-Host "6. ARCHIVOS SENSIBLES" -ForegroundColor Cyan
Write-Host "---------------------" -ForegroundColor Cyan

Test-Item -Name ".gitignore existe" -Test {
    Test-Path "$baseDir\.gitignore"
} -ErrorMessage ".gitignore no encontrado"

Test-Item -Name "Verificar credenciales en config.php" -Test {
    $configContent = Get-Content "$baseDir\config.php" -Raw
    # Verificar que no tenga credenciales por defecto peligrosas
    if ($configContent -match "DB_PASS',\s*''\)" -or $configContent -match "DB_PASS',\s*'password'\)") {
        return $false
    }
    return $true
} -ErrorMessage "Usar contraseña segura en config.php"

Write-Host ""
Write-Host "7. TAMAÑO Y COMPRESIÓN" -ForegroundColor Cyan
Write-Host "----------------------" -ForegroundColor Cyan

$totalSize = (Get-ChildItem -Path $baseDir -Recurse -File -Exclude "node_modules","vendor" | Measure-Object -Property Length -Sum).Sum
$sizeMB = [math]::Round($totalSize / 1MB, 2)

Write-Host "  Tamaño total: $sizeMB MB" -ForegroundColor Gray

if ($sizeMB -lt 100) {
    Write-Host "  ✓ Tamaño aceptable para despliegue" -ForegroundColor Green
    $passed++
} else {
    Write-Host "  ⚠ Tamaño grande, considerar optimizar" -ForegroundColor Yellow
    $warnings += "Tamaño del proyecto es $sizeMB MB"
}

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  RESUMEN" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Verificaciones pasadas: " -NoNewline
Write-Host "$passed" -ForegroundColor Green

Write-Host "Verificaciones fallidas: " -NoNewline
Write-Host "$failed" -ForegroundColor Red

Write-Host ""

if ($errors.Count -gt 0) {
    Write-Host "ERRORES CRÍTICOS:" -ForegroundColor Red
    foreach ($error in $errors) {
        Write-Host "  ✗ $error" -ForegroundColor Red
    }
    Write-Host ""
}

if ($warnings.Count -gt 0) {
    Write-Host "ADVERTENCIAS:" -ForegroundColor Yellow
    foreach ($warning in $warnings) {
        Write-Host "  ⚠ $warning" -ForegroundColor Yellow
    }
    Write-Host ""
}

if ($errors.Count -eq 0) {
    Write-Host "✓ SISTEMA LISTO PARA DESPLIEGUE" -ForegroundColor Green
    Write-Host ""
    Write-Host "Siguiente paso:" -ForegroundColor Cyan
    Write-Host "  .\deploy\create-deployment-package.ps1" -ForegroundColor White
    exit 0
} else {
    Write-Host "✗ CORREGIR ERRORES ANTES DE DESPLEGAR" -ForegroundColor Red
    exit 1
}
