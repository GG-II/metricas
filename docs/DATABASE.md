# Documentación de Base de Datos - Sistema de Métricas

Esquema completo y relaciones de la base de datos.

## Diagrama ER

```
┌──────────────────┐       ┌──────────────────┐
│   departamentos  │───┬───│      areas       │
└──────────────────┘   │   └──────────────────┘
                       │            │
                       │            │
                       │   ┌────────▼─────────┐
                       │   │     metricas     │
                       │   └────────┬─────────┘
                       │            │
                       │            ├───┬──────────────────┐
                       │            │   │                  │
                       │   ┌────────▼───▼──┐    ┌─────────▼────────┐
                       │   │valores_metricas│    │  metas_metricas  │
                       │   └────────────────┘    └──────────────────┘
                       │
                       │   ┌──────────────────┐
                       └───│     usuarios     │
                           └──────────────────┘
                                    │
                       ┌────────────┴────────────┐
                       │                         │
              ┌────────▼────────┐    ┌──────────▼───────────┐
              │    periodos     │    │configuracion_graficos│
              └─────────────────┘    └──────────────────────┘
```

---

## Tablas

### usuarios

Almacena información de usuarios del sistema.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `username` | VARCHAR(50) | NO | - | Nombre de usuario único |
| `password` | VARCHAR(255) | NO | - | Hash bcrypt de contraseña |
| `nombre` | VARCHAR(100) | NO | - | Nombre completo |
| `email` | VARCHAR(100) | NO | - | Correo electrónico |
| `rol` | ENUM | NO | 'dept_viewer' | super_admin, dept_admin, dept_viewer |
| `departamento_id` | INT(11) | YES | NULL | FK → departamentos |
| `area_id` | INT(11) | YES | NULL | FK → areas |
| `foto_perfil` | VARCHAR(255) | YES | NULL | Path a imagen de avatar |
| `tema` | ENUM | YES | 'auto' | auto, light, dark |
| `avatar_icono` | VARCHAR(50) | YES | NULL | Icono de Tabler para avatar |
| `avatar_color` | VARCHAR(7) | YES | '#206bc4' | Color hex para avatar |
| `activo` | TINYINT(1) | YES | 1 | 1 = activo, 0 = inactivo |
| `ultimo_acceso` | DATETIME | YES | NULL | Último login |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`username`)
- UNIQUE KEY (`email`)
- KEY (`departamento_id`)
- KEY (`area_id`)
- KEY (`activo`)

**Relaciones:**
- `departamento_id` → `departamentos.id` (ON DELETE SET NULL)
- `area_id` → `areas.id` (ON DELETE SET NULL)

---

### departamentos

Organización de alto nivel.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `nombre` | VARCHAR(100) | NO | - | Nombre del departamento |
| `slug` | VARCHAR(100) | YES | NULL | URL-friendly name |
| `descripcion` | TEXT | YES | NULL | Descripción opcional |
| `color` | VARCHAR(7) | YES | '#206bc4' | Color hex identificador |
| `icono` | VARCHAR(50) | YES | 'building' | Icono de Tabler |
| `orden` | INT(11) | YES | 0 | Orden de visualización |
| `activo` | TINYINT(1) | YES | 1 | 1 = activo, 0 = inactivo |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Índices:**
- PRIMARY KEY (`id`)
- KEY (`activo`)
- KEY (`orden`)

---

### areas

Áreas dentro de departamentos.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `departamento_id` | INT(11) | NO | - | FK → departamentos |
| `nombre` | VARCHAR(100) | NO | - | Nombre del área |
| `slug` | VARCHAR(100) | YES | NULL | URL-friendly name |
| `descripcion` | TEXT | YES | NULL | Descripción opcional |
| `color` | VARCHAR(7) | YES | NULL | Color hex (hereda de depto si NULL) |
| `icono` | VARCHAR(50) | YES | 'chart-line' | Icono de Tabler |
| `activo` | TINYINT(1) | YES | 1 | 1 = activo, 0 = inactivo |
| `orden` | INT(11) | YES | 0 | Orden de visualización |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Índices:**
- PRIMARY KEY (`id`)
- KEY (`departamento_id`)
- KEY (`activo`)
- KEY (`orden`)

**Relaciones:**
- `departamento_id` → `departamentos.id` (ON DELETE CASCADE)

---

### metricas

Definición de métricas/indicadores.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `area_id` | INT(11) | NO | - | FK → areas |
| `nombre` | VARCHAR(100) | NO | - | Nombre de la métrica |
| `slug` | VARCHAR(100) | NO | - | URL-friendly name |
| `descripcion` | TEXT | YES | NULL | Descripción/ayuda |
| `tiene_meta` | TINYINT(1) | YES | 0 | 1 si tiene metas configurables |
| `unidad` | VARCHAR(50) | YES | NULL | Unidad de medida (ej: "USD", "%") |
| `tipo_valor` | ENUM | YES | 'numero' | numero, decimal, porcentaje, tiempo |
| `icono` | VARCHAR(50) | YES | 'chart-line' | Icono de Tabler |
| `es_calculada` | TINYINT(1) | YES | 0 | 1 si es métrica calculada |
| `orden` | INT(11) | YES | 0 | Orden de visualización |
| `activo` | TINYINT(1) | YES | 1 | 1 = activo, 0 = inactivo |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Índices:**
- PRIMARY KEY (`id`)
- KEY (`area_id`)
- KEY (`activo`)
- KEY (`es_calculada`)

**Relaciones:**
- `area_id` → `areas.id` (ON DELETE CASCADE)

---

### periodos

Períodos temporales (meses).

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `ejercicio` | INT(4) | NO | - | Año (ej: 2026) |
| `periodo` | INT(2) | NO | - | Mes (1-12) |
| `nombre` | VARCHAR(50) | YES | NULL | Nombre descriptivo (ej: "Enero 2026") |
| `fecha_inicio` | DATE | YES | NULL | Inicio del período |
| `fecha_fin` | DATE | YES | NULL | Fin del período |
| `activo` | TINYINT(1) | YES | 1 | 1 = seleccionable, 0 = oculto |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`ejercicio`, `periodo`)
- KEY (`activo`)

---

### valores_metricas

Valores registrados de métricas.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `metrica_id` | INT(11) | NO | - | FK → metricas |
| `periodo_id` | INT(11) | NO | - | FK → periodos |
| `valor_numero` | INT(11) | YES | NULL | Para tipo_valor = numero |
| `valor_decimal` | DECIMAL(15,2) | YES | NULL | Para tipo_valor = decimal |
| `valor_texto` | TEXT | YES | NULL | Para datos adicionales |
| `usuario_registro_id` | INT(11) | YES | NULL | FK → usuarios (quién registró) |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de registro |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última modificación |

**Índices:**
- PRIMARY KEY (`id`)
- KEY (`metrica_id`)
- KEY (`periodo_id`)
- UNIQUE KEY (`metrica_id`, `periodo_id`) - Un valor por métrica por período

**Relaciones:**
- `metrica_id` → `metricas.id` (ON DELETE CASCADE)
- `periodo_id` → `periodos.id` (ON DELETE CASCADE)
- `usuario_registro_id` → `usuarios.id` (ON DELETE SET NULL)

---

### metas_metricas

Objetivos/metas de métricas.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `metrica_id` | INT(11) | NO | - | FK → metricas |
| `periodo_id` | INT(11) | YES | NULL | FK → periodos (para meta mensual) |
| `tipo_meta` | ENUM | NO | 'mensual' | mensual, anual |
| `ejercicio` | INT(4) | NO | - | Año de la meta |
| `valor_objetivo` | DECIMAL(15,2) | NO | - | Valor a alcanzar |
| `tipo_comparacion` | ENUM | YES | 'mayor_mejor' | mayor_mejor, menor_mejor |
| `activo` | TINYINT(1) | YES | 1 | 1 = vigente, 0 = archivada |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Índices:**
- PRIMARY KEY (`id`)
- KEY (`metrica_id`)
- KEY (`periodo_id`)
- KEY (`ejercicio`)

**Relaciones:**
- `metrica_id` → `metricas.id` (ON DELETE CASCADE)
- `periodo_id` → `periodos.id` (ON DELETE CASCADE)

**Lógica de Metas:**
- **Mensual**: `periodo_id` tiene valor → meta específica para ese mes
- **Anual**: `periodo_id` es NULL → meta para todo el año (se divide entre 12)

---

### configuracion_graficos

Widgets/gráficos en dashboards.

| Columna | Tipo | Null | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | INT(11) | NO | AUTO_INCREMENT | PK |
| `area_id` | INT(11) | NO | - | FK → areas |
| `tipo` | VARCHAR(50) | NO | - | Tipo de gráfico (kpi_card, line, etc.) |
| `titulo` | VARCHAR(200) | NO | - | Título del widget |
| `configuracion` | TEXT | NO | - | JSON con config del gráfico |
| `grid_x` | INT(11) | YES | 0 | Posición X en grid |
| `grid_y` | INT(11) | YES | 0 | Posición Y en grid |
| `grid_w` | INT(11) | YES | 6 | Ancho en columnas (1-12) |
| `grid_h` | INT(11) | YES | 3 | Alto en filas |
| `orden` | INT(11) | YES | 0 | Orden de carga |
| `activo` | TINYINT(1) | YES | 1 | 1 = visible, 0 = oculto |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | |

**Índices:**
- PRIMARY KEY (`id`)
- KEY (`area_id`)
- KEY (`activo`)

**Relaciones:**
- `area_id` → `areas.id` (ON DELETE CASCADE)

**Estructura JSON de configuracion:**
```json
{
  "metrica_id": 5,
  "color": "#3b82f6",
  "altura": 300,
  "periodos": 6,
  "mostrar_valores": true
}
```

---

## Queries Comunes

### Obtener Dashboard de un Área

```sql
SELECT 
    cg.*,
    a.nombre as area_nombre,
    a.color as area_color
FROM configuracion_graficos cg
JOIN areas a ON cg.area_id = a.id
WHERE cg.area_id = ? AND cg.activo = 1
ORDER BY cg.grid_y, cg.grid_x;
```

### Obtener Valores Históricos de una Métrica

```sql
SELECT 
    vm.*,
    p.ejercicio,
    p.periodo,
    p.nombre as periodo_nombre
FROM valores_metricas vm
JOIN periodos p ON vm.periodo_id = p.id
WHERE vm.metrica_id = ?
  AND p.activo = 1
ORDER BY p.ejercicio DESC, p.periodo DESC
LIMIT 12;
```

### Obtener Meta Aplicable

```sql
-- Meta mensual específica
SELECT * FROM metas_metricas
WHERE metrica_id = ?
  AND periodo_id = ?
  AND activo = 1
LIMIT 1;

-- Si no existe, buscar meta anual
SELECT * FROM metas_metricas
WHERE metrica_id = ?
  AND tipo_meta = 'anual'
  AND ejercicio = ?
  AND activo = 1
LIMIT 1;
```

### Métricas de un Área con Últimos Valores

```sql
SELECT 
    m.*,
    vm.valor_numero,
    vm.valor_decimal,
    p.ejercicio,
    p.periodo
FROM metricas m
LEFT JOIN valores_metricas vm ON m.id = vm.metrica_id
LEFT JOIN periodos p ON vm.periodo_id = p.id
WHERE m.area_id = ?
  AND m.activo = 1
GROUP BY m.id
ORDER BY m.orden, m.nombre;
```

---

## Mantenimiento

### Backup

```bash
# Backup completo
mysqldump -u root -p metricas_sistema > backup.sql

# Solo estructura
mysqldump -u root -p --no-data metricas_sistema > schema.sql

# Solo datos
mysqldump -u root -p --no-create-info metricas_sistema > data.sql
```

### Optimización

```sql
-- Analizar tablas
ANALYZE TABLE valores_metricas, configuracion_graficos, metas_metricas;

-- Optimizar
OPTIMIZE TABLE valores_metricas, configuracion_graficos, metas_metricas;

-- Ver tamaño de tablas
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'metricas_sistema'
ORDER BY size_mb DESC;
```

### Limpieza de Datos Antiguos

```sql
-- Eliminar valores de más de 5 años
DELETE vm FROM valores_metricas vm
JOIN periodos p ON vm.periodo_id = p.id
WHERE p.ejercicio < YEAR(NOW()) - 5;

-- Eliminar metas antiguas inactivas
DELETE FROM metas_metricas
WHERE activo = 0
  AND ejercicio < YEAR(NOW()) - 3;
```

---

## Migraciones

### Agregar Nueva Columna

```sql
-- Ejemplo: Agregar campo "privado" a metricas
ALTER TABLE metricas
ADD COLUMN privado TINYINT(1) DEFAULT 0 AFTER activo;

-- Crear índice
CREATE INDEX idx_privado ON metricas(privado);
```

### Modificar Tipo de Columna

```sql
-- Cambiar tamaño de VARCHAR
ALTER TABLE departamentos
MODIFY COLUMN nombre VARCHAR(150);

-- Cambiar tipo de dato
ALTER TABLE valores_metricas
MODIFY COLUMN valor_decimal DECIMAL(20,4);
```

---

## Integridad Referencial

Todas las foreign keys tienen acciones ON DELETE y ON UPDATE:

- **CASCADE**: Elimina/actualiza registros relacionados
- **SET NULL**: Establece NULL en registros relacionados
- **RESTRICT**: Previene eliminación si hay registros relacionados

**Ejemplo:**
```sql
-- Si eliminas un área, se eliminan sus métricas
areas (CASCADE)
  └─> metricas (CASCADE)
       ├─> valores_metricas
       └─> metas_metricas

-- Si eliminas un usuario, sus registros quedan con NULL
usuarios (SET NULL)
  └─> valores_metricas.usuario_registro_id
```

---

## Índices Recomendados

Para optimizar performance:

```sql
-- Índices compuestos para queries frecuentes
CREATE INDEX idx_valores_metrica_periodo ON valores_metricas(metrica_id, periodo_id);
CREATE INDEX idx_metas_metrica_ejercicio ON metas_metricas(metrica_id, ejercicio);
CREATE INDEX idx_graficos_area_activo ON configuracion_graficos(area_id, activo);

-- Índices para ORDER BY
CREATE INDEX idx_periodos_orden ON periodos(ejercicio DESC, periodo DESC);
CREATE INDEX idx_metricas_orden ON metricas(area_id, orden, nombre);
```

---

## Seguridad

### Usuario de Base de Datos

Crear usuario con permisos limitados:

```sql
-- Crear usuario
CREATE USER 'metricas_app'@'localhost' IDENTIFIED BY 'password_seguro';

-- Otorgar permisos
GRANT SELECT, INSERT, UPDATE, DELETE 
ON metricas_sistema.* 
TO 'metricas_app'@'localhost';

-- NO dar permisos de:
-- DROP, ALTER, CREATE, INDEX (solo para admin)

FLUSH PRIVILEGES;
```

### Auditoría

Agregar triggers para auditoría (opcional):

```sql
-- Crear tabla de auditoría
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla VARCHAR(50),
    accion ENUM('INSERT', 'UPDATE', 'DELETE'),
    registro_id INT,
    usuario_id INT,
    datos_anteriores TEXT,
    datos_nuevos TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trigger ejemplo
DELIMITER //
CREATE TRIGGER valores_metricas_audit
AFTER UPDATE ON valores_metricas
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, accion, registro_id, datos_anteriores, datos_nuevos)
    VALUES ('valores_metricas', 'UPDATE', NEW.id, 
            CONCAT('valor: ', OLD.valor_numero),
            CONCAT('valor: ', NEW.valor_numero));
END//
DELIMITER ;
```

---

**Versión del Esquema:** 2.0
**Última actualización:** 2026-04-25
