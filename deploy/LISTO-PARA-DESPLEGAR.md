# ✅ SISTEMA LISTO PARA DESPLIEGUE

**Fecha:** 21 de Abril 2026  
**Versión:** 2.0  
**Paquete:** `metricas-it-full-20260421-140802.zip` (2.89 MB)

---

## 📦 Contenido del Paquete

El archivo ZIP contiene el sistema completo:

```
metricas-it/
├── public/          ← Raíz web (IIS apunta aquí)
│   ├── index.php    ← Dashboard principal
│   ├── login.php    ← Página de acceso
│   ├── admin/       ← Panel de administración
│   ├── assets/      ← CSS, JS, imágenes
│   └── web.config   ← Configuración IIS
├── src/             ← Modelos, Servicios, Utils
├── api/             ← API REST endpoints
├── views/           ← Componentes de gráficos
├── database/        ← Schema y migraciones SQL
├── vendor/          ← Composer autoload
├── storage/cache/   ← Caché (vacío)
├── logs/            ← Logs PHP (vacío)
├── includes/        ← db.php, functions.php
├── config.php       ← ⚠️ CONFIGURACIÓN PRINCIPAL
├── config.example.php ← Template de configuración
└── README.md        ← Documentación completa
```

---

## 🚀 PASOS PARA DESPLEGAR (15 minutos)

### **PASO 1: Copiar Archivos al Servidor**

Desde tu máquina local, copiar al servidor Windows:

```
📁 deploy/metricas-it-full-20260421-140802.zip
📄 deploy/deploy-iis.bat
📄 deploy/DEPLOY-INSTRUCTIONS.txt
```

**Métodos de transferencia:**
- RDP (Escritorio Remoto)
- FTP/SFTP
- AWS Storage Gateway
- Copiar/Pegar directo si tienes acceso RDP

---

### **PASO 2: Descomprimir en el Servidor**

En el servidor Windows:

```powershell
# Crear carpeta destino
New-Item -ItemType Directory -Path "C:\inetpub\wwwroot\metricas-it" -Force

# Descomprimir
Expand-Archive -Path ".\metricas-it-full-20260421-140802.zip" `
               -DestinationPath "C:\inetpub\wwwroot\metricas-it" `
               -Force
```

---

### **PASO 3: Ejecutar Script de Despliegue Automático**

**Como Administrador:**

```batch
cd C:\inetpub\wwwroot\metricas-it
.\deploy-iis.bat
```

**El script automáticamente:**
- ✅ Crea Application Pool "metricas-it-pool"
- ✅ Crea Sitio IIS "metricas-it"
- ✅ Configura binding: http://siscoop.cicrl.com.gt
- ✅ Configura permisos de carpetas (IIS_IUSRS)
- ✅ Configura FastCGI y handler de PHP
- ✅ Inicia el sitio

**Duración:** ~30 segundos

---

### **PASO 4: Configurar Base de Datos**

#### A) Editar config.php

Abrir con Notepad++:
```
C:\inetpub\wwwroot\metricas-it\config.php
```

**Cambiar solo estas líneas:**
```php
define('DB_NAME', 'metricas_it_prod');  // ← Cambiar nombre de BD
define('DB_USER', 'metricas_user');     // ← Cambiar usuario
define('DB_PASS', 'TU_PASSWORD_AQUI');  // ← ⚠️ PASSWORD SEGURO
```

**Dejar el resto igual** (ENVIRONMENT, BASE_URL, etc.)

#### B) Crear Base de Datos

```sql
-- Conectar a MySQL como root
mysql -u root -p

-- Ejecutar:
CREATE DATABASE metricas_it_prod 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'metricas_user'@'localhost' 
  IDENTIFIED BY 'PASSWORD_SEGURO_AQUI';

GRANT ALL PRIVILEGES ON metricas_it_prod.* 
  TO 'metricas_user'@'localhost';

FLUSH PRIVILEGES;
exit;
```

#### C) Importar Schema

```powershell
cd C:\inetpub\wwwroot\metricas-it
mysql -u metricas_user -p metricas_it_prod < database\schema.sql
```

---

### **PASO 5: Verificar Funcionamiento**

Abrir navegador:

```
http://siscoop.cicrl.com.gt/login.php
```

**Login por defecto:**
- Usuario: `admin@sistema.com`
- Contraseña: `admin123`

**⚠️ CAMBIAR INMEDIATAMENTE** desde Perfil → Cambiar Contraseña

---

### **PASO 6: Checklist Post-Despliegue**

Verificar que funcione:

- [ ] **Login exitoso** - Puedo iniciar sesión
- [ ] **Dashboard carga** - Veo el dashboard sin errores
- [ ] **CSS aplicado** - La página se ve correctamente
- [ ] **JavaScript funciona** - Los gráficos se muestran
- [ ] **Crear métrica** - Puedo crear una métrica nueva
- [ ] **Registrar valor** - Puedo registrar un valor
- [ ] **Exportar CSV** - Puedo exportar datos a Excel
- [ ] **Exportar PDF** - Puedo generar reporte imprimible
- [ ] **API funciona** - Puedo generar token API

---

## 🔐 SEGURIDAD CRÍTICA

### Después del primer login:

1. **Cambiar password de admin**
   - Ir a: Perfil → Cambiar Contraseña
   - Usar mínimo 16 caracteres alfanuméricos + símbolos

2. **Verificar config.php**
   ```php
   define('ENVIRONMENT', 'production'); // ← DEBE ser 'production'
   ```

3. **Verificar php.ini**
   ```ini
   display_errors = Off  ; ← DEBE estar Off
   log_errors = On       ; ← DEBE estar On
   ```

4. **Revisar logs**
   ```
   C:\inetpub\wwwroot\metricas-it\logs\php-errors.log
   ```
   No debe haber errores críticos

---

## 🐛 SOLUCIÓN RÁPIDA DE PROBLEMAS

### Error 500 al acceder

**Activar errores temporalmente:**

Editar `config.php`:
```php
define('ENVIRONMENT', 'development'); // Temporal
```

Recargar página, ver error, corregir.  
**Volver a 'production' después.**

---

### "Access Denied" Base de Datos

**Verificar credenciales:**
```powershell
# Probar conexión
mysql -u metricas_user -p metricas_it_prod

# Si falla, recrear usuario
mysql -u root -p

mysql> DROP USER 'metricas_user'@'localhost';
mysql> CREATE USER 'metricas_user'@'localhost' IDENTIFIED BY 'NUEVO_PASS';
mysql> GRANT ALL PRIVILEGES ON metricas_it_prod.* TO 'metricas_user'@'localhost';
mysql> FLUSH PRIVILEGES;
```

---

### CSS/JS No Cargan

**Verificar BASE_URL:**

En `config.php` debe ser:
```php
define('BASE_URL', '/metricas-it'); // Sin slash final
```

Si está en raíz del dominio:
```php
define('BASE_URL', ''); // Vacío
```

---

### Sesiones No Persisten

**Dar permisos a carpeta de sesiones:**

```powershell
mkdir C:\inetpub\temp\sessions
icacls "C:\inetpub\temp\sessions" /grant "IIS_IUSRS:(OI)(CI)F" /T
```

Editar `C:\PHP\php.ini`:
```ini
session.save_path = "C:\inetpub\temp\sessions"
```

Reiniciar IIS:
```powershell
iisreset
```

---

## 📞 SOPORTE

**Documentación completa:**
- README.md (en el paquete)
- GUIA-DESPLIEGUE-AWS-IIS.md
- docs/API_REFERENCE.md
- docs/QUICKSTART.md

**Logs:**
- PHP: `C:\inetpub\wwwroot\metricas-it\logs\php-errors.log`
- IIS: `C:\inetpub\logs\LogFiles\W3SVC*\`
- Windows Event Viewer: Application

**Contacto:**
- Email: soporte@cicrl.com.gt

---

## ✅ RESUMEN EJECUTIVO

**Sistema:** Dashboard de Métricas IT v2.0  
**Estado:** ✅ LISTO PARA DESPLEGAR  
**Tiempo estimado:** 15 minutos  
**Complejidad:** Baja (script automatizado)

**Incluye:**
- ✅ 21 tipos de gráficos
- ✅ API REST completa
- ✅ Exportación PDF/Excel
- ✅ Sistema de caché
- ✅ Control de acceso por roles
- ✅ Gestión de metas y KPIs
- ✅ Documentación completa

**Próximo paso:** Copiar ZIP al servidor y ejecutar deploy-iis.bat

---

**Fecha de preparación:** 2026-04-21 14:08  
**Preparado por:** Claude Code Assistant  
**Proyecto:** Sistema de Métricas IT - CICRL
