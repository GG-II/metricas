# 🚀 Instrucciones de Despliegue - Métricas IT

## 📦 Contenido del Paquete

```
metricas-it-deploy-YYYYMMDD-HHMMSS.zip
├── public/              # Raíz web (apunta IIS aquí)
├── src/                 # Código fuente PHP
├── includes/            # Funciones y DB
├── database/            # Schema SQL
├── vendor/              # Dependencias Composer
├── views/               # Plantillas
├── config.php           # Configuración (EDITAR)
├── logs/                # Logs (vacío)
├── storage/cache/       # Caché (vacío)
└── uploads/             # Archivos subidos (vacío)
```

---

## ⚡ Despliegue Rápido (5 minutos)

### **Paso 1: Descomprimir en el Servidor**

```powershell
# En el servidor Windows, descomprimir en:
C:\inetpub\wwwroot\metricas-it\
```

### **Paso 2: Ejecutar Script de Despliegue**

```batch
# Como Administrador, ejecutar:
cd C:\inetpub\wwwroot\metricas-it
.\deploy-iis.bat
```

**¿Qué hace el script?**
- ✅ Crea Application Pool "metricas-it-pool"
- ✅ Crea sitio IIS "metricas-it"
- ✅ Configura permisos de carpetas
- ✅ Configura handler de PHP
- ✅ Inicia el sitio

### **Paso 3: Configurar Base de Datos**

Editar `C:\inetpub\wwwroot\metricas-it\config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'metricas_it_prod');  // Cambiar nombre
define('DB_USER', 'metricas_user');     // Cambiar usuario
define('DB_PASS', 'TU_PASSWORD_AQUI');  // Cambiar password
```

### **Paso 4: Importar Base de Datos**

```powershell
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE metricas_it_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear usuario
CREATE USER 'metricas_user'@'localhost' IDENTIFIED BY 'TU_PASSWORD_AQUI';
GRANT ALL PRIVILEGES ON metricas_it_prod.* TO 'metricas_user'@'localhost';
FLUSH PRIVILEGES;
exit;

# Importar schema
cd C:\inetpub\wwwroot\metricas-it
mysql -u metricas_user -p metricas_it_prod < database\schema.sql
```

### **Paso 5: Verificar**

Abrir navegador:
```
http://siscoop.cicrl.com.gt/login.php
```

**Credenciales por defecto:**
- Usuario: `admin@sistema.com`
- Contraseña: `admin123`

**⚠️ CAMBIAR INMEDIATAMENTE**

---

## 🔧 Configuración Manual (si script falla)

### 1. Crear Application Pool

**IIS Manager → Application Pools → Add**
- Name: `metricas-it-pool`
- .NET CLR version: `No Managed Code`
- Pipeline mode: `Integrated`

**Advanced Settings:**
- Identity: `ApplicationPoolIdentity`
- Recycling: `29:00:00` (29 horas)

### 2. Crear Sitio Web

**IIS Manager → Sites → Add Website**
- Site name: `metricas-it`
- Application pool: `metricas-it-pool`
- Physical path: `C:\inetpub\wwwroot\metricas-it\public`
- Binding:
  - Type: `http`
  - Port: `80`
  - Host name: `siscoop.cicrl.com.gt`

### 3. Configurar Permisos

```powershell
# Permisos de lectura
icacls "C:\inetpub\wwwroot\metricas-it" /grant "IIS_IUSRS:(OI)(CI)RX" /T
icacls "C:\inetpub\wwwroot\metricas-it" /grant "IUSR:(OI)(CI)RX" /T

# Permisos de escritura
icacls "C:\inetpub\wwwroot\metricas-it\logs" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas-it\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas-it\uploads" /grant "IIS_IUSRS:(OI)(CI)F" /T
```

### 4. Configurar PHP Handler

```powershell
# FastCGI
C:\Windows\System32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='C:\PHP\php-cgi.exe']" /commit:apphost

# Handler
C:\Windows\System32\inetsrv\appcmd.exe set config -section:system.webServer/handlers /+"[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='C:\PHP\php-cgi.exe',resourceType='Either']"
```

---

## 🐛 Solución de Problemas

### Error 500 - Internal Server Error

**Activar errores temporalmente:**

Editar `config.php`:
```php
define('ENVIRONMENT', 'development'); // Temporal
```

Ver error en pantalla y corregir. **Luego volver a 'production'**.

### "Access Denied" a Base de Datos

Verificar credenciales en `config.php`:
```powershell
# Probar conexión
mysql -u metricas_user -p metricas_it_prod
```

### CSS/JS No Cargan

Verificar `BASE_URL` en `config.php`:
```php
define('BASE_URL', '/metricas-it'); // Sin slash final
```

### Sesiones No Persisten

Dar permisos a carpeta de sesiones:
```powershell
mkdir C:\inetpub\temp\sessions
icacls "C:\inetpub\temp\sessions" /grant "IIS_IUSRS:(OI)(CI)F" /T
```

Editar `C:\PHP\php.ini`:
```ini
session.save_path = "C:\inetpub\temp\sessions"
```

### Ver Logs

```powershell
# Ver últimas 50 líneas de error log
Get-Content "C:\inetpub\wwwroot\metricas-it\logs\php-errors.log" -Tail 50
```

---

## 📋 Checklist Post-Despliegue

- [ ] Sitio accesible en navegador
- [ ] Login funciona correctamente
- [ ] Dashboard carga sin errores
- [ ] CSS y JavaScript funcionan
- [ ] Imágenes y assets cargan
- [ ] Crear métrica funciona
- [ ] Registrar valor funciona
- [ ] Exportar funciona (CSV/PDF)
- [ ] API REST funciona (generar token)
- [ ] Cambiar password de admin
- [ ] `ENVIRONMENT = 'production'` en config.php
- [ ] Logs se escriben en `logs/php-errors.log`
- [ ] No hay errores en Event Viewer

---

## 🔐 Seguridad Post-Despliegue

### 1. Cambiar Contraseña de Admin

```sql
-- Conectar a MySQL
mysql -u metricas_user -p metricas_it_prod

-- Cambiar password (usar hash bcrypt)
UPDATE usuarios 
SET password = '$2y$10$NUEVO_HASH_AQUI' 
WHERE email = 'admin@sistema.com';
```

O desde la aplicación: Perfil → Cambiar Contraseña

### 2. Verificar Permisos de Archivos

```powershell
# Solo lectura en archivos de código
icacls "C:\inetpub\wwwroot\metricas-it\*.php" /reset
icacls "C:\inetpub\wwwroot\metricas-it\*.php" /grant "IIS_IUSRS:R" /T
```

### 3. Deshabilitar Listado de Directorios

En IIS Manager:
- Sitio → Directory Browsing → Disable

### 4. Configurar SSL/HTTPS (Recomendado)

**Obtener certificado:**
- Let's Encrypt (gratuito)
- Certificado corporativo

**Configurar binding:**
- IIS Manager → Site → Bindings
- Add: `https`, port `443`, certificado SSL

**Forzar HTTPS en web.config:**
```xml
<rule name="Redirect to HTTPS" stopProcessing="true">
    <match url="(.*)" />
    <conditions>
        <add input="{HTTPS}" pattern="off" />
    </conditions>
    <action type="Redirect" url="https://{HTTP_HOST}/{R:1}" />
</rule>
```

---

## 🔄 Actualización del Sistema

### Proceso de Actualización

```powershell
# 1. Backup
Copy-Item "C:\inetpub\wwwroot\metricas-it" `
          "C:\backups\metricas-it-$(Get-Date -Format 'yyyyMMdd-HHmmss')" `
          -Recurse

# 2. Backup de BD
mysqldump -u metricas_user -p metricas_it_prod > backup-db.sql

# 3. Detener sitio
C:\Windows\System32\inetsrv\appcmd.exe stop site "metricas-it"

# 4. Desplegar nueva versión
# ... copiar archivos nuevos ...

# 5. Ejecutar migraciones (si hay)
mysql -u metricas_user -p metricas_it_prod < database\migrations\nueva.sql

# 6. Iniciar sitio
C:\Windows\System32\inetsrv\appcmd.exe start site "metricas-it"
```

---

## 📞 Soporte

**Logs importantes:**
- PHP: `C:\inetpub\wwwroot\metricas-it\logs\php-errors.log`
- IIS: `C:\inetpub\logs\LogFiles\W3SVC*\`
- Windows: Event Viewer → Application

**Contacto:**
- Email: soporte@cicrl.com.gt
- Documentación: `/README.md`

---

## 📊 Información del Paquete

- **Versión:** 2.0
- **Fecha creación:** Ver nombre del ZIP
- **Requiere:**
  - Windows Server 2019/2022
  - IIS 10.0+
  - PHP 8.1+ (Non-Thread Safe)
  - MySQL 8.0+ / MariaDB 10.5+

---

**✅ ¡Despliegue Exitoso!**
