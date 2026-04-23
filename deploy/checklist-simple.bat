@echo off
REM ============================================
REM Checklist Simple Pre-Despliegue
REM ============================================

echo =========================================
echo   CHECKLIST PRE-DESPLIEGUE
echo   Sistema de Metricas IT
echo =========================================
echo.

cd ..

set ERRORES=0

echo [1] Verificando archivos criticos...
if not exist "config.php" (echo    X config.php NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK config.php)
if not exist "includes\db.php" (echo    X includes\db.php NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK includes\db.php)
if not exist "includes\functions.php" (echo    X includes\functions.php NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK includes\functions.php)
if not exist "public\index.php" (echo    X public\index.php NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK public\index.php)
if not exist "public\login.php" (echo    X public\login.php NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK public\login.php)
echo.

echo [2] Verificando estructura de directorios...
if not exist "public" (echo    X public\ NO EXISTE & set /a ERRORES+=1) else (echo    OK public\)
if not exist "includes" (echo    X includes\ NO EXISTE & set /a ERRORES+=1) else (echo    OK includes\)
if not exist "src" (echo    X src\ NO EXISTE & set /a ERRORES+=1) else (echo    OK src\)
if not exist "vendor\autoload.php" (echo    X vendor\autoload.php NO EXISTE & set /a ERRORES+=1) else (echo    OK vendor\autoload.php)
echo.

echo [3] Creando directorios necesarios...
if not exist "logs" mkdir logs && echo    OK logs\ creada
if not exist "storage\cache" mkdir storage\cache && echo    OK storage\cache\ creada
if not exist "uploads" mkdir uploads && echo    OK uploads\ creada
echo.

echo [4] Verificando configuracion...
findstr /C:"ENVIRONMENT', 'production'" config.php >nul
if %errorLevel% equ 0 (
    echo    OK ENVIRONMENT = production
) else (
    echo    ADVERTENCIA: Verificar ENVIRONMENT en config.php
)
echo.

echo [5] Verificando archivos de despliegue...
if not exist "deploy\deploy-iis.bat" (echo    X deploy-iis.bat NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK deploy-iis.bat)
if not exist "public\web.config" (echo    X web.config NO ENCONTRADO & set /a ERRORES+=1) else (echo    OK web.config)
if not exist "database\schema.sql" (echo    ! schema.sql no encontrado - necesario para BD) else (echo    OK schema.sql)
echo.

echo =========================================
echo   RESUMEN
echo =========================================
if %ERRORES% equ 0 (
    echo.
    echo OK SISTEMA LISTO PARA DESPLIEGUE
    echo.
    echo Siguiente paso:
    echo   deploy\create-package.bat
    echo.
    exit /b 0
) else (
    echo.
    echo ERROR: Se encontraron %ERRORES% errores criticos
    echo Corregir antes de continuar
    echo.
    pause
    exit /b 1
)
