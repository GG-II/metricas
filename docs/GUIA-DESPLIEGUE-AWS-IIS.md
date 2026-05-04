# 🚀 Guía Completa de Despliegue en AWS (Windows Server + IIS)

**Sistema:** Dashboard de Métricas IT  
**Servidor:** Windows Server con IIS (Microsoft-IIS/10.0)  
**PHP Version:** 8.5.4  
**Base de Datos:** MySQL/MariaDB  
**Fecha:** Abril 2026  

---

## 📋 Tabla de Contenidos

1. [Preparación del Entorno](#1-preparación-del-entorno)
2. [Configuración del Servidor IIS](#2-configuración-del-servidor-iis)
3. [Despliegue de la Aplicación](#3-despliegue-de-la-aplicación)
4. [Configuración de Base de Datos](#4-configuración-de-base-de-datos)
5. [Solución de Errores Comunes](#5-solución-de-errores-comunes)
6. [Lecciones Aprendidas](#6-lecciones-aprendidas)
7. [Checklist de Despliegue](#7-checklist-de-despliegue)
8. [Herramientas de Diagnóstico](#8-herramientas-de-diagnóstico)

---

## 1. Preparación del Entorno

### 1.1. Requisitos del Servidor

```yaml
Sistema Operativo: Windows Server 2019/2022
Servidor Web: IIS 10.0+
PHP: 8.1+ (recomendado 8.5.4)
Base de Datos: MySQL 8.0+ o MariaDB 10.5+
Memoria RAM: Mínimo 4GB (recomendado 8GB)
Disco: Mínimo 20GB libres
```

### 1.2. Instalación de PHP en IIS

**Paso 1: Descargar PHP**
```powershell
# Descargar desde https://windows.php.net/download/
# Elegir: "Non-Thread Safe (NTS)" para IIS
```

**Paso 2: Extraer PHP**
```powershell
# Extraer a: C:\PHP\
mkdir C:\PHP
# Copiar archivos de PHP aquí
```

**Paso 3: Configurar php.ini**
```ini
# Copiar php.ini-production a php.ini
cp C:\PHP\php.ini-production C:\PHP\php.ini

# Editar php.ini y habilitar extensiones necesarias:
extension=mysqli
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=curl
extension=gd
extension=fileinfo

# Configurar zona horaria
date.timezone = America/Guatemala

# Configurar límites
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 300

# Configurar errores (IMPORTANTE)
display_errors = Off  ; En producción SIEMPRE Off
log_errors = On
error_log = C:\inetpub\wwwroot\logs\php-errors.log
```

**Paso 4: Agregar PHP al PATH**
```powershell
# Panel de Control > Sistema > Variables de entorno
# Agregar a PATH: C:\PHP
```

**Paso 5: Instalar PHP en IIS**
```powershell
# Descargar e instalar Web Platform Installer (Web PI)
# O usar PowerShell:

# Agregar Handler de PHP a IIS
C:\Windows\System32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='C:\PHP\php-cgi.exe']" /commit:apphost

C:\Windows\System32\inetsrv\appcmd.exe set config -section:system.webServer/handlers /+"[name='PHP_via_FastCGI',path='*.php',verb='GET,HEAD,POST',modules='FastCgiModule',scriptProcessor='C:\PHP\php-cgi.exe',resourceType='Either']"
```

### 1.3. Instalación de MySQL

**Opción A: MySQL Community Server**
```powershell
# Descargar desde: https://dev.mysql.com/downloads/mysql/
# Instalar con configuración por defecto
# Anotar credenciales de root
```

**Opción B: MariaDB**
```powershell
# Descargar desde: https://mariadb.org/download/
# Recomendado para compatibilidad
```

**Verificar instalación:**
```powershell
mysql --version
# mysql  Ver 8.0.xx for Win64
```

---

## 2. Configuración del Servidor IIS

### 2.1. Crear Sitio en IIS

**Paso 1: Abrir IIS Manager**
```
Win + R → inetmgr → Enter
```

**Paso 2: Crear Application Pool**
```
1. Click derecho en "Application Pools" → Add Application Pool
2. Name: metricas-it-pool
3. .NET CLR version: No Managed Code
4. Managed pipeline mode: Integrated
5. Click OK
```

**Paso 3: Configurar Application Pool**
```
1. Click derecho en metricas-it-pool → Advanced Settings
2. Process Model → Identity: ApplicationPoolIdentity
3. Recycling → Regular Time Interval: 1740 (29 horas)
4. Click OK
```

**Paso 4: Crear Sitio Web**
```
1. Click derecho en "Sites" → Add Website
2. Site name: metricas-it
3. Application pool: metricas-it-pool
4. Physical path: C:\inetpub\wwwroot\metricas-it
5. Binding:
   - Type: http
   - IP: All Unassigned
   - Port: 80
   - Host name: siscoop.cicrl.com.gt
6. Click OK
```

### 2.2. Configurar Permisos

**Permisos de carpeta:**
```powershell
# Dar permisos al usuario IIS
icacls "C:\inetpub\wwwroot\metricas-it" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas-it" /grant "IUSR:(OI)(CI)F" /T

# Permisos específicos para carpetas de escritura
icacls "C:\inetpub\wwwroot\metricas-it\uploads" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas-it\logs" /grant "IIS_IUSRS:(OI)(CI)F" /T
```

### 2.3. Configurar web.config

Crear archivo `web.config` en la raíz del proyecto:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <!-- Habilitar URL Rewrite -->
        <rewrite>
            <rules>
                <rule name="PHP via FastCGI" stopProcessing="true">
                    <match url="^(.*)$" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php/{R:1}" />
                </rule>
            </rules>
        </rewrite>
        
        <!-- Configuración de errores -->
        <httpErrors errorMode="Detailed" />
        
        <!-- Configuración de PHP -->
        <handlers>
            <add name="PHP_via_FastCGI" 
                 path="*.php" 
                 verb="GET,HEAD,POST,PUT,DELETE,OPTIONS" 
                 modules="FastCgiModule" 
                 scriptProcessor="C:\PHP\php-cgi.exe" 
                 resourceType="Either" 
                 requireAccess="Script" />
        </handlers>
        
        <!-- Seguridad -->
        <security>
            <requestFiltering>
                <requestLimits maxAllowedContentLength="52428800" />
            </requestFiltering>
        </security>
        
        <!-- Configuración de directorios por defecto -->
        <defaultDocument>
            <files>
                <clear />
                <add value="index.php" />
                <add value="index.html" />
            </files>
        </defaultDocument>
    </system.webServer>
</configuration>
```

---

## 3. Despliegue de la Aplicación

### 3.1. Preparar el Código

**En tu máquina local:**

```bash
# Crear archivo .gitignore o lista de exclusión
uploads/
logs/
*.log
config.php  # No subir con credenciales
.env
node_modules/
vendor/
```

**Comprimir proyecto:**
```powershell
# Crear ZIP del proyecto
Compress-Archive -Path .\metricas-it\* -DestinationPath metricas-it.zip
```

### 3.2. Subir Archivos al Servidor

**Opción A: FTP/SFTP**
```powershell
# Usar FileZilla, WinSCP o similar
# Conectar a: ftp://tu-servidor-aws.com
# Subir a: C:\inetpub\wwwroot\metricas-it\
```

**Opción B: RDP (Escritorio Remoto)**
```powershell
# Conectar vía RDP
mstsc /v:tu-servidor-aws.com

# Copiar archivos directamente
```

### 3.3. Configurar el Proyecto

**Paso 1: Crear directorios necesarios**
```powershell
mkdir C:\inetpub\wwwroot\metricas-it\logs
mkdir C:\inetpub\wwwroot\metricas-it\uploads
mkdir C:\inetpub\wwwroot\metricas-it\uploads\avatars
```

**Paso 2: Configurar config.php**

```php
<?php
/**
 * Configuración principal del sistema
 */

// BASE DE DATOS
define('DB_HOST', 'localhost');
define('DB_NAME', 'metricas_it_prod');  // Nombre de BD en producción
define('DB_USER', 'metricas_user');      // Usuario específico
define('DB_PASS', 'TU_PASSWORD_SEGURO'); // Password seguro
define('DB_CHARSET', 'utf8mb4');

// SISTEMA
define('APP_NAME', 'Dashboard de Métricas IT');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Guatemala');

// RUTAS
define('BASE_PATH', __DIR__);
define('ASSETS_PATH', BASE_PATH . '/assets');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('BASE_URL', '/metricas-it');  // O '/' si está en raíz

// ENTORNO - ⚠️ CRÍTICO: Siempre 'production' en servidor
define('ENVIRONMENT', 'production');

// Configuración de errores
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/php-errors.log');
}

date_default_timezone_set(TIMEZONE);

// Sesiones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Includes
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

// Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_VIEWER', 'viewer');

// IDs de áreas
define('AREA_SOFTWARE', 1);
define('AREA_INFRAESTRUCTURA', 2);
define('AREA_SOPORTE', 3);
define('AREA_CIBERSEGURIDAD', 4);
define('AREA_MEDIOS_DIGITALES', 5);
```

**⚠️ IMPORTANTE:** 
- Nunca usar credenciales reales en repositorios Git
- Siempre `ENVIRONMENT = 'production'` en servidor
- Contraseñas seguras (mínimo 16 caracteres, alfanuméricos + símbolos)

---

## 4. Configuración de Base de Datos

### 4.1. Crear Base de Datos

```sql
-- Conectar como root
mysql -u root -p

-- Crear base de datos
CREATE DATABASE metricas_it_prod 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Crear usuario específico
CREATE USER 'metricas_user'@'localhost' 
IDENTIFIED BY 'TU_PASSWORD_SEGURO';

-- Otorgar permisos
GRANT ALL PRIVILEGES ON metricas_it_prod.* 
TO 'metricas_user'@'localhost';

FLUSH PRIVILEGES;
```

### 4.2. Importar Schema

```powershell
# Importar desde archivo SQL
mysql -u metricas_user -p metricas_it_prod < schema.sql

# O desde MySQL Workbench:
# Server → Data Import → Import from Self-Contained File
```

### 4.3. Verificar Conexión

Crear archivo `test-db.php` en la raíz:

```php
<?php
// test-db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'metricas_it_prod';
$user = 'metricas_user';
$pass = 'TU_PASSWORD';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Conexión exitosa a la base de datos<br>";
    echo "Servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    
    // Probar query
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Usuarios en BD: " . $result['total'];
    
} catch (PDOException $e) {
    echo "✗ Error de conexión: " . $e->getMessage();
}
```

Acceder a: `http://tu-dominio/metricas-it/test-db.php`

**⚠️ ELIMINAR test-db.php después de verificar**

---

## 5. Solución de Errores Comunes

### 5.1. Error 500 - Internal Server Error

**Síntomas:**
- Página en blanco
- "HTTP ERROR 500"
- Sin mensaje de error visible

**Causas comunes:**

#### A) Error de sintaxis en PHP

**Solución:**
```php
// 1. Activar temporalmente errores en config.php
define('ENVIRONMENT', 'development');  // Cambiar temporalmente

// 2. Revisar logs
C:\inetpub\wwwroot\metricas-it\logs\php-errors.log

// 3. Revisar Event Viewer de Windows
eventvwr.msc → Windows Logs → Application
```

#### B) Llave de cierre faltante o extra

**Ejemplo del problema real que tuvimos:**

```php
// ❌ INCORRECTO - ValorMetrica.php línea 261
class ValorMetrica extends Model {
    public function getHistorico() {
        return $data;
    }
}  // ← Llave que cierra la clase prematuramente

    // ← Métodos fuera de la clase = ParseError
    public function getByMetricaYPeriodoConMeta() {
        // ...
    }
}

// ✓ CORRECTO
class ValorMetrica extends Model {
    public function getHistorico() {
        return $data;
    }
    // SIN llave extra aquí
    
    public function getByMetricaYPeriodoConMeta() {
        // ...
    }
}  // ← Una sola llave al final
```

**Cómo detectarlo:**
1. Usar herramienta de diagnóstico (ver sección 8)
2. Revisar línea exacta del error en logs
3. Contar llaves de apertura `{` vs cierre `}`

#### C) Función inexistente

**Error típico:**
```
Fatal error: Call to undefined function calcularCumplimiento()
```

**Solución:**
- Verificar que la función esté definida en `includes/functions.php`
- Verificar que `functions.php` se cargue en `config.php`
- No tener funciones duplicadas

### 5.2. Error de Conexión a Base de Datos

**Error típico:**
```
SQLSTATE[HY000] [1045] Access denied for user 'metricas_user'@'localhost'
```

**Solución:**

```sql
-- 1. Verificar usuario existe
SELECT User, Host FROM mysql.user WHERE User = 'metricas_user';

-- 2. Verificar permisos
SHOW GRANTS FOR 'metricas_user'@'localhost';

-- 3. Recrear usuario si es necesario
DROP USER 'metricas_user'@'localhost';
CREATE USER 'metricas_user'@'localhost' IDENTIFIED BY 'nueva_password';
GRANT ALL PRIVILEGES ON metricas_it_prod.* TO 'metricas_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5.3. Permisos de Archivos

**Error típico:**
```
Warning: file_put_contents(): failed to open stream: Permission denied
```

**Solución:**
```powershell
# Dar permisos completos a carpetas de escritura
icacls "C:\inetpub\wwwroot\metricas-it\logs" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas-it\uploads" /grant "IIS_IUSRS:(OI)(CI)F" /T

# Verificar propietario
# Click derecho en carpeta → Properties → Security → Advanced
```

### 5.4. Sesiones No Persisten

**Síntoma:** Login funciona pero inmediatamente cierra sesión

**Solución:**

```php
// 1. Verificar permisos en carpeta de sesiones
// En php.ini:
session.save_path = "C:\inetpub\temp\sessions"

// 2. Crear carpeta y dar permisos
mkdir C:\inetpub\temp\sessions
icacls "C:\inetpub\temp\sessions" /grant "IIS_IUSRS:(OI)(CI)F" /T

// 3. Verificar que session_start() se llama en config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### 5.5. Assets No Cargan (CSS/JS)

**Síntoma:** Página sin estilos, JavaScript no funciona

**Solución:**

```php
// 1. Verificar BASE_URL en config.php
define('BASE_URL', '/metricas-it');  // Con slash inicial, sin slash final

// 2. Verificar rutas en HTML
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">

// 3. Verificar MIME types en IIS
// IIS Manager → MIME Types → Verify:
.css → text/css
.js → application/javascript
.json → application/json
```

---

## 6. Lecciones Aprendidas

### 6.1. Siempre Usar Diagnóstico Antes de Desplegar

**❌ Error común:**
Subir código directamente al servidor sin probar archivos individualmente.

**✓ Mejor práctica:**
Usar script de diagnóstico que prueba cada include de forma aislada.

```php
// Herramienta creada: login-con-diagnostico.php
// Beneficios:
// - Detecta errores sin romper la aplicación
// - Muestra línea exacta del error
// - Se puede dejar permanentemente en login.php
```

### 6.2. Entorno de Producción ≠ Desarrollo

**❌ Error común:**
```php
define('ENVIRONMENT', 'production');
error_reporting(E_ALL);
ini_set('display_errors', 1);  // ← Inconsistente!
```

**✓ Mejor práctica:**
```php
define('ENVIRONMENT', 'production');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/php-errors.log');
}
```

**Regla de oro:**
- **Desarrollo:** Errores visibles en pantalla
- **Producción:** Errores solo en logs, nunca en pantalla

### 6.3. Validar Sintaxis ANTES de Subir

**Herramientas:**

```powershell
# Validar sintaxis de un archivo PHP
php -l archivo.php

# Validar todos los archivos PHP del proyecto
Get-ChildItem -Path .\metricas-it -Recurse -Filter *.php | ForEach-Object {
    php -l $_.FullName
}
```

**En VS Code:**
- Instalar extensión: "PHP Intelephense"
- Detecta errores de sintaxis en tiempo real

### 6.4. Control de Versiones es Crítico

**❌ Error común:**
Editar directamente archivos en producción sin backup.

**✓ Mejor práctica:**

```bash
# Usar Git
git init
git add .
git commit -m "Initial commit"

# Antes de modificar en producción
git branch hotfix/fix-valormetrica
git checkout hotfix/fix-valormetrica
# ... hacer cambios ...
git commit -m "Fix ValorMetrica syntax error"
git checkout main
git merge hotfix/fix-valormetrica
```

**Alternativa sin Git:**
```powershell
# Crear backup antes de modificar
Copy-Item "C:\inetpub\wwwroot\metricas-it" `
          "C:\backups\metricas-it-$(Get-Date -Format 'yyyyMMdd-HHmmss')" `
          -Recurse
```

### 6.5. Logs Son Tu Mejor Amigo

**Configurar logging robusto:**

```php
// includes/functions.php
function logError($message, $context = []) {
    $logFile = BASE_PATH . '/logs/app-errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    
    $logEntry = "[$timestamp] $message";
    if ($contextStr) {
        $logEntry .= " | Context: $contextStr";
    }
    $logEntry .= PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Uso
try {
    $result = $model->getData();
} catch (Exception $e) {
    logError('Error al obtener datos', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'user_id' => $_SESSION['user_id'] ?? 'guest'
    ]);
}
```

### 6.6. Separar Configuración por Entorno

**Estructura recomendada:**

```
metricas-it/
├── config.php                  (carga config según entorno)
├── config.development.php      (no subir a Git)
├── config.production.php       (no subir a Git)
├── config.example.php          (template, SÍ en Git)
└── .gitignore
```

```php
// config.php
<?php
$environment = getenv('APP_ENV') ?: 'production';

if ($environment === 'development') {
    require_once __DIR__ . '/config.development.php';
} else {
    require_once __DIR__ . '/config.production.php';
}
```

---

## 7. Checklist de Despliegue

### Pre-Despliegue

- [ ] Código validado sintácticamente (`php -l`)
- [ ] Tests locales ejecutados y pasando
- [ ] Credenciales de BD actualizadas para producción
- [ ] `ENVIRONMENT = 'production'` en config.php
- [ ] `display_errors = Off` en php.ini
- [ ] Assets compilados/minimizados
- [ ] Backup del servidor actual creado
- [ ] .gitignore configurado (no subir config.php con credenciales)

### Durante Despliegue

- [ ] Crear backup de archivos actuales
- [ ] Exportar backup de base de datos
- [ ] Subir archivos nuevos
- [ ] Ejecutar migraciones de BD (si aplica)
- [ ] Verificar permisos de carpetas
- [ ] Verificar config.php tiene credenciales correctas
- [ ] Limpiar caché de OPcache (si está habilitado)

### Post-Despliegue

- [ ] Verificar login funciona
- [ ] Verificar dashboard carga correctamente
- [ ] Probar creación/edición de registros
- [ ] Verificar logs no muestran errores críticos
- [ ] Probar en múltiples navegadores
- [ ] Verificar en modo móvil/responsivo
- [ ] Verificar permisos de usuarios (admin vs viewer)
- [ ] Monitorear logs por 24 horas

---

## 8. Herramientas de Diagnóstico

### 8.1. Script de Diagnóstico Integrado

**Archivo:** `login-con-diagnostico.php`

Este script se integra en `login.php` y muestra en la consola del navegador qué archivos cargan correctamente y cuáles fallan.

**Ventajas:**
- ✅ No rompe la aplicación
- ✅ Muestra errores en consola (F12)
- ✅ Detecta línea exacta del error
- ✅ Puede dejarse permanentemente

**Uso:**
1. Agregar código de diagnóstico al inicio de `login.php`
2. Abrir navegador en página de login
3. Presionar F12 → Console
4. Ver resultados coloreados

**Output esperado:**
```
✓ 01. Config (2369 bytes)
✓ 02. Database
✓ 03. Functions
✗ 06. ValorMetrica Model
   ❌ ERROR: syntax error, unexpected token "public"
   línea: 271
```

### 8.2. Test de Includes Aislado

**Archivo:** `test-includes.php`

Script independiente que prueba cada archivo sin depender del sistema.

```php
<?php
// Prueba cada archivo de forma aislada
// Captura errores sin detener ejecución
// Genera reporte HTML con colores
```

**Uso:**
```
http://tu-dominio/metricas-it/test-includes.php
```

### 8.3. Verificación de Sintaxis por Lote

```powershell
# Script PowerShell para validar todos los PHP

$files = Get-ChildItem -Path "C:\inetpub\wwwroot\metricas-it" -Recurse -Filter *.php

$errors = @()

foreach ($file in $files) {
    $result = php -l $file.FullName 2>&1
    if ($result -notmatch "No syntax errors detected") {
        $errors += [PSCustomObject]@{
            File = $file.Name
            Path = $file.FullName
            Error = $result
        }
    }
}

if ($errors.Count -eq 0) {
    Write-Host "✓ Todos los archivos PHP tienen sintaxis correcta" -ForegroundColor Green
} else {
    Write-Host "✗ Se encontraron errores en $($errors.Count) archivo(s):" -ForegroundColor Red
    $errors | Format-Table -AutoSize
}
```

### 8.4. Monitor de Logs en Tiempo Real

```powershell
# PowerShell: Tail de logs (como en Linux)
Get-Content "C:\inetpub\wwwroot\metricas-it\logs\php-errors.log" -Wait -Tail 50
```

**O usar herramienta gráfica:**
- **Baretail** (gratuito): https://www.baremetalsoft.com/baretail/
- **SnakeTail** (gratuito): https://snaketail.net/

### 8.5. Verificación de Salud del Sistema

**Archivo:** `health-check.php`

```php
<?php
// health-check.php
header('Content-Type: application/json');

$checks = [
    'php_version' => phpversion(),
    'db_connection' => false,
    'session_working' => false,
    'logs_writable' => false,
    'uploads_writable' => false
];

// Test DB
try {
    require_once 'config.php';
    $db = getDB();
    $checks['db_connection'] = true;
} catch (Exception $e) {
    $checks['db_error'] = $e->getMessage();
}

// Test Session
try {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $checks['session_working'] = true;
    }
} catch (Exception $e) {
    $checks['session_error'] = $e->getMessage();
}

// Test Write Permissions
$checks['logs_writable'] = is_writable(__DIR__ . '/logs');
$checks['uploads_writable'] = is_writable(__DIR__ . '/uploads');

echo json_encode($checks, JSON_PRETTY_PRINT);
```

**Uso:**
```
http://tu-dominio/metricas-it/health-check.php
```

**Para monitoreo automático:**
```powershell
# Llamar cada 5 minutos desde Task Scheduler
Invoke-RestMethod -Uri "http://localhost/metricas-it/health-check.php"
```

---

## 9. Mantenimiento Post-Despliegue

### 9.1. Monitoreo de Logs

**Crear tarea programada para revisar logs:**

```powershell
# Task Scheduler: Ejecutar diariamente
$logFile = "C:\inetpub\wwwroot\metricas-it\logs\php-errors.log"
$lines = Get-Content $logFile -Tail 100

$errors = $lines | Select-String -Pattern "Fatal error|Parse error|Warning"

if ($errors) {
    # Enviar email o alerta
    Send-MailMessage -To "admin@empresa.com" `
                     -Subject "Errores detectados en Métricas IT" `
                     -Body ($errors -join "`n")
}
```

### 9.2. Backups Automáticos

```powershell
# Script de backup diario

$source = "C:\inetpub\wwwroot\metricas-it"
$destination = "C:\backups\metricas-it-$(Get-Date -Format 'yyyyMMdd')"

# Backup de archivos
Copy-Item $source $destination -Recurse

# Backup de BD
mysqldump -u metricas_user -p metricas_it_prod > "$destination\database.sql"

# Comprimir
Compress-Archive -Path $destination -DestinationPath "$destination.zip"

# Eliminar backups mayores a 30 días
Get-ChildItem "C:\backups" -Filter "metricas-it-*.zip" | 
    Where-Object {$_.CreationTime -lt (Get-Date).AddDays(-30)} | 
    Remove-Item
```

### 9.3. Actualización del Sistema

```powershell
# Procedimiento de actualización

# 1. Crear backup
.\backup-sistema.ps1

# 2. Poner sitio en mantenimiento
Copy-Item "maintenance.html" "C:\inetpub\wwwroot\metricas-it\index.html"

# 3. Desplegar nueva versión
# ... subir archivos ...

# 4. Ejecutar migraciones
php artisan migrate  # O el sistema de migraciones que uses

# 5. Limpiar caché
php artisan cache:clear

# 6. Quitar mantenimiento
Remove-Item "C:\inetpub\wwwroot\metricas-it\index.html"

# 7. Verificar
Invoke-RestMethod -Uri "http://localhost/metricas-it/health-check.php"
```

---

## 10. Recursos Adicionales

### Documentación Oficial

- **IIS:** https://docs.microsoft.com/en-us/iis/
- **PHP en IIS:** https://docs.microsoft.com/en-us/iis/application-frameworks/scenario-build-a-php-website-on-iis/
- **MySQL:** https://dev.mysql.com/doc/

### Herramientas Recomendadas

- **VS Code:** Editor con soporte PHP
- **Baretail:** Visor de logs en tiempo real
- **MySQL Workbench:** Gestión de base de datos
- **FileZilla:** Cliente FTP/SFTP
- **Git for Windows:** Control de versiones

### Comunidad y Soporte

- **Stack Overflow:** Para errores específicos
- **PHP.net:** Documentación oficial de PHP
- **IIS Forums:** https://forums.iis.net/

---

## 11. Resumen Ejecutivo

### ✅ Checklist Rápido (5 minutos)

```
□ PHP instalado y configurado en IIS
□ MySQL/MariaDB funcionando
□ Sitio creado en IIS con Application Pool
□ Permisos configurados en carpetas
□ config.php con credenciales correctas
□ ENVIRONMENT = 'production'
□ Base de datos importada
□ Login funciona
□ Dashboard carga sin errores
```

### 🚨 Problemas Más Comunes y Soluciones Rápidas

| Error | Solución en 30 segundos |
|-------|-------------------------|
| Error 500 | Activar `ENVIRONMENT='development'` temporalmente, ver error |
| "Access denied" DB | Verificar usuario/password en config.php |
| Página en blanco | Revisar `logs/php-errors.log` |
| CSS no carga | Verificar `BASE_URL` en config.php |
| Session no persiste | Dar permisos a carpeta de sesiones |

### 📞 Cuando Todo Falla

1. **Restaurar backup** más reciente
2. **Revisar logs** en `C:\inetpub\wwwroot\metricas-it\logs\`
3. **Usar script de diagnóstico** (login-con-diagnostico.php)
4. **Validar sintaxis** de todos los PHP
5. **Contactar soporte** con logs completos

---

**Fecha de última actualización:** Abril 2026  
**Versión del documento:** 1.0  
**Autores:** Equipo de Desarrollo IT - CICRL

---

## Apéndice A: Troubleshooting Específico de Windows/IIS

### Error: "The FastCGI process exited unexpectedly"

```xml
<!-- web.config: Aumentar tiempo de espera -->
<system.webServer>
    <fastCgi>
        <application fullPath="C:\PHP\php-cgi.exe">
            <environmentVariables>
                <environmentVariable name="PHP_FCGI_MAX_REQUESTS" value="10000" />
            </environmentVariables>
        </application>
    </fastCgi>
</system.webServer>
```

### Error: "500.19 - Internal Server Error"

**Causa:** Falta módulo de URL Rewrite

**Solución:**
```powershell
# Instalar URL Rewrite Module
# Descargar de: https://www.iis.net/downloads/microsoft/url-rewrite
# O via Web Platform Installer
```

### Optimizaciones de Rendimiento

```xml
<!-- web.config: Habilitar compresión -->
<system.webServer>
    <urlCompression 
        doStaticCompression="true" 
        doDynamicCompression="true" />
    <httpCompression>
        <dynamicTypes>
            <add mimeType="text/*" enabled="true"/>
            <add mimeType="application/json" enabled="true"/>
        </dynamicTypes>
    </httpCompression>
</system.webServer>
```

```ini
; php.ini: Habilitar OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

---

**FIN DE LA GUÍA**

Esta guía fue creada basándose en la experiencia real de despliegue del sistema Dashboard de Métricas IT en AWS Windows Server con IIS, incluyendo todos los errores encontrados y sus soluciones.
