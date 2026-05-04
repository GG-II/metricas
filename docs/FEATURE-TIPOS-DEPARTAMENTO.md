# Feature: Tipos de Departamento y Áreas Globales Múltiples

**Versión:** 2.1  
**Fecha:** 28 de Abril 2026  
**Estado:** En Desarrollo

---

## 📋 Índice

1. [Contexto y Problema](#contexto-y-problema)
2. [Solución Propuesta](#solución-propuesta)
3. [Arquitectura Técnica](#arquitectura-técnica)
4. [Cambios en Base de Datos](#cambios-en-base-de-datos)
5. [Cambios en Código](#cambios-en-código)
6. [Casos de Uso](#casos-de-uso)
7. [Migración y Retrocompatibilidad](#migración-y-retrocompatibilidad)
8. [Testing](#testing)

---

## Contexto y Problema

### Organización Objetivo

El sistema se implementará en una **Cooperativa** con estructura organizacional compleja:

1. **Red de Agencias** (Operacional/Comercial)
   - Múltiples sucursales distribuidas geográficamente
   - Áreas operativas comunes: Caja, Créditos, Cuentas Nuevas, Atención Cliente
   - Contribuyen directamente a metas comerciales globales

2. **Departamentos Corporativos** (Soporte/Gestión)
   - Áreas de soporte: Informática, Mercadeo, Recursos Humanos, Negocio
   - Métricas internas/operativas
   - También pueden contribuir a algunas metas globales

3. **Métricas Globales Consolidadas**
   - Metas organizacionales (ej: Q47M utilidades, 980M colocación créditos)
   - Se calculan sumando métricas de múltiples agencias y departamentos
   - Requieren organización en categorías (Financieras, Comerciales, Operativas)

### Problema Actual

El sistema actual:

❌ No distingue entre "Agencias" y "Departamentos Corporativos"  
❌ Todos usan la misma tabla `departamentos` sin diferenciación  
❌ Solo permite una área global ("Métricas Consolidadas")  
❌ No hay forma de organizar métricas globales por categorías  
❌ La UI no refleja la diferencia organizacional entre tipos

### Necesidad

✅ Distinguir visualmente entre Agencias y Departamentos Corporativos  
✅ Permitir múltiples áreas dentro del departamento Global  
✅ Organizar métricas globales por categorías (Financieras, Comerciales, etc.)  
✅ Mantener flexibilidad para que ambos tipos contribuyan a metas globales  
✅ Conservar retrocompatibilidad con datos existentes

---

## Solución Propuesta

### 1. Campo `tipo` en Departamentos

Agregar enumeración de tipo a la tabla `departamentos`:

```sql
tipo ENUM('agencia', 'corporativo', 'global')
```

**Propósito:** Organización visual y filtrado, NO restricción funcional.

### 2. Múltiples Áreas Globales

Permitir crear múltiples áreas dentro del departamento tipo `global`:

```
Departamento Global
  ├─ Área: Métricas Financieras
  ├─ Área: Métricas Comerciales
  ├─ Área: Métricas Operativas
  └─ Área: KPIs Estratégicos
```

### 3. Detección de Áreas Globales Mejorada

**Antes:** Solo detectaba el slug `metricas-consolidadas`  
**Ahora:** Detecta CUALQUIER área que pertenezca a un departamento con `tipo = 'global'`

### 4. UI Organizada por Tipo

Dashboard con secciones visuales separadas:

- 🏦 **Red de Agencias** (tipo='agencia')
- 🏢 **Corporativo** (tipo='corporativo')
- 🌍 **Global** (tipo='global', solo super_admin)

---

## Arquitectura Técnica

### Diagrama de Entidades

```
┌─────────────────────────────────────────────────────────┐
│                    DEPARTAMENTOS                        │
│  ┌──────────────┬──────────────┬──────────────┐        │
│  │   AGENCIAS   │ CORPORATIVO  │    GLOBAL    │        │
│  │ tipo=agencia │tipo=corporati│  tipo=global │        │
│  │              │vo            │              │        │
│  └──────┬───────┴──────┬───────┴──────┬───────┘        │
│         │              │              │                │
│    ┌────▼────┐    ┌────▼────┐    ┌────▼────┐          │
│    │  ÁREAS  │    │  ÁREAS  │    │  ÁREAS  │          │
│    │  (Caja, │    │(Soporte,│    │(Finan., │          │
│    │Créditos)│    │ Diseño) │    │Comerc.) │          │
│    └────┬────┘    └────┬────┘    └────┬────┘          │
│         │              │              │                │
│    ┌────▼────┐    ┌────▼────┐    ┌────▼──────────┐    │
│    │MÉTRICAS │    │MÉTRICAS │    │   MÉTRICAS    │    │
│    │(Normales│    │(Normales│    │  (CALCULADAS  │    │
│    │ o Calc.)│    │ o Calc.)│    │   GLOBALES)   │    │
│    └─────────┘    └─────────┘    └───────────────┘    │
└─────────────────────────────────────────────────────────┘
```

### Flujo de Consolidación

```
PASO 1: Registro de valores en agencias/corporativo
┌─────────────────────────────────────────────────┐
│ Agencia SJ > Créditos > Colocación = 45M       │
│ Agencia Heredia > Créditos > Colocación = 38M  │
│ Corp. Negocio > Productos > Especiales = 10M   │
└─────────────────────────────────────────────────┘
                    ↓
PASO 2: Métrica calculada global suma componentes
┌─────────────────────────────────────────────────┐
│ Global > Financieras > Total Colocación        │
│   Componentes:                                  │
│     - Agencia SJ (45M)                         │
│     - Agencia Heredia (38M)                    │
│     - Corp. Negocio (10M)                      │
│   Operación: SUMA                              │
│   Resultado: 93M                               │
└─────────────────────────────────────────────────┘
                    ↓
PASO 3: Comparación con meta
┌─────────────────────────────────────────────────┐
│ Valor actual: 93M                              │
│ Meta anual: 980M                               │
│ Cumplimiento: 9.5% (enero)                     │
│ Semáforo: 🔴 Rojo (muy por debajo)            │
└─────────────────────────────────────────────────┘
```

---

## Cambios en Base de Datos

### Migración 008: Agregar Campo Tipo

**Archivo:** `database/migrations/008_add_tipo_departamentos.sql`

```sql
-- =============================================
-- Migración 008: Agregar tipo a departamentos
-- Fecha: 2026-04-28
-- Descripción: Distinción entre Agencias, Corporativo y Global
-- =============================================

-- 1. Agregar campo tipo
ALTER TABLE departamentos 
ADD COLUMN tipo ENUM('agencia', 'corporativo', 'global') 
DEFAULT 'corporativo' 
AFTER descripcion;

-- 2. Agregar índice para filtrado eficiente
ALTER TABLE departamentos 
ADD INDEX idx_tipo (tipo);

-- 3. Marcar departamento Global existente
UPDATE departamentos 
SET tipo = 'global' 
WHERE nombre = 'Global';

-- 4. Verificación
SELECT id, nombre, tipo, activo 
FROM departamentos 
ORDER BY tipo, orden;
```

### Datos de Ejemplo Post-Migración

```sql
-- Ejemplo de departamentos después de migración
+----+-------------------+--------------+--------+
| id | nombre            | tipo         | activo |
+----+-------------------+--------------+--------+
|  1 | Agencia San José  | agencia      |      1 |
|  2 | Agencia Heredia   | agencia      |      1 |
|  3 | Agencia Cartago   | agencia      |      1 |
|  4 | Informática       | corporativo  |      1 |
|  5 | Mercadeo          | corporativo  |      1 |
|  6 | Negocio           | corporativo  |      1 |
| 99 | Global            | global       |      1 |
+----+-------------------+--------------+--------+

-- Ejemplo de áreas globales múltiples
SELECT a.id, a.nombre, d.nombre as departamento, d.tipo
FROM areas a
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'global';

+----+------------------------+--------------+--------+
| id | nombre                 | departamento | tipo   |
+----+------------------------+--------------+--------+
|  6 | Métricas Financieras   | Global       | global |
|  7 | Métricas Comerciales   | Global       | global |
|  8 | Métricas Operativas    | Global       | global |
|  9 | KPIs Estratégicos      | Global       | global |
+----+------------------------+--------------+--------+
```

### Impacto en Tablas

| Tabla | Cambio | Tipo |
|-------|--------|------|
| `departamentos` | ✅ Agregar campo `tipo` | ALTER TABLE |
| `areas` | ❌ Sin cambios | - |
| `metricas` | ❌ Sin cambios | - |
| `valores_metricas` | ❌ Sin cambios | - |
| `usuarios` | ❌ Sin cambios | - |

**Retrocompatibilidad:** ✅ Total - Solo agrega columna con default

---

## Cambios en Código

### 1. Modelo: Departamento.php

**Nuevos métodos:**

```php
/**
 * Obtener solo agencias activas
 */
public function getAgencias() {
    $stmt = $this->db->query("
        SELECT * FROM {$this->table}
        WHERE tipo = 'agencia' AND activo = 1
        ORDER BY orden, nombre
    ");
    return $stmt->fetchAll();
}

/**
 * Obtener solo departamentos corporativos activos
 */
public function getCorporativos() {
    $stmt = $this->db->query("
        SELECT * FROM {$this->table}
        WHERE tipo = 'corporativo' AND activo = 1
        ORDER BY orden, nombre
    ");
    return $stmt->fetchAll();
}

/**
 * Obtener departamento global
 */
public function getGlobal() {
    $stmt = $this->db->query("
        SELECT * FROM {$this->table}
        WHERE tipo = 'global' AND activo = 1
        LIMIT 1
    ");
    return $stmt->fetch();
}

/**
 * Obtener estadísticas por tipo
 */
public function getStatsByTipo() {
    $stmt = $this->db->query("
        SELECT 
            tipo,
            COUNT(*) as total,
            SUM(activo) as activos
        FROM {$this->table}
        GROUP BY tipo
    ");
    return $stmt->fetchAll();
}
```

### 2. Servicio: MetricaCalculadaService.php

**Nuevo método de detección:**

```php
/**
 * Verificar si un área pertenece al departamento Global
 * 
 * @param int $area_id ID del área a verificar
 * @return bool True si es área global
 */
private function esAreaGlobal($area_id) {
    $stmt = $this->db->prepare("
        SELECT d.tipo 
        FROM areas a
        JOIN departamentos d ON a.departamento_id = d.id
        WHERE a.id = ?
    ");
    $stmt->execute([$area_id]);
    $result = $stmt->fetch();
    
    return $result && $result['tipo'] === 'global';
}
```

**Modificación de método existente:**

```php
/**
 * Obtener métricas disponibles como componentes
 * 
 * CAMBIO: Ahora detecta área global por tipo de departamento,
 * no por slug hardcodeado
 */
public function getMetricasDisponibles($area_id, $excluir_metrica_id = null) {
    // NUEVO: Usar método de detección flexible
    $es_area_global = $this->esAreaGlobal($area_id);
    
    if ($es_area_global) {
        // GLOBAL: Todas las métricas excepto de otras áreas globales
        $sql = "
            SELECT 
                m.id, 
                m.nombre, 
                m.unidad, 
                m.tipo_valor, 
                m.es_calculada,
                a.nombre as area_nombre,
                a.color as area_color,
                d.nombre as departamento_nombre,
                d.tipo as departamento_tipo,
                d.color as departamento_color
            FROM metricas m
            JOIN areas a ON m.area_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE m.activo = 1 
              AND d.tipo != 'global'  -- Evitar recursión
        ";
        $params = [];
        
        if ($excluir_metrica_id) {
            $sql .= " AND m.id != ?";
            $params[] = $excluir_metrica_id;
        }
        
        // Ordenar: primero agencias, luego corporativo
        $sql .= " ORDER BY 
                    FIELD(d.tipo, 'agencia', 'corporativo'),
                    d.orden, 
                    a.orden, 
                    m.orden, 
                    m.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
    } else {
        // NORMAL: Solo métricas de esta área
        $sql = "
            SELECT 
                m.id, 
                m.nombre, 
                m.unidad, 
                m.tipo_valor, 
                m.es_calculada
            FROM metricas m
            WHERE m.area_id = ? AND m.activo = 1
        ";
        $params = [$area_id];
        
        if ($excluir_metrica_id) {
            $sql .= " AND m.id != ?";
            $params[] = $excluir_metrica_id;
        }
        
        $sql .= " ORDER BY m.orden, m.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
    
    return $stmt->fetchAll();
}
```

### 3. Endpoint: get-metricas-by-area.php

**Actualización de validación:**

```php
// ANTES (línea 37):
$es_area_global = ($area && $area['slug'] === 'metricas-consolidadas');

// AHORA:
$es_area_global = $calculadaService->esAreaGlobal($area_id);
// Nota: Hacer el método público temporalmente o crear método en AreaModel
```

**Alternativa - Agregar método público en servicio:**

```php
// En MetricaCalculadaService.php
public function isAreaGlobal($area_id) {
    return $this->esAreaGlobal($area_id);
}

// En get-metricas-by-area.php
$es_area_global = $calculadaService->isAreaGlobal($area_id);
```

### 4. Vista: Dashboard Principal (index.php)

**Agregar navegación por pestañas:**

```php
<?php
// Obtener departamentos por tipo
$agencias = $departamentoModel->getAgencias();
$corporativos = $departamentoModel->getCorporativos();
$global = $departamentoModel->getGlobal();
?>

<div class="page-header">
    <h1>Sistema de Métricas - Cooperativa</h1>
</div>

<!-- Pestañas de Navegación -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-agencias">
            <i class="ti ti-building-bank me-1"></i>
            Red de Agencias
            <span class="badge bg-primary ms-2"><?= count($agencias) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-corporativo">
            <i class="ti ti-building me-1"></i>
            Corporativo
            <span class="badge bg-info ms-2"><?= count($corporativos) ?></span>
        </a>
    </li>
    <?php if ($user['rol'] === 'super_admin'): ?>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-global">
            <i class="ti ti-world me-1"></i>
            Global
            <span class="badge bg-purple ms-2">Super Admin</span>
        </a>
    </li>
    <?php endif; ?>
</ul>

<!-- Contenido de Pestañas -->
<div class="tab-content">
    <!-- Agencias -->
    <div class="tab-pane fade show active" id="tab-agencias">
        <div class="row">
            <?php foreach ($agencias as $agencia): ?>
                <div class="col-md-4">
                    <div class="card card-link">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar me-3" style="background-color: <?= $agencia['color'] ?>">
                                    <i class="ti ti-<?= $agencia['icono'] ?>"></i>
                                </span>
                                <div>
                                    <h3 class="card-title mb-0"><?= htmlspecialchars($agencia['nombre']) ?></h3>
                                    <small class="text-muted">Agencia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Corporativo -->
    <div class="tab-pane fade" id="tab-corporativo">
        <div class="row">
            <?php foreach ($corporativos as $depto): ?>
                <div class="col-md-4">
                    <div class="card card-link">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar me-3" style="background-color: <?= $depto['color'] ?>">
                                    <i class="ti ti-<?= $depto['icono'] ?>"></i>
                                </span>
                                <div>
                                    <h3 class="card-title mb-0"><?= htmlspecialchars($depto['nombre']) ?></h3>
                                    <small class="text-muted">Departamento</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Global (Solo Super Admin) -->
    <?php if ($user['rol'] === 'super_admin' && $global): ?>
    <div class="tab-pane fade" id="tab-global">
        <div class="alert alert-purple">
            <i class="ti ti-shield-lock me-2"></i>
            <strong>Área Global - Solo Super Administrador</strong>
            <p class="mb-0">Métricas consolidadas de toda la organización</p>
        </div>
        
        <?php
        $areas_globales = $areaModel->getByDepartamento($global['id']);
        ?>
        
        <div class="row">
            <?php foreach ($areas_globales as $area): ?>
                <div class="col-md-4">
                    <div class="card card-link" onclick="location.href='dashboard.php?area_id=<?= $area['id'] ?>'">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar me-3 bg-purple">
                                    <i class="ti ti-<?= $area['icono'] ?>"></i>
                                </span>
                                <div>
                                    <h3 class="card-title mb-0"><?= htmlspecialchars($area['nombre']) ?></h3>
                                    <small class="text-muted">Métricas Globales</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
```

### 5. Vista: Admin de Áreas

**Permitir crear áreas en departamento Global:**

```php
// public/admin/areas.php

// Filtrar departamentos según rol
if ($user['rol'] === 'super_admin') {
    // Super admin ve TODOS los departamentos (incluido Global)
    $departamentos = $departamentoModel->getAllWithStats();
} else if ($user['rol'] === 'dept_admin') {
    // Dept admin solo ve su departamento (nunca Global)
    $departamentos = [$departamentoModel->getWithAreas($user['departamento_id'])];
} else {
    $departamentos = [];
}
```

```html
<!-- Formulario de creación -->
<form method="POST">
    <div class="mb-3">
        <label class="form-label">Departamento</label>
        <select name="departamento_id" class="form-select" required>
            <?php foreach ($departamentos as $depto): ?>
                <option value="<?= $depto['id'] ?>">
                    <?= htmlspecialchars($depto['nombre']) ?>
                    <?php if ($depto['tipo'] === 'global'): ?>
                        <span class="badge bg-purple">Global</span>
                    <?php elseif ($depto['tipo'] === 'agencia'): ?>
                        <span class="badge bg-success">Agencia</span>
                    <?php else: ?>
                        <span class="badge bg-info">Corporativo</span>
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Nombre del Área</label>
        <input type="text" name="nombre" class="form-control" 
               placeholder="Ej: Métricas Financieras" required>
    </div>
    
    <!-- ... resto del formulario ... -->
</form>
```

---

## Casos de Uso

### Caso 1: Crear Meta Global Financiera

**Usuario:** Super Admin  
**Objetivo:** Crear métrica global "Total Colocación de Créditos"

**Pasos:**

1. Navegar a "Global > Métricas Financieras"
2. Crear nueva métrica:
   - Nombre: "Total Colocación de Créditos"
   - Tipo: Calculada
   - Componentes:
     - Agencia San José > Créditos > Colocación
     - Agencia Heredia > Créditos > Colocación
     - Agencia Cartago > Créditos > Colocación
   - Operación: SUMA
3. Definir meta anual: 980,000,000
4. Sistema calcula automáticamente cada período

**Resultado:**
```
Período: Enero 2026
  Agencia SJ: 45M
  Agencia Heredia: 38M
  Agencia Cartago: 30M
  ────────────────
  Total: 113M
  Meta: 980M
  % Cumplimiento: 11.5%
  Semáforo: 🟡 Amarillo (esperado para mes 1)
```

### Caso 2: Crear Área Global para KPIs Estratégicos

**Usuario:** Super Admin  
**Objetivo:** Organizar KPIs ejecutivos en nueva área global

**Pasos:**

1. Admin > Áreas > Crear Nueva
2. Seleccionar departamento: "Global"
3. Nombre: "KPIs Estratégicos"
4. Slug: "kpis-estrategicos"
5. Icono: "chart-dots-3"
6. Color: "#8b5cf6" (purple)
7. Guardar

**Resultado:**
- Nueva área visible solo para super_admin
- Puede contener métricas calculadas exclusivas de C-Level
- Ejemplos: ROE, Índice de Morosidad, Cuota de Mercado

### Caso 3: Dashboard Ejecutivo Mensual

**Usuario:** Super Admin (Director General)  
**Vista:** Dashboard Global > Métricas Comerciales

**Métricas Visibles:**

```
┌─────────────────────────────────────────────┐
│ 📊 Métricas Comerciales - Abril 2026       │
├─────────────────────────────────────────────┤
│                                             │
│  Asociados Nuevos: 2,150 / 6,000 (35.8%)   │
│  🟢 Adelantado (esperado: 33%)              │
│                                             │
│  Tarjetas de Débito: 1,200 / 4,300 (27.9%) │
│  🟡 En Meta (esperado: 25%)                 │
│                                             │
│  CIC Virtual: 980 / 4,260 (23.0%)           │
│  🔴 Atrasado (esperado: 33%)                │
│                                             │
│  Billetera: 450 / 2,565 (17.5%)             │
│  🔴 Atrasado (esperado: 25%)                │
│                                             │
└─────────────────────────────────────────────┘
```

**Drill-down:** Click en métrica → Ver contribución por agencia

---

## Migración y Retrocompatibilidad

### Estrategia de Migración

**Fase 1: Migración de Esquema (Sin impacto)**
```bash
# Ejecutar migración
mysql -u root -p metricas_db < database/migrations/008_add_tipo_departamentos.sql

# Verificar
mysql -u root -p metricas_db -e "DESCRIBE departamentos;"
```

**Fase 2: Clasificación de Departamentos Existentes**
```sql
-- Marcar agencias (manual o por patrón)
UPDATE departamentos 
SET tipo = 'agencia' 
WHERE nombre LIKE 'Agencia%' OR nombre IN ('San José', 'Heredia', 'Cartago');

-- Marcar global
UPDATE departamentos 
SET tipo = 'global' 
WHERE nombre = 'Global';

-- El resto queda como 'corporativo' (default)
```

**Fase 3: Crear Áreas Globales Adicionales** (Opcional)
```sql
-- Obtener ID del departamento Global
SET @global_id = (SELECT id FROM departamentos WHERE tipo = 'global' LIMIT 1);

-- Crear áreas globales
INSERT INTO areas (departamento_id, nombre, slug, color, icono, orden) VALUES
(@global_id, 'Métricas Financieras', 'metricas-financieras', '#8b5cf6', 'currency-dollar', 1),
(@global_id, 'Métricas Comerciales', 'metricas-comerciales', '#8b5cf6', 'shopping-cart', 2),
(@global_id, 'Métricas Operativas', 'metricas-operativas', '#8b5cf6', 'settings', 3),
(@global_id, 'KPIs Estratégicos', 'kpis-estrategicos', '#8b5cf6', 'chart-dots-3', 4);
```

**Fase 4: Actualizar Código** (Deploy)
```bash
# Copiar archivos actualizados
git pull origin main

# Limpiar caché si existe
php cache-manager.php clear
```

### Retrocompatibilidad

✅ **100% Compatible con Código Anterior**

| Escenario | Comportamiento |
|-----------|----------------|
| Consultas sin filtro de tipo | ✅ Funcionan igual (tipo tiene default) |
| Área global slug-based | ✅ Sigue funcionando (ambos métodos coexisten) |
| Usuarios sin asignación | ✅ Sin cambios |
| Métricas existentes | ✅ Sin modificación |
| Valores históricos | ✅ Intactos |

**Rollback Plan:**
```sql
-- Si se necesita revertir
ALTER TABLE departamentos DROP COLUMN tipo;
-- Código anterior seguirá funcionando
```

---

## Testing

### Test Cases

#### TC-001: Migración de Esquema

**Objetivo:** Verificar que la migración se ejecuta sin errores

```bash
# Pre-condición
mysql -u root -p metricas_db -e "DESCRIBE departamentos;" | grep tipo
# Resultado esperado: Vacío (campo no existe)

# Ejecutar migración
mysql -u root -p metricas_db < database/migrations/008_add_tipo_departamentos.sql

# Post-condición
mysql -u root -p metricas_db -e "DESCRIBE departamentos;" | grep tipo
# Resultado esperado: tipo | enum('agencia','corporativo','global')
```

**Criterio de Éxito:** ✅ Campo existe, índice creado, Global marcado

---

#### TC-002: Modelo Departamento - Métodos por Tipo

**Objetivo:** Verificar filtrado por tipo en modelo

```php
// Test script: tests/DepartamentoTipoTest.php
$deptModel = new Departamento();

// Test: Obtener agencias
$agencias = $deptModel->getAgencias();
assert(count($agencias) > 0, "Debe haber al menos una agencia");
foreach ($agencias as $ag) {
    assert($ag['tipo'] === 'agencia', "Tipo debe ser 'agencia'");
}

// Test: Obtener corporativos
$corporativos = $deptModel->getCorporativos();
foreach ($corporativos as $corp) {
    assert($corp['tipo'] === 'corporativo', "Tipo debe ser 'corporativo'");
}

// Test: Obtener global
$global = $deptModel->getGlobal();
assert($global !== false, "Debe existir departamento global");
assert($global['tipo'] === 'global', "Tipo debe ser 'global'");

echo "✅ Todos los tests de Departamento pasaron\n";
```

---

#### TC-003: Servicio - Detección de Área Global

**Objetivo:** Verificar que detecta áreas globales correctamente

```php
// Test script: tests/AreaGlobalDetectionTest.php
$service = new MetricaCalculadaService();
$areaModel = new Area();

// Crear área de prueba en departamento global
$global = (new Departamento())->getGlobal();
$area_test_id = $areaModel->create([
    'departamento_id' => $global['id'],
    'nombre' => 'Test Global Area',
    'slug' => 'test-global-area'
]);

// Test: Detectar área global
$es_global = $service->isAreaGlobal($area_test_id);
assert($es_global === true, "Debe detectar área como global");

// Test: Área normal no es global
$area_normal_id = 1; // Asumiendo que existe y no es global
$es_global_normal = $service->isAreaGlobal($area_normal_id);
assert($es_global_normal === false, "Área normal no debe ser global");

// Cleanup
$areaModel->delete($area_test_id);

echo "✅ Tests de detección de área global pasaron\n";
```

---

#### TC-004: Métricas Disponibles - Filtrado de Globales

**Objetivo:** Verificar que áreas globales excluyen otras áreas globales

```php
// Test script: tests/MetricasDisponiblesTest.php
$service = new MetricaCalculadaService();
$global = (new Departamento())->getGlobal();

// Crear dos áreas globales de prueba
$area1_id = (new Area())->create([
    'departamento_id' => $global['id'],
    'nombre' => 'Global Test 1',
    'slug' => 'global-test-1'
]);

$area2_id = (new Area())->create([
    'departamento_id' => $global['id'],
    'nombre' => 'Global Test 2',
    'slug' => 'global-test-2'
]);

// Test: Métricas disponibles desde área global
$metricas = $service->getMetricasDisponibles($area1_id);

// Verificar que NO incluye métricas de otras áreas globales
foreach ($metricas as $metrica) {
    assert(
        $metrica['departamento_tipo'] !== 'global',
        "No debe incluir métricas de departamentos globales"
    );
}

// Cleanup
(new Area())->delete($area1_id);
(new Area())->delete($area2_id);

echo "✅ Tests de métricas disponibles pasaron\n";
```

---

#### TC-005: UI - Navegación por Pestañas

**Objetivo:** Verificar renderizado de pestañas por tipo

**Pasos Manuales:**

1. Login como super_admin
2. Ir a dashboard principal
3. Verificar que existen 3 pestañas:
   - 🏦 Red de Agencias
   - 🏢 Corporativo
   - 🌍 Global

4. Click en cada pestaña
5. Verificar que muestra departamentos correctos

**Criterios:**
- ✅ Pestaña "Red de Agencias" muestra solo tipo='agencia'
- ✅ Pestaña "Corporativo" muestra solo tipo='corporativo'
- ✅ Pestaña "Global" muestra áreas del departamento global
- ✅ Para dept_admin: NO ve pestaña Global

---

#### TC-006: Permisos - Solo Super Admin Ve Global

**Objetivo:** Verificar restricción de acceso

```php
// Test: Super admin puede acceder
$_SESSION['user'] = ['rol' => 'super_admin', 'id' => 1];
$response = file_get_contents('http://localhost/metricas/public/api/get-metricas-by-area.php?area_id=6');
$data = json_decode($response, true);
assert($data['success'] === true, "Super admin debe poder acceder");

// Test: Dept admin NO puede acceder a área global
$_SESSION['user'] = ['rol' => 'dept_admin', 'departamento_id' => 2, 'id' => 2];
$response = file_get_contents('http://localhost/metricas/public/api/get-metricas-by-area.php?area_id=6');
$data = json_decode($response, true);
assert($data['success'] === false, "Dept admin NO debe acceder");
assert(strpos($data['error'], 'super_admin') !== false, "Mensaje debe mencionar super_admin");

echo "✅ Tests de permisos pasaron\n";
```

---

#### TC-007: Integración - Crear Métrica Calculada Global

**Objetivo:** Flujo completo de creación de meta global

**Pasos:**

1. Login como super_admin
2. Ir a Global > Métricas Financieras
3. Crear nueva métrica:
   - Nombre: "Test Total Colocación"
   - Tipo: Calculada
   - Componentes: Seleccionar 3 métricas de diferentes agencias
   - Operación: Suma
4. Guardar
5. Verificar que calcula correctamente

**SQL de Verificación:**
```sql
-- Verificar que la métrica se creó en área global
SELECT m.*, a.nombre as area, d.tipo as dept_tipo
FROM metricas m
JOIN areas a ON m.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
WHERE m.nombre = 'Test Total Colocación';

-- Resultado esperado: dept_tipo = 'global', es_calculada = 1

-- Verificar componentes
SELECT mc.*, m.nombre as metrica_nombre
FROM metricas_componentes mc
JOIN metricas m ON mc.metrica_componente_id = m.id
WHERE mc.metrica_calculada_id = [ID de la métrica creada];

-- Resultado esperado: 3 componentes, todos activos
```

**Criterios:**
- ✅ Métrica se crea en área global
- ✅ Componentes se guardan correctamente
- ✅ Cálculo funciona
- ✅ No permite seleccionar métricas de otras áreas globales

---

### Suite de Tests Automatizados

**Archivo:** `tests/run_tipo_departamento_tests.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Ejecutando Suite de Tests - Tipos de Departamento\n\n";

$tests = [
    'DepartamentoTipoTest.php',
    'AreaGlobalDetectionTest.php',
    'MetricasDisponiblesTest.php',
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    echo "Ejecutando: $test\n";
    ob_start();
    try {
        include __DIR__ . '/' . $test;
        $output = ob_get_clean();
        echo $output;
        $passed++;
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo "❌ FALLÓ: " . $e->getMessage() . "\n";
        echo $output;
        $failed++;
    }
    echo "\n";
}

echo "──────────────────────────────────\n";
echo "Resumen: $passed pasaron, $failed fallaron\n";

exit($failed > 0 ? 1 : 0);
```

**Ejecutar:**
```bash
php tests/run_tipo_departamento_tests.php
```

---

## Resumen de Archivos a Crear/Modificar

### 📝 Nuevos Archivos

| Archivo | Descripción |
|---------|-------------|
| `database/migrations/008_add_tipo_departamentos.sql` | Migración de esquema |
| `docs/FEATURE-TIPOS-DEPARTAMENTO.md` | Esta documentación |
| `tests/DepartamentoTipoTest.php` | Tests unitarios |
| `tests/AreaGlobalDetectionTest.php` | Tests de detección |
| `tests/MetricasDisponiblesTest.php` | Tests de filtrado |
| `tests/run_tipo_departamento_tests.php` | Suite de tests |

### ✏️ Archivos a Modificar

| Archivo | Cambios |
|---------|---------|
| `src/Models/Departamento.php` | +4 métodos (getAgencias, getCorporativos, getGlobal, getStatsByTipo) |
| `src/Services/MetricaCalculadaService.php` | +2 métodos (esAreaGlobal, isAreaGlobal), modificar getMetricasDisponibles |
| `public/api/get-metricas-by-area.php` | Cambiar detección de área global (línea ~37) |
| `public/index.php` | Agregar navegación por pestañas |
| `public/admin/areas.php` | Permitir crear áreas en Global para super_admin |
| `public/admin/departamentos.php` | Mostrar badge de tipo, filtros |

---

## Próximos Pasos

1. ✅ **Documentación Completa** (Este archivo)
2. ⏳ **Crear Migración** `008_add_tipo_departamentos.sql`
3. ⏳ **Modificar Modelos** (Departamento.php)
4. ⏳ **Modificar Servicios** (MetricaCalculadaService.php)
5. ⏳ **Actualizar Endpoints** (get-metricas-by-area.php)
6. ⏳ **Modificar Vistas** (index.php, admin/areas.php, admin/departamentos.php)
7. ⏳ **Crear Tests** (Suite completa)
8. ⏳ **Testing Manual** (Checklist de casos de uso)
9. ⏳ **Commit y Deploy**

---

## Notas Adicionales

### Consideraciones de Performance

- **Índice en `tipo`:** Mejora queries de filtrado por tipo
- **Orden de pestañas:** Cargar solo la pestaña activa (lazy loading)
- **Cache:** Considerar cachear lista de departamentos por tipo

### Seguridad

- ✅ Solo super_admin puede ver/editar áreas globales
- ✅ Validación en backend (no solo frontend)
- ✅ Auditoria de cambios en metas globales (tabla audit_log)

### Escalabilidad

- ✅ Soporta N agencias sin cambios de código
- ✅ Permite crear áreas globales adicionales dinámicamente
- ✅ Flexible para futuros tipos (ej: 'franquicia', 'regional')

---

**Versión del Documento:** 1.0  
**Última Actualización:** 2026-04-28  
**Autores:** Equipo de Desarrollo  
**Aprobado por:** Pendiente

---
