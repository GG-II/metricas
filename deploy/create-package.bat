@echo off
REM ============================================
REM Crear Paquete de Despliegue
REM ============================================

echo =========================================
echo   CREAR PAQUETE DE DESPLIEGUE
echo =========================================
echo.

cd ..

REM Generar timestamp
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "timestamp=%dt:~0,8%-%dt:~8,6%"

set "packageName=metricas-it-deploy-%timestamp%.zip"
set "packagePath=deploy\%packageName%"

echo Preparando archivos para comprimir...
echo.

REM Verificar que existe PowerShell o tar
where powershell >nul 2>&1
if %errorLevel% equ 0 (
    echo Usando PowerShell para comprimir...

    REM Crear ZIP con PowerShell
    powershell -Command "& {Compress-Archive -Path .\* -DestinationPath '.\deploy\%packageName%' -Force -CompressionLevel Optimal -Exclude 'deploy\*.zip','deploy\*.ps1','logs\*','storage\cache\*','uploads\*','node_modules','*.git*','.vscode','.idea','*.log'}"

    if exist "deploy\%packageName%" (
        echo.
        echo =========================================
        echo   PAQUETE CREADO EXITOSAMENTE
        echo =========================================
        echo.
        echo Ubicacion: deploy\%packageName%

        REM Mostrar tamano
        for %%A in ("deploy\%packageName%") do set size=%%~zA
        set /a sizeMB=size/1024/1024
        echo Tamano: %sizeMB% MB
        echo.
        echo SIGUIENTE PASO:
        echo 1. Copiar el ZIP al servidor Windows
        echo 2. Descomprimir en C:\inetpub\wwwroot\metricas-it
        echo 3. Ejecutar: deploy-iis.bat
        echo.

        REM Copiar script de despliegue
        copy "deploy\deploy-iis.bat" "deploy\deploy-iis-server.bat" >nul 2>&1
        if exist "deploy\deploy-iis-server.bat" (
            echo deploy-iis.bat copiado como deploy-iis-server.bat
        )
        echo.
        echo OK Todo listo para despliegue!
        pause
    ) else (
        echo ERROR: No se pudo crear el paquete
        pause
        exit /b 1
    )
) else (
    echo ERROR: PowerShell no disponible
    echo Instalar PowerShell o crear ZIP manualmente
    pause
    exit /b 1
)
