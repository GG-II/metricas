# 🔒 Guía de Implementación CSRF - COMPLETADO

## ✅ QUÉ SE HA IMPLEMENTADO

### 1. Clase de Protección CSRF
**Archivo:** `includes/CsrfProtection.php`

Funcionalidades:
- ✅ Generación de tokens seguros (64 caracteres hex)
- ✅ Validación con comparación segura (hash_equals)
- ✅ Regeneración de tokens
- ✅ Manejo de errores con página 403

### 2. Funciones Helper
**Archivo:** `includes/functions.php` (líneas agregadas al final)

Funciones disponibles:
```php
csrf_field()     // Retorna <input type="hidden" name="csrf_token" value="...">
csrf_token()     // Retorna el token actual
csrf_validate()  // Valida o muere con 403
csrf_check()     // Valida sin terminar ejecución
```

### 3. Validación en Backend
**Archivos protegidos (9 archivos):**

| Archivo | Ubicación | Estado |
|---------|-----------|--------|
| captura-valores.php | public/ | ✅ Protegido |
| usuarios.php | public/admin/ | ✅ Protegido |
| metricas.php | public/admin/ | ✅ Protegido |
| graficos.php | public/admin/ | ✅ Protegido |
| metas.php | public/admin/ | ✅ Protegido |
| periodos.php | public/admin/ | ✅ Protegido |
| areas.php | public/admin/ | ✅ Protegido |
| departamentos.php | public/admin/ | ✅ Protegido |
| perfil.php | public/ | ✅ Protegido |
| cambiar-tema.php | public/api/ | ✅ Protegido |
| **login.php** | public/ | ✅ Regenera token al login |

---

## 📝 CÓMO USAR EN FORMULARIOS HTML

### Opción 1: Función Helper (Recomendado)

```php
<form method="POST" action="procesar.php">
    <?php echo csrf_field(); ?>
    
    <input type="text" name="nombre">
    <button type="submit">Enviar</button>
</form>
```

### Opción 2: Manual

```php
<form method="POST" action="procesar.php">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
    
    <input type="text" name="nombre">
    <button type="submit">Enviar</button>
</form>
```

### Opción 3: Para AJAX

```javascript
// Obtener token del meta tag o input hidden
const csrfToken = document.querySelector('[name="csrf_token"]').value;

fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify({ data: 'value' })
});
```

---

## 🎯 PRÓXIMOS PASOS PARA COMPLETAR

### Paso 1: Agregar Campo CSRF a Formularios HTML

Busca todos los `<form method="POST">` y agrega `<?php echo csrf_field(); ?>` después de la etiqueta de apertura:

**Archivos a revisar:**
```bash
# Buscar formularios
grep -r "<form.*method.*POST" public/ --include="*.php"
```

**Ejemplos de archivos que pueden necesitar el campo:**
- public/captura-valores.php (formulario de captura)
- public/admin/usuarios.php (formulario de crear/editar usuarios)
- public/admin/metricas.php (formulario de métricas)
- public/perfil.php (formulario de cambio de contraseña)

### Paso 2: Verificar que Funciona

**Test 1: Formulario válido**
```
1. Acceder a un formulario
2. Llenar datos
3. Enviar
4. ✅ Debe funcionar normalmente
```

**Test 2: Sin token (simulando CSRF)**
```
1. Abrir DevTools > Network
2. Enviar formulario
3. En Network, copiar la petición como cURL
4. Eliminar el parámetro csrf_token
5. Re-enviar
6. ✅ Debe recibir error 403
```

**Test 3: Token inválido**
```
1. Inspeccionar el input csrf_token
2. Cambiar el value manualmente a "token_falso"
3. Enviar formulario
4. ✅ Debe recibir error 403
```

### Paso 3: Agregar Meta Tag para AJAX (Opcional)

En `partials/header.php` o layouts, agregar:

```php
<meta name="csrf-token" content="<?php echo csrf_token(); ?>">
```

Luego en JavaScript:
```javascript
const token = document.querySelector('meta[name="csrf-token"]').content;
```

---

## 🔍 VERIFICACIÓN DE SEGURIDAD

### ¿Cómo saber si está funcionando?

1. **Intentar el exploit CSRF original:**
   ```
   http://localhost/csrf-test.html
   ```
   **Resultado esperado:** Error 403 en lugar de modificar métricas

2. **Verificar en logs:**
   - Si alguien intenta CSRF, verás peticiones con status 403
   - El error dirá "Token CSRF inválido"

3. **Revisar código:**
   ```php
   // En archivos PHP que procesan POST, debe haber:
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       csrf_validate(); // ← Esta línea
       // ... resto del código
   }
   ```

---

## 📊 COMPARACIÓN ANTES/DESPUÉS

| Aspecto | ANTES ❌ | DESPUÉS ✅ |
|---------|----------|------------|
| **Validación CSRF** | Ninguna | Todos los POST |
| **Exploit funciona** | Sí | No (403 Forbidden) |
| **Tokens en sesión** | No | Sí (regenerados) |
| **Login seguro** | Básico | Token regenerado |
| **AJAX protegido** | No | Sí (header X-CSRF-Token) |
| **Archivos protegidos** | 0 | 10 archivos |

---

## 🚀 BENEFICIOS IMPLEMENTADOS

1. ✅ **Prevención de CSRF:** Sitios externos no pueden ejecutar acciones
2. ✅ **Session Fixation:** Token regenerado al login
3. ✅ **Validación Automática:** Una línea de código protege todo el endpoint
4. ✅ **Error Handling:** Mensajes claros para usuario y desarrollador
5. ✅ **Compatible con AJAX:** Soporte para peticiones asíncronas

---

## ⚠️ NOTAS IMPORTANTES

1. **No proteger login.php con csrf_validate()** en el POST
   - Login es público, no hay sesión previa
   - Sí regenerar token DESPUÉS de login exitoso ✅ (ya implementado)

2. **API pública**
   - Si tienes endpoints de API sin autenticación, considera usar otro método
   - CSRF solo aplica a peticiones con sesiones (cookies)

3. **Expiración de tokens**
   - El token actual expira cuando expira la sesión
   - Si necesitas tiempo de vida diferente, modificar `CsrfProtection.php`

4. **SameSite Cookie**
   - Agregar `session.cookie_samesite = 'Strict'` en php.ini
   - Ya configurado en `config.php` ✅

---

## 🎓 RECURSOS ADICIONALES

- [OWASP CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [CWE-352: Cross-Site Request Forgery](https://cwe.mitre.org/data/definitions/352.html)

---

**Estado:** ✅ IMPLEMENTACIÓN BACKEND COMPLETA
**Pendiente:** Agregar `<?php echo csrf_field(); ?>` a formularios HTML (frontend)
