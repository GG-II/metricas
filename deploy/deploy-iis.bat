@echo off
REM ============================================
REM Deployment Script for IIS
REM Dashboard de Métricas IT
REM ============================================

echo =========================================
echo   DESPLIEGUE AUTOMATICO EN IIS
echo   Dashboard de Métricas IT
echo =========================================
echo.

REM Verificar que se ejecuta como Administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Este script requiere permisos de Administrador
    echo Por favor, ejecute como Administrador
    pause
    exit /b 1
)

REM ============================================
REM CONFIGURACION
REM ============================================

set SITE_NAME=metricas-it
set APP_POOL_NAME=metricas-it-pool
set SITE_PATH=C:\inetpub\wwwroot\metricas-it
set DOMAIN=siscoop.cicrl.com.gt
set PHP_PATH=C:\PHP\php-cgi.exe

echo Configuracion:
echo   Sitio: %SITE_NAME%
echo   Ruta:  %SITE_PATH%
echo   Pool:  %APP_POOL_NAME%
echo   Dominio: %DOMAIN%
echo.

REM ============================================
REM 1. VERIFICAR RUTA ACTUAL
REM ============================================

echo [1/8] Verificando ruta de instalacion...

if not exist "%SITE_PATH%" (
    echo ERROR: La ruta %SITE_PATH% no existe
    echo Asegurese de haber descomprimido los archivos en esa ubicacion
    pause
    exit /b 1
)

if not exist "%SITE_PATH%\public\index.php" (
    echo ERROR: No se encuentra public\index.php en %SITE_PATH%
    echo Verifique que descomprimio correctamente el paquete
    pause
    exit /b 1
)

echo   OK - Archivos encontrados
echo.

REM ============================================
REM 2. VERIFICAR PHP
REM ============================================

echo [2/8] Verificando PHP...

if not exist "%PHP_PATH%" (
    echo ERROR: PHP no encontrado en %PHP_PATH%
    echo Instale PHP o ajuste la ruta PHP_PATH en este script
    pause
    exit /b 1
)

php --version >nul 2>&1
if %errorLevel% neq 0 (
    echo ADVERTENCIA: PHP no esta en PATH, pero se encontro en %PHP_PATH%
) else (
    echo   OK - PHP instalado
)
echo.

REM ============================================
REM 3. CREAR APPLICATION POOL
REM ============================================

echo [3/8] Configurando Application Pool...

REM Verificar si ya existe
%systemroot%\system32\inetsrv\appcmd.exe list apppool "%APP_POOL_NAME%" >nul 2>&1
if %errorLevel% equ 0 (
    echo   Pool '%APP_POOL_NAME%' ya existe, eliminando...
    %systemroot%\system32\inetsrv\appcmd.exe delete apppool "%APP_POOL_NAME%"
)

REM Crear nuevo pool
%systemroot%\system32\inetsrv\appcmd.exe add apppool /name:"%APP_POOL_NAME%" /managedRuntimeVersion:"" /managedPipelineMode:Integrated

REM Configurar identidad
%systemroot%\system32\inetsrv\appcmd.exe set apppool "%APP_POOL_NAME%" /processModel.identityType:ApplicationPoolIdentity

REM Configurar reciclaje (29 horas)
%systemroot%\system32\inetsrv\appcmd.exe set apppool "%APP_POOL_NAME%" /recycling.periodicRestart.time:29:00:00

echo   OK - Application Pool creado
echo.

REM ============================================
REM 4. CREAR SITIO WEB
REM ============================================

echo [4/8] Configurando Sitio Web...

REM Verificar si ya existe
%systemroot%\system32\inetsrv\appcmd.exe list site "%SITE_NAME%" >nul 2>&1
if %errorLevel% equ 0 (
    echo   Sitio '%SITE_NAME%' ya existe, eliminando...
    %systemroot%\system32\inetsrv\appcmd.exe delete site "%SITE_NAME%"
)

REM Crear sitio
%systemroot%\system32\inetsrv\appcmd.exe add site /name:"%SITE_NAME%" /physicalPath:"%SITE_PATH%\public" /bindings:http/*:80:%DOMAIN%

REM Asignar application pool
%systemroot%\system32\inetsrv\appcmd.exe set site "%SITE_NAME%" /applicationDefaults.applicationPool:"%APP_POOL_NAME%"

REM Configurar documento por defecto
%systemroot%\system32\inetsrv\appcmd.exe set config "%SITE_NAME%" /section:defaultDocument /enabled:true
%systemroot%\system32\inetsrv\appcmd.exe set config "%SITE_NAME%" /section:defaultDocument /+"files.[value='index.php']" /commit:apphost

echo   OK - Sitio web creado
echo.

REM ============================================
REM 5. CONFIGURAR PERMISOS
REM ============================================

echo [5/8] Configurando permisos de carpetas...

REM Dar permisos al usuario IIS
icacls "%SITE_PATH%" /grant "IIS_IUSRS:(OI)(CI)RX" /T /Q >nul 2>&1
icacls "%SITE_PATH%" /grant "IUSR:(OI)(CI)RX" /T /Q >nul 2>&1

REM Permisos de escritura en carpetas específicas
icacls "%SITE_PATH%\logs" /grant "IIS_IUSRS:(OI)(CI)F" /T /Q >nul 2>&1
icacls "%SITE_PATH%\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T /Q >nul 2>&1
icacls "%SITE_PATH%\uploads" /grant "IIS_IUSRS:(OI)(CI)F" /T /Q >nul 2>&1

echo   OK - Permisos configurados
echo.

REM ============================================
REM 6. CONFIGURAR HANDLER DE PHP
REM ============================================

echo [6/8] Configurando handler de PHP...

REM Verificar si handler ya existe
%systemroot%\system32\inetsrv\appcmd.exe list config -section:system.webServer/handlers | findstr "PHP_via_FastCGI" >nul 2>&1
if %errorLevel% equ 0 (
    echo   Handler PHP ya configurado globalmente
) else (
    echo   Configurando handler PHP...

    REM Configurar FastCGI
    %systemroot%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%PHP_PATH%']" /commit:apphost

    REM Configurar Handler
    %systemroot%\system32\inetsrv\appcmd.exe set config -section:system.webServer/handlers /+"[name='PHP_via_FastCGI',path='*.php',verb='GET,HEAD,POST,PUT,DELETE',modules='FastCgiModule',scriptProcessor='%PHP_PATH%',resourceType='Either']" /commit:apphost

    echo   OK - Handler PHP configurado
)
echo.

REM ============================================
REM 7. VERIFICAR WEB.CONFIG
REM ============================================

echo [7/8] Verificando web.config...

if exist "%SITE_PATH%\public\web.config" (
    echo   OK - web.config encontrado
) else (
    echo   ADVERTENCIA: No se encontro web.config
    echo   El sitio podria no funcionar correctamente
)
echo.

REM ============================================
REM 8. INICIAR SITIO
REM ============================================

echo [8/8] Iniciando sitio...

%systemroot%\system32\inetsrv\appcmd.exe start site "%SITE_NAME%"
%systemroot%\system32\inetsrv\appcmd.exe start apppool "%APP_POOL_NAME%"

echo   OK - Sitio iniciado
echo.

REM ============================================
REM RESUMEN
REM ============================================

echo =========================================
echo   DESPLIEGUE COMPLETADO
echo =========================================
echo.
echo Sitio creado: %SITE_NAME%
echo URL: http://%DOMAIN%
echo Ruta fisica: %SITE_PATH%\public
echo.
echo SIGUIENTE PASO:
echo 1. Configurar base de datos en config.php
echo 2. Importar schema.sql a la base de datos
echo 3. Acceder a: http://%DOMAIN%/login.php
echo.
echo Para ver el sitio en IIS Manager, ejecute: inetmgr
echo.

pause
