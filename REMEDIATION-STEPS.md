# 🔒 PASOS DE REMEDIACIÓN - VULNERABILIDAD #1

## ✅ COMPLETADO HASTA AHORA:

1. ✅ Creado `.env.example` (template)
2. ✅ Actualizado `composer.json` (agregado vlucas/phpdotenv)
3. ✅ Actualizado `config.example.php` (usa variables de entorno)
4. ✅ Actualizado `config.php` (usa variables de entorno)
5. ✅ Creado `.env` con credenciales actuales

## 📋 PASOS PENDIENTES (EJECUTA EN ORDEN):

### PASO 1: Instalar Dependencias de Composer

```powershell
cd C:\xampp\htdocs\metricas
composer install
```

**Espera:** "Generating autoload files" y "Package vlucas/phpdotenv installed"

---

### PASO 2: Establecer Contraseña de MySQL

```powershell
# Conectar a MySQL sin password (como está ahora)
cd C:\xampp\mysql\bin
.\mysql.exe -u root

# Dentro de MySQL, ejecutar:
```

```sql
-- Establecer nueva contraseña segura
ALTER USER 'root'@'localhost' IDENTIFIED BY 'M3tr1c@s!2026$eGur0';
FLUSH PRIVILEGES;

-- Verificar que se aplicó
SELECT user, host FROM mysql.user WHERE user = 'root';

-- Salir
EXIT;
```

---

### PASO 3: Actualizar .env con la Nueva Contraseña

Editar el archivo `.env` y cambiar la línea:

```env
DB_PASS=M3tr1c@s!2026$eGur0
```

---

### PASO 4: Remover config.php del Tracking de Git

```bash
# Remover del índice pero mantener archivo local
git rm --cached config.php

# Verificar que ya no está trackeado
git status

# Debe aparecer:
# deleted: config.php (staged)
# Untracked files: config.php
```

---

### PASO 5: Verificar que el Sistema Funciona

```powershell
# Probar conexión con nueva contraseña
cd C:\xampp\mysql\bin
.\mysql.exe -u root -p"M3tr1c@s!2026$eGur0" metricas_it_dev -e "SELECT COUNT(*) FROM usuarios;"
```

**Debe mostrar:** El conteo de usuarios (éxito)

**Luego abre en navegador:**
```
http://localhost/metricas-it/
```

**Debe cargar:** La página de login sin errores

---

### PASO 6: Probar que el Ataque YA NO FUNCIONA

```powershell
# Intentar conectar SIN contraseña (como antes)
cd C:\xampp\mysql\bin
.\mysql.exe -u root metricas_it_dev -e "SHOW TABLES;"
```

**Debe fallar con:**
```
ERROR 1045 (28000): Access denied for user 'root'@'localhost' (using password: NO)
```

✅ **SI FALLA = VULNERABILIDAD CORREGIDA**

---

### PASO 7: Commit de Cambios Seguros

```bash
# Ver cambios
git status

# Agregar archivos seguros (NO config.php, NO .env)
git add .env.example
git add config.example.php
git add composer.json
git add .gitignore

# Commit
git commit -m "Security: Migrate credentials to environment variables

- Add vlucas/phpdotenv dependency
- Update config.php to load from .env
- Remove sensitive credentials from code
- Add .env.example template
- config.php removed from Git tracking

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```

---

### PASO 8: Verificar que .env y config.php NO están en Git

```bash
# Estos comandos NO deben mostrar nada:
git ls-files | grep "^config.php$"
git ls-files | grep "^\.env$"

# Solo debe aparecer:
git ls-files | grep config
# config.example.php
# config/ (directorio)
```

---

## 🎯 PRUEBA FINAL: RE-INTENTAR EXPLOTACIÓN

### Escenario 1: Clonar Repositorio Fresco
```bash
cd C:\temp
git clone C:\xampp\htdocs\metricas metricas-test

# Ver archivos:
ls metricas-test/

# ¿Existe config.php? → NO ✅
# ¿Existe .env? → NO ✅
# ¿Existe .env.example? → SÍ ✅
# ¿Existe config.example.php? → SÍ ✅
```

**Resultado esperado:**
- ❌ NO hay credenciales en el repositorio
- ✅ Hay instrucciones para configurar

### Escenario 2: Intentar Leer Credenciales del Código
```bash
# Buscar passwords en todo el repo
grep -r "DB_PASS" --include="*.php" C:\xampp\htdocs\metricas

# Debe mostrar SOLO:
# config.example.php: define('DB_PASS', $_ENV['DB_PASS']);
# NO debe mostrar la contraseña real
```

### Escenario 3: MySQL sin Autenticación
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root metricas_it_dev
```

**Debe fallar:**
```
ERROR 1045 (28000): Access denied
```

---

## 📊 COMPARACIÓN ANTES/DESPUÉS

| Aspecto | ANTES ❌ | DESPUÉS ✅ |
|---------|----------|------------|
| Credenciales en config.php | En texto plano | Desde .env |
| config.php en Git | Trackeado | Ignorado (.gitignore) |
| .env en Git | N/A | Ignorado (.gitignore) |
| Contraseña MySQL root | Vacía | M3tr1c@s!2026$eGur0 |
| Acceso sin password | Permitido | DENEGADO |
| Credenciales en repo | Parcial (antes vacío) | NINGUNA |
| Template disponible | .example.php | .example.php + .env.example |

---

## 🔐 MEJORAS ADICIONALES IMPLEMENTADAS

1. **Validación de Variables**: `$dotenv->required()` asegura que existan
2. **Configuración de Sesión Segura**: 
   - `cookie_httponly => true` (previene XSS)
   - `cookie_samesite => 'Strict'` (previene CSRF)
3. **Separación de Entornos**: dev/staging/prod en .env
4. **Template Documentation**: Instrucciones claras en .env.example

---

## ⚠️ RECORDATORIOS IMPORTANTES

1. **NUNCA** ejecutar `git add .env`
2. **NUNCA** ejecutar `git add config.php` (con credenciales)
3. **SIEMPRE** verificar `git status` antes de commit
4. **Rotar contraseñas** si este código estuvo en repo público
5. **Backups** de la nueva contraseña en lugar seguro

---

## 🚀 SIGUIENTES VULNERABILIDADES

Una vez completada esta remediación, continuaremos con:
- ✅ Vulnerabilidad #1: Credenciales (EN PROCESO)
- ⏳ Vulnerabilidad #2: CSRF en formularios
- ⏳ Vulnerabilidad #3: SQL Injection en cache-manager.php
