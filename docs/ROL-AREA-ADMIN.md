# Rol area_admin - Administrador de Área

**Versión:** 2.2  
**Fecha:** 2026-04-28  
**Estado:** ✅ Implementado y Probado

---

## 📋 Descripción

Se ha implementado un nuevo rol **`area_admin`** que permite asignar usuarios a un área específica para que solo puedan ver y editar métricas de esa área en particular.

### Diferencia con otros roles

| Característica | super_admin | dept_admin | **area_admin** | dept_viewer |
|----------------|-------------|------------|----------------|-------------|
| **Alcance** | Todo el sistema | Todo su departamento | **Solo su área** | Todo su departamento |
| **Navegar entre áreas** | ✅ Sí | ✅ Sí | **❌ No** | ✅ Sí |
| **Ver su área** | ✅ | ✅ | **✅** | ✅ |
| **Editar métricas** | ✅ | ✅ Su dept | **✅ Solo su área** | ❌ |
| **Ingresar valores** | ✅ | ✅ | **✅** | ❌ |
| **Crear gráficos** | ✅ | ✅ Su dept | **✅ Solo su área** | ❌ |
| **Gestionar usuarios** | ✅ | ❌ | **❌** | ❌ |

---

## 🎯 Caso de Uso

**Escenario:** 
- Agencia San José tiene un Gerente de Créditos que solo debe gestionar el área de Créditos
- No debe ver ni editar áreas de Caja, Cuentas Nuevas, ni Atención al Cliente
- Solo debe tener acceso al dashboard de Créditos

**Solución:**
- Crear usuario con rol `area_admin`
- Asignar departamento: Agencia San José
- Asignar área: Créditos
- El usuario queda restringido solo a esa área

---

## 🔧 Implementación Técnica

### 1. Base de Datos

**Migración:** `012_add_area_admin_role.sql`

```sql
ALTER TABLE usuarios
MODIFY COLUMN rol ENUM(
    'super_admin',
    'dept_admin',
    'area_admin',  -- NUEVO
    'dept_viewer'
);
```

**Nota:** El campo `area_id` ya existía en la tabla usuarios, por lo que no fue necesario crearlo.

### 2. Modelo Usuario

**Archivo:** `src/Models/Usuario.php`

**Nuevos métodos:**

- `validateAreaAdmin($data)` - Valida que area_admin tenga area_id
- `canAccessArea($user, $area_id)` - Verifica acceso a un área
- `canEditArea($user, $area_id)` - Verifica permiso de edición
- `getAccessibleAreas($user)` - Retorna áreas accesibles
- `canAccessGlobal($user)` - Verifica acceso a áreas globales

**Lógica de permisos:**

```php
// area_admin solo puede acceder a su área asignada
if ($user['rol'] === 'area_admin') {
    return $area_id == $user['area_id'];
}
```

### 3. PermissionService

**Archivo:** `src/Services/PermissionService.php`

**Métodos actualizados:**

- `canViewArea()` - Agregado soporte para area_admin
- `canEditArea()` - Agregado soporte para area_admin  
- `getAreasPermitidas()` - Retorna solo área asignada para area_admin
- `canCreateInArea()` - **NUEVO** - Valida permisos de creación por área

**Lógica:**

```php
// Para area_admin
if ($user['rol'] === 'area_admin' && $user['area_id']) {
    $area = $areaModel->find($user['area_id']);
    return $area ? [$area] : [];
}
```

### 4. Controlador Principal

**Archivo:** `public/index.php`

**Cambios:**

1. Detección de area_admin y redirección automática a su área:

```php
if ($user['rol'] === 'area_admin') {
    if (empty($user['area_id'])) {
        die('Tu usuario no tiene un área asignada.');
    }
    
    // Redirigir si intenta acceder a otra área
    if (!$area_param || (int)$area_param !== (int)$user['area_id']) {
        header('Location: ?area=' . $user['area_id'] . '&periodo=' . $periodo_param);
        exit;
    }
}
```

2. Actualización de `$is_admin` para incluir area_admin:

```php
$is_admin = in_array($user['rol'], ['super_admin', 'dept_admin', 'area_admin']);
```

### 5. Administración de Usuarios

**Archivo:** `public/admin/usuarios.php`

**Cambios:**

1. Agregado rol en selector:
   - Super Admin (Acceso global)
   - Admin de Departamento
   - **Admin de Área** ← NUEVO
   - Visualizador (solo lectura)

2. Lógica JavaScript actualizada:
   - Muestra selector de departamento y área para area_admin
   - Ambos son requeridos para area_admin
   - Hints dinámicos según rol seleccionado

3. Validación servidor:
   - Valida que area_admin tenga area_id antes de crear/editar

4. Badge en tabla:
   - Color morado para area_admin
   - Texto: "Admin de Área"

---

## 📊 Datos de Prueba

**Usuario de prueba creado:**

- **Username:** `area_admin_test`
- **Password:** `password`
- **Rol:** `area_admin`
- **Departamento:** Agencia San José
- **Área asignada:** Créditos

**Seed:** `013_seed_area_admin_test_user.sql`

---

## 🧪 Tests

**Archivo:** `tests/AreaAdminRoleTest.php`

**7 tests implementados:**

1. ✅ `testUsuarioAreaAdminExiste` - Usuario creado correctamente
2. ✅ `testValidacionAreaAdmin` - Validación funciona
3. ✅ `testCanAccessArea` - Puede acceder a su área
4. ✅ `testCanEditArea` - Puede editar su área
5. ✅ `testCanNotAccessOtherAreas` - NO puede acceder a otras áreas
6. ✅ `testGetAccessibleAreas` - Solo ve su área
7. ✅ `testPermissionService` - PermissionService funciona

**Resultado:** 7/7 tests pasando ✅

**Ejecutar tests:**
```bash
php tests/AreaAdminRoleTest.php
```

---

## 🚀 Cómo Usar

### Crear Usuario area_admin

1. **Login como super_admin**

2. **Ir a Admin > Usuarios**

3. **Click en "Nuevo Usuario"**

4. **Completar formulario:**
   - Nombre: Juan Pérez
   - Username: jperez
   - Email: jperez@cooperativa.com
   - Password: *******
   - **Rol:** Admin de Área
   - **Departamento:** Agencia San José (requerido)
   - **Área:** Créditos (requerido)

5. **Guardar**

### Comportamiento del Usuario

**Al iniciar sesión:**
1. Es redirigido automáticamente a su área asignada
2. No ve selector de áreas en la navegación
3. Solo ve su área en el menú

**Dashboard:**
- Solo puede ver dashboard de su área
- Puede crear/editar/eliminar gráficos de su área
- Botón "Modo Edición" disponible

**Métricas:**
- Puede ver todas las métricas de su área
- Puede ingresar valores
- Puede definir metas
- NO puede crear métricas calculadas globales

**Restricciones:**
- NO puede navegar a otras áreas
- NO puede ver áreas globales
- NO puede acceder a administración de usuarios
- Si intenta acceder manualmente a otra área → error 403

---

## 📝 Validaciones Implementadas

### Validación Cliente (JavaScript)

**Archivo:** `public/admin/usuarios.php`

```javascript
// Al seleccionar rol area_admin:
- departamento_id: required
- area_id: required
- Muestra selector de área
- Hints actualizados
```

### Validación Servidor (PHP)

**Archivo:** `public/admin/usuarios.php`

```php
// Al crear/editar usuario
$validation = $usuarioModel->validateAreaAdmin($data);
if (!$validation['valid']) {
    setFlash('error', $validation['error']);
    redirect('/public/admin/usuarios.php');
    break;
}
```

**Error mostrado:**
> "El rol area_admin requiere un área asignada"

### Validación en Runtime

**Archivo:** `public/index.php`

```php
if ($user['rol'] === 'area_admin') {
    if (empty($user['area_id'])) {
        die('Tu usuario no tiene un área asignada. Contacta al administrador.');
    }
}
```

---

## 🔒 Seguridad

### Restricciones por Rol

1. **Acceso a Áreas:**
   - Validado en `PermissionService::canViewArea()`
   - Verificado en cada página que muestra datos de área

2. **Edición de Métricas:**
   - Validado en `PermissionService::canEditArea()`
   - Verificado antes de permitir modificaciones

3. **Creación de Recursos:**
   - Validado en `PermissionService::canCreateInArea()`
   - Verificado en formularios de creación

4. **Navegación:**
   - Redirección forzada a área asignada
   - No se muestra navegación de áreas
   - URLs directas bloqueadas

### Matriz de Permisos

| Acción | super_admin | dept_admin | area_admin | dept_viewer |
|--------|-------------|------------|------------|-------------|
| Ver área asignada | ✅ | ✅ | ✅ | ✅ |
| Ver otras áreas del dept | ✅ | ✅ | ❌ | ✅ |
| Ver áreas de otros dept | ✅ | ❌ | ❌ | ❌ |
| Ver áreas globales | ✅ | ❌ | ❌ | ❌ |
| Editar métricas área | ✅ | ✅ | ✅ (solo suya) | ❌ |
| Crear gráficos área | ✅ | ✅ | ✅ (solo suya) | ❌ |
| Ingresar valores | ✅ | ✅ | ✅ | ❌ |
| Gestionar usuarios | ✅ | ❌ | ❌ | ❌ |

---

## 🐛 Troubleshooting

### Problema: Usuario area_admin ve mensaje "No área asignada"

**Causa:** El usuario no tiene area_id en la base de datos

**Solución:**
1. Verificar en Admin > Usuarios que el área esté asignada
2. Editar usuario y seleccionar área
3. Guardar cambios

### Problema: No puedo seleccionar área al crear usuario

**Causa:** No has seleccionado departamento primero

**Solución:**
1. Seleccionar departamento
2. Esperar que carguen las áreas del departamento
3. Seleccionar área

### Problema: area_admin puede ver otras áreas

**Causa:** Error en lógica de permisos

**Solución:**
1. Verificar que el rol sea exactamente `'area_admin'`
2. Verificar que area_id esté asignado
3. Ejecutar tests: `php tests/AreaAdminRoleTest.php`

### Problema: Formulario no valida área requerida

**Causa:** JavaScript no se está ejecutando

**Solución:**
1. Verificar consola del navegador
2. Verificar que tabler.js esté cargado
3. Refrescar página

---

## 📋 Checklist de Implementación

- [x] Migración de base de datos ejecutada
- [x] Modelo Usuario actualizado con validaciones
- [x] PermissionService actualizado
- [x] index.php con redirección automática
- [x] admin/usuarios.php con selector de área
- [x] Validaciones cliente y servidor
- [x] Usuario de prueba creado
- [x] Tests implementados y pasando (7/7)
- [x] Documentación completa
- [x] Permisos en todas las páginas

---

## 🔮 Posibles Mejoras Futuras

1. **Múltiples Áreas por Usuario:**
   - Permitir que un area_admin gestione N áreas
   - Tabla intermedia: `usuario_areas`

2. **Permisos Granulares:**
   - Permitir solo ver pero no editar
   - Permitir solo ingresar valores pero no configurar

3. **Dashboard Personalizado:**
   - Mostrar solo gráficos específicos según area_admin
   - Ocultarconfiguraciones avanzadas

4. **Auditoría:**
   - Log de acciones de area_admin
   - Reporte de actividad por área

5. **Notificaciones:**
   - Alertar a dept_admin cuando area_admin hace cambios
   - Notificar a area_admin de nuevas metas

---

## 📞 Resumen Ejecutivo

### ¿Qué se Implementó?

Un nuevo rol `area_admin` que permite asignar usuarios a un área específica con permisos de gestión (ver y editar) restringidos solo a esa área.

### ¿Por Qué?

Para dar autonomía a gerentes de área sin darles acceso a otras áreas del mismo departamento, manteniendo la seguridad y privacidad de datos.

### ¿Cómo Funciona?

1. Super admin crea usuario con rol area_admin
2. Asigna departamento y área específica
3. Usuario solo puede acceder a esa área
4. Puede editar métricas, ingresar valores, crear gráficos
5. No puede ver ni acceder a otras áreas

### Impacto

- ✅ Mayor granularidad en permisos
- ✅ Mejor delegación de responsabilidades
- ✅ Más seguridad en acceso a datos
- ✅ Escalable a cualquier cantidad de áreas

---

**Archivos Modificados:** 6  
**Archivos Nuevos:** 4  
**Tests:** 7/7 pasando  
**Estado:** ✅ Producción Ready  

**Fecha de Implementación:** 28 de Abril 2026  
**Implementado por:** Sistema de Métricas v2.2
