# Solución Error 403 en IIS - Sistema de Métricas

## Problema Identificado
El sitio está configurado incorrectamente en IIS. La ruta física apunta a la raíz del proyecto en lugar de la carpeta `public`.

---

## Solución Paso a Paso

### 1. Copiar archivos al servidor IIS

```powershell
# Copiar desde XAMPP a IIS
xcopy "C:\xampp\htdocs\metricas" "C:\inetpub\wwwroot\metricas" /E /I /Y
```

### 2. Configurar el sitio en IIS

#### Opción A: Cambiar Document Root (RECOMENDADO)

1. Abrir **Internet Information Services (IIS) Manager**
2. Expandir **Sitios** → Click en **metricas**
3. En el panel derecho, click en **Configuración básica...**
4. Cambiar **Ruta de acceso física** de:
   ```
   C:\inetpub\wwwroot\metricas
   ```
   a:
   ```
   C:\inetpub\wwwroot\metricas\public
   ```
5. Click **Aceptar**

#### Opción B: Crear web.config en raíz (ALTERNATIVA)

Si no puedes cambiar el document root, crea este archivo en `C:\inetpub\wwwroot\metricas\web.config`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Redirect to public folder" stopProcessing="true">
                    <match url="^(.*)$" />
                    <action type="Rewrite" url="public/{R:1}" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>
```

### 3. Configurar PHP Handler

El archivo `web.config` en `public/` tiene configurado `C:\PHP\php-cgi.exe`. 

**Verificar ruta de PHP:**

```powershell
# Buscar donde está instalado PHP
where php
# o buscar php-cgi.exe manualmente
```

**Ubicaciones comunes:**
- `C:\PHP\php-cgi.exe`
- `C:\Program Files\PHP\php-cgi.exe`
- `C:\xampp\php\php-cgi.exe`

**Si PHP no está instalado en IIS:**

1. Ir a **IIS Manager** → seleccionar servidor
2. Doble click en **Asignaciones de controlador**
3. Click derecho → **Agregar asignación de script...**
   - **Ruta de acceso de solicitud:** `*.php`
   - **Ejecutable:** `C:\PHP\php-cgi.exe` (o tu ruta)
   - **Nombre:** `PHP_via_FastCGI`
   - Click **Aceptar**

**O instalar PHP usando Web Platform Installer:**
1. Descargar e instalar [Microsoft Web Platform Installer](https://www.microsoft.com/web/downloads/platform.aspx)
2. Buscar "PHP" e instalar la versión recomendada (7.4 o superior)

### 4. Configurar Permisos NTFS

```powershell
# Dar permisos al usuario de IIS
icacls "C:\inetpub\wwwroot\metricas" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas" /grant "IUSR:(OI)(CI)R" /T

# Permisos especiales para carpetas de escritura
icacls "C:\inetpub\wwwroot\metricas\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas\logs" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\metricas\public\uploads" /grant "IIS_IUSRS:(OI)(CI)F" /T
```

### 5. Configurar Autenticación

1. En **IIS Manager** → sitio **metricas**
2. Doble click en **Autenticación**
3. **Autenticación anónima** → debe estar **Habilitada**
4. **Autenticación de Windows** → debe estar **Deshabilitada**

### 6. Configurar Base de Datos

Editar `C:\inetpub\wwwroot\metricas\.env`:

```env
DB_HOST=localhost
DB_NAME=metricas_sistema
DB_USER=tu_usuario_mysql
DB_PASS=tu_password_mysql
DB_PORT=3306

APP_URL=http://siscoop.cicrl.com.gt/metricas
APP_ENV=production
```

### 7. Verificar Módulos IIS Requeridos

En **IIS Manager** → servidor → **Módulos**, verificar que estén instalados:
- ✅ **RewriteModule** (URL Rewrite)
- ✅ **FastCgiModule**

**Si falta URL Rewrite:**
1. Descargar [URL Rewrite Module](https://www.iis.net/downloads/microsoft/url-rewrite)
2. Instalar y reiniciar IIS

### 8. Reiniciar IIS

```powershell
# Reiniciar IIS
iisreset

# O detener/iniciar el sitio específico
Stop-WebSite -Name "metricas"
Start-WebSite -Name "metricas"
```

### 9. Verificar Configuración

Acceder a: `http://siscoop.cicrl.com.gt/metricas/`

Debería redirigir a `login.php`

---

## Troubleshooting

### Error: "Handler PHP_via_FastCGI has a bad module FastCGIModule"

**Solución:** Instalar FastCGI para IIS:
```powershell
# En PowerShell como administrador
Install-WindowsFeature Web-CGI
```

### Error: HTTP 500 Internal Server Error

1. Habilitar errores detallados editando `public/web.config`:
   ```xml
   <httpErrors errorMode="Detailed" existingResponse="PassThrough" />
   ```
2. Ver logs en: `C:\inetpub\logs\LogFiles\`

### Error: "Could not connect to database"

1. Verificar que MySQL esté corriendo
2. Verificar credenciales en `.env`
3. Verificar que la base de datos existe:
   ```sql
   CREATE DATABASE IF NOT EXISTS metricas_sistema;
   ```

### La página carga pero sin estilos (CSS/JS no cargan)

Verificar MIME types en `web.config` (ya están incluidos en el archivo `public/web.config`)

---

## Resumen de Cambios Críticos

| Acción | Antes | Después |
|--------|-------|---------|
| Document Root | `C:\inetpub\wwwroot\metricas` | `C:\inetpub\wwwroot\metricas\public` |
| Autenticación | Windows | Anónima |
| Permisos NTFS | No configurados | IIS_IUSRS con permisos de lectura/escritura |
| PHP Handler | No configurado | FastCGI apuntando a php-cgi.exe |

---

## Comandos Rápidos de Verificación

```powershell
# Ver configuración del sitio
Get-WebSite -Name "metricas" | Select-Object Name, State, PhysicalPath, Bindings

# Ver permisos
icacls "C:\inetpub\wwwroot\metricas\public"

# Ver logs recientes de IIS
Get-Content "C:\inetpub\logs\LogFiles\W3SVC*\*.log" -Tail 20

# Probar PHP
php -v
```

---

## Contacto y Soporte

Si después de estos pasos aún hay problemas:
1. Revisar logs de IIS
2. Revisar logs de PHP: `C:\xampp\htdocs\metricas\logs\`
3. Verificar logs de base de datos MySQL
