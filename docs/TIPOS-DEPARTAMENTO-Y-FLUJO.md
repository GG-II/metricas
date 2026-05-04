# Sistema de Tipos de Departamento y Flujo de Métricas

**Versión:** 2.1  
**Fecha:** Abril 2026  
**Autor:** Sistema de Métricas - Cooperativa

---

## 📋 Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Problema Original](#problema-original)
3. [Solución Implementada](#solución-implementada)
4. [Estructura del Sistema](#estructura-del-sistema)
5. [Flujo de Métricas](#flujo-de-métricas)
6. [Navegación y Permisos](#navegación-y-permisos)
7. [Ejemplos Prácticos](#ejemplos-prácticos)
8. [Cambios Técnicos](#cambios-técnicos)

---

## 📌 Resumen Ejecutivo

Se implementó un sistema de **clasificación por tipos de departamento** para organizar la estructura de la cooperativa de manera más clara y funcional:

- **🏦 Agencias**: Sucursales físicas con operaciones locales
- **🏢 Corporativo**: Departamentos centrales de soporte
- **🌍 Global**: Área ejecutiva con métricas consolidadas

Este cambio permite:
- Visualización organizada por tipo de departamento
- Múltiples áreas globales para diferentes categorías de métricas
- Métricas calculadas que suman automáticamente datos de todas las agencias
- Dashboards ejecutivos con visión consolidada

---

## ❌ Problema Original

### Limitaciones del Sistema Anterior

1. **Sin distinción entre tipos**
   - Todas las áreas se veían igual
   - No había separación visual entre agencias y departamentos centrales
   - Difícil navegación cuando hay muchos departamentos

2. **Área global hardcodeada**
   - Solo existía una área global fija: "Métricas Consolidadas"
   - No se podían crear otras áreas globales
   - La detección era por slug específico (`metricas-consolidadas`)
   - Inflexible para necesidades ejecutivas

3. **Confusión en la navegación**
   - Todo mezclado en una sola vista
   - No era claro qué era una agencia vs departamento corporativo
   - Super admin veía todo revuelto

4. **Estructura no alineada con la organización**
   - Las cooperativas tienen:
     - Red de agencias (sucursales)
     - Departamentos centrales (corporativo)
     - Métricas consolidadas (ejecutivo)
   - El sistema no reflejaba esta estructura real

---

## ✅ Solución Implementada

### 1. Campo `tipo` en Departamentos

Se agregó un campo ENUM a la tabla `departamentos`:

```
tipo = 'agencia' | 'corporativo' | 'global'
```

**Comportamiento:**
- Departamentos existentes se marcaron como `'corporativo'` por defecto
- Se creó un departamento especial tipo `'global'`
- Se crearon 4 departamentos tipo `'agencia'`

### 2. Múltiples Áreas Globales

**Antes:**
- Solo 1 área global hardcodeada

**Ahora:**
- Departamento "Global" puede tener N áreas
- Cada área global puede agrupar métricas por categoría:
  - Métricas Financieras
  - Métricas Comerciales
  - Métricas Operativas
  - KPIs Estratégicos
  - (y las que se necesiten)

### 3. Detección Flexible de Áreas Globales

**Antes:**
```php
$es_global = ($area['slug'] === 'metricas-consolidadas');
```

**Ahora:**
```php
$es_global = ($departamento['tipo'] === 'global');
```

Cualquier área dentro del departamento Global se detecta automáticamente como área global.

### 4. Navegación por Pestañas

Se creó una página de inicio con 3 pestañas organizadas por tipo:

- **🏦 Red de Agencias**: Muestra las 4 agencias con sus áreas
- **🏢 Corporativo**: Muestra departamentos centrales
- **🌍 Global**: Solo visible para super_admin

### 5. Código de Colores

- 🟢 Verde: Agencias
- 🔵 Azul: Corporativos
- 🟣 Morado: Global

---

## 🏗️ Estructura del Sistema

### Jerarquía de Datos

```
Departamento (tipo: agencia | corporativo | global)
  └── Área
       └── Métrica
            ├── Valores (valores_metricas)
            └── Metas (metas_metricas)
```

### Tipos de Departamento en Detalle

#### 🏦 Agencias

**Características:**
- Representan sucursales físicas de la cooperativa
- Cada agencia tiene áreas operativas propias
- Métricas específicas por ubicación geográfica

**Ejemplos creados:**
1. Agencia San José
   - Áreas: Caja, Créditos, Cuentas Nuevas, Atención al Cliente
2. Agencia Heredia
   - Áreas: Caja, Créditos, Cuentas Nuevas
3. Agencia Cartago
   - Áreas: Caja, Créditos, Cuentas Nuevas
4. Agencia Alajuela
   - Áreas: Créditos, Cuentas Nuevas

**Métricas típicas:**
- Colocación de Créditos
- Incremento de Cartera
- Asociados Nuevos
- Tarjetas de Débito emitidas
- Depósitos
- Retiros

#### 🏢 Corporativo

**Características:**
- Departamentos centrales que dan soporte
- Métricas de procesos internos
- No están atados a ubicación geográfica

**Ejemplos:**
- TI Corporativo
  - Áreas: Backend, Frontend, Infraestructura
  - Métricas: Deploys, Uptime, Tickets resueltos
- Recursos Humanos
  - Áreas: Reclutamiento, Capacitación
  - Métricas: Nuevos empleados, Horas de capacitación

#### 🌍 Global

**Características:**
- UN solo departamento especial
- Solo accesible para super_admin
- Contiene múltiples áreas de métricas consolidadas
- Sus métricas son calculadas (suman de otras áreas)

**Áreas globales creadas:**
1. **Métricas Consolidadas** (original)
2. **Métricas Financieras**
   - Total Colocación Créditos
   - Total Incremento Cartera
3. **Métricas Comerciales**
   - Total Asociados Nuevos
   - Total Tarjetas Débito
4. **Métricas Operativas**
   - Comparaciones entre agencias
   - Análisis de correlación
5. **KPIs Estratégicos**
   - Indicadores clave consolidados
   - Cumplimiento de objetivos anuales

---

## 🔄 Flujo de Métricas

### Flujo 1: Métricas Normales (Agencias/Corporativo)

```
┌─────────────────┐
│ Usuario ingresa │
│ valor en área   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Se guarda en    │
│valores_metricas │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Se muestra en   │
│ dashboard del   │
│ área            │
└─────────────────┘
```

**Ejemplo:**
1. Gerente de Créditos San José ingresa: "Colocación Abril 2026 = 45.5 millones"
2. Se guarda en `valores_metricas`
3. Dashboard de "Créditos - San José" muestra: 45.5M

### Flujo 2: Métricas Calculadas Globales

```
┌──────────────────────────────────────────────────┐
│ PASO 1: Definir métricas base en agencias       │
│                                                  │
│ • Colocación Créditos San José                  │
│ • Colocación Créditos Heredia                   │
│ • Colocación Créditos Cartago                   │
│ • Colocación Créditos Alajuela                  │
└────────────────┬─────────────────────────────────┘
                 │
                 ▼
┌──────────────────────────────────────────────────┐
│ PASO 2: Crear área global                       │
│                                                  │
│ • Departamento: Global                          │
│ • Área: "Métricas Financieras"                  │
└────────────────┬─────────────────────────────────┘
                 │
                 ▼
┌──────────────────────────────────────────────────┐
│ PASO 3: Crear métrica calculada                 │
│                                                  │
│ • Nombre: "Total Colocación Créditos"           │
│ • Tipo: Métrica Calculada                       │
│ • Operación: SUMA                               │
└────────────────┬─────────────────────────────────┘
                 │
                 ▼
┌──────────────────────────────────────────────────┐
│ PASO 4: Seleccionar componentes                 │
│                                                  │
│ Sistema muestra métricas de TODAS las agencias  │
│ (NO muestra métricas de otras áreas globales)   │
│                                                  │
│ Se seleccionan:                                 │
│ ✓ Colocación San José                           │
│ ✓ Colocación Heredia                            │
│ ✓ Colocación Cartago                            │
│ ✓ Colocación Alajuela                           │
└────────────────┬─────────────────────────────────┘
                 │
                 ▼
┌──────────────────────────────────────────────────┐
│ PASO 5: Sistema calcula automáticamente         │
│                                                  │
│ Total = SJ + Heredia + Cartago + Alajuela       │
│       = 45.5 + 32.8 + 28.3 + 23.7               │
│       = 130.3 millones                          │
└────────────────┬─────────────────────────────────┘
                 │
                 ▼
┌──────────────────────────────────────────────────┐
│ PASO 6: Se muestra en dashboard global          │
│                                                  │
│ Dashboard "Métricas Financieras" muestra:       │
│ • Total Colocación: 130.3M                      │
│ • Meta: 81.67M/mes                              │
│ • Cumplimiento: 159% ✅                         │
└──────────────────────────────────────────────────┘
```

### Reglas Importantes del Flujo

#### 1. Prevención de Recursión

**Regla:** Un área global NO puede usar métricas de otras áreas globales como componentes.

**Por qué:** Evitar bucles infinitos y dependencias circulares.

**Ejemplo:**
```
❌ INCORRECTO:
   Área Global A usa métrica de Área Global B
   Área Global B usa métrica de Área Global A
   → Bucle infinito

✅ CORRECTO:
   Área Global A usa métricas de Agencia San José
   Área Global A usa métricas de Agencia Heredia
   → Cálculo directo, sin recursión
```

#### 2. Disponibilidad de Métricas

**Para áreas normales (agencia/corporativo):**
- Solo ven métricas de su propia área
- No pueden crear métricas calculadas globales

**Para áreas globales:**
- Ven métricas de TODAS las agencias
- Ven métricas de TODOS los departamentos corporativos
- NO ven métricas de otras áreas globales
- Pueden crear métricas calculadas con cualquier combinación

#### 3. Metas Globales

**Flujo de metas:**
```
Meta Global = Suma de metas de componentes

Ejemplo:
Meta Colocación San José:    20.4M/mes
Meta Colocación Heredia:     20.4M/mes
Meta Colocación Cartago:     20.4M/mes
Meta Colocación Alajuela:    20.5M/mes
                            ──────────
Meta Total Colocación:       81.7M/mes
                            (980M/año)
```

---

## 🔐 Navegación y Permisos

### Vista para Super Admin

**Página de Inicio con 3 Pestañas:**

#### Pestaña 1: 🏦 Red de Agencias (badge: 4)

```
┌─────────────────────────────────────────────┐
│ 🏦 Agencia San José                         │
│ Badge: Agencia | Color: Verde               │
│                                             │
│ Áreas:                                      │
│ • Caja                                      │
│ • Créditos                                  │
│ • Cuentas Nuevas                            │
│ • Atención al Cliente                       │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🏦 Agencia Heredia                          │
│ Badge: Agencia | Color: Verde               │
│                                             │
│ Áreas:                                      │
│ • Caja                                      │
│ • Créditos                                  │
│ • Cuentas Nuevas                            │
└─────────────────────────────────────────────┘

(Similar para Cartago y Alajuela)
```

**Click en cualquier área → Dashboard de esa área específica**

#### Pestaña 2: 🏢 Corporativo (badge: variable)

```
┌─────────────────────────────────────────────┐
│ 🏢 TI Corporativo                           │
│ Badge: Corporativo | Color: Azul            │
│                                             │
│ Áreas:                                      │
│ • Backend                                   │
│ • Frontend                                  │
│ • Infraestructura                           │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🏢 Recursos Humanos                         │
│ Badge: Corporativo | Color: Azul            │
│                                             │
│ Áreas:                                      │
│ • Reclutamiento                             │
│ • Capacitación                              │
└─────────────────────────────────────────────┘

(Otros departamentos corporativos...)
```

#### Pestaña 3: 🌍 Global (badge: "Super Admin")

```
┌─────────────────────────────────────────────┐
│ ⚠️ ÁREA GLOBAL - Solo Super Administrador  │
│                                             │
│ Estas áreas contienen métricas calculadas  │
│ que consolidan datos de todas las agencias │
│ y departamentos.                            │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🌍 Métricas Consolidadas                    │
│ Badge: Global | Color: Morado               │
│ Icono: world                                │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🌍 Métricas Financieras                     │
│ Badge: Global | Color: Morado               │
│ Icono: coin                                 │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🌍 Métricas Comerciales                     │
│ Badge: Global | Color: Morado               │
│ Icono: users                                │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🌍 Métricas Operativas                      │
│ Badge: Global | Color: Morado               │
│ Icono: settings                             │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🌍 KPIs Estratégicos                        │
│ Badge: Global | Color: Morado               │
│ Icono: chart-line                           │
└─────────────────────────────────────────────┘
```

**Click en área → Dashboard ejecutivo con métricas consolidadas**

### Vista para dept_admin / dept_viewer

**NO ven pestañas:**
- Solo ven directamente su departamento asignado
- Si es admin de Agencia Heredia, solo ve Heredia
- NO ven la pestaña "Global"
- Acceso directo denegado si intentan URL de área global

### Matriz de Permisos

| Rol           | Ve Agencias | Ve Corporativo | Ve Global | Puede crear áreas globales |
|---------------|-------------|----------------|-----------|---------------------------|
| super_admin   | ✅ Todas    | ✅ Todos       | ✅ Sí     | ✅ Sí                     |
| dept_admin    | ⚠️ Solo su dept | ⚠️ Solo su dept | ❌ No | ❌ No                     |
| dept_viewer   | ⚠️ Solo su dept | ⚠️ Solo su dept | ❌ No | ❌ No                     |

---

## 💡 Ejemplos Prácticos

### Ejemplo 1: Meta Anual de Colocación

**Contexto:**
La cooperativa tiene meta anual de **980 millones** en colocación de créditos.

**Setup:**

1. **Meta mensual:** 980M ÷ 12 = 81.67M/mes

2. **Distribución por agencia** (proporcional por tamaño):
   - San José (25%): 20.4M/mes
   - Heredia (25%): 20.4M/mes
   - Cartago (25%): 20.4M/mes
   - Alajuela (25%): 20.5M/mes

3. **Configuración en el sistema:**

   a. Cada agencia tiene su métrica "Colocación de Créditos" con su meta
   
   b. Se crea área global "Métricas Financieras"
   
   c. Se crea métrica calculada "Total Colocación Créditos"
      - Componentes: Las 4 métricas de colocación de agencias
      - Operación: SUMA
      - Meta: 81.67M/mes (suma automática de metas de componentes)

**Resultado en Abril 2026:**

**Dashboard Agencia San José:**
- Colocación: 45.5M
- Meta: 20.4M
- Cumplimiento: 223% ✅ (verde)

**Dashboard Agencia Heredia:**
- Colocación: 32.8M
- Meta: 20.4M
- Cumplimiento: 160% ✅ (verde)

**Dashboard Agencia Cartago:**
- Colocación: 28.3M
- Meta: 20.4M
- Cumplimiento: 138% ✅ (verde)

**Dashboard Agencia Alajuela:**
- Colocación: 23.7M
- Meta: 20.5M
- Cumplimiento: 115% ✅ (verde)

**Dashboard Global - Métricas Financieras:**
- Total Colocación: **130.3M**
  - (45.5 + 32.8 + 28.3 + 23.7)
- Meta: 81.67M
- Cumplimiento: **159%** ✅ (verde)
- **Nota:** "Calculado: suma de 4 agencias"

**Análisis ejecutivo:**
- ✅ La cooperativa está superando la meta en un 59%
- ✅ Todas las agencias están cumpliendo individualmente
- ✅ San José lidera con 223% de cumplimiento
- ℹ️ Se podría redistribuir metas para el próximo período

### Ejemplo 2: Captación de Asociados

**Contexto:**
Meta anual de **6,000 asociados nuevos**.

**Setup:**

1. **Meta mensual:** 6,000 ÷ 12 = 500 asociados/mes

2. **Distribución por agencia:**
   - San José: 171/mes (agencia principal)
   - Heredia: 143/mes
   - Cartago: 110/mes
   - Alajuela: 76/mes

3. **Configuración:**
   - Métrica en cada agencia: "Asociados Nuevos"
   - Área global: "Métricas Comerciales"
   - Métrica global: "Total Asociados Nuevos"

**Resultado en Abril 2026:**

**Por agencia:**
- San José: 342 asociados (200% de meta)
- Heredia: 287 asociados (200% de meta)
- Cartago: 221 asociados (200% de meta)
- Alajuela: 0 asociados (no tiene esta métrica configurada)

**Dashboard Global - Métricas Comerciales:**
- Total Asociados: **850**
  - (342 + 287 + 221)
- Meta: 500
- Cumplimiento: **170%** ✅
- Gráfico de barras apiladas muestra contribución por agencia

### Ejemplo 3: Dashboard Ejecutivo Completo

**Usuario:** Gerente General (super_admin)

**Navegación:**
1. Entra al sistema
2. Ve página de inicio con 3 pestañas
3. Click en pestaña "🌍 Global"
4. Click en área "KPIs Estratégicos"

**Dashboard muestra:**

```
┌─────────────────────────────────────────────┐
│ KPIs ESTRATÉGICOS - Abril 2026              │
│                                             │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐     │
│ │💰 130.3M │ │📈 60.8M  │ │👥 850    │     │
│ │Colocación│ │Incremento│ │Asociados │     │
│ │159% ✅   │ │148% ✅   │ │170% ✅   │     │
│ └──────────┘ └──────────┘ └──────────┘     │
│                                             │
│ ┌──────────┐                                │
│ │💳 475    │                                │
│ │Tarjetas  │                                │
│ │132% ✅   │                                │
│ └──────────┘                                │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ Tendencias Generales (12 meses)         │ │
│ │ [Gráfico multi-línea mostrando          │ │
│ │  evolución de las 4 métricas clave]     │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ Comparación: Abril vs Marzo             │ │
│ │ [Gráfico de barras comparativas]        │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

**Gerente puede:**
- Ver todas las métricas clave de un vistazo
- Identificar tendencias
- Comparar períodos
- Click en "Métricas Financieras" para detalle de colocación
- Click en "Métricas Comerciales" para detalle de captación

---

## 🔧 Cambios Técnicos Implementados

### 1. Base de Datos

**Migración:** `008_add_tipo_departamentos.sql`

```sql
-- Campo tipo
ALTER TABLE departamentos 
ADD COLUMN tipo ENUM('agencia', 'corporativo', 'global') 
DEFAULT 'corporativo';

-- Índice para optimizar filtros
ALTER TABLE departamentos 
ADD INDEX idx_tipo (tipo);

-- Marcar departamento Global
UPDATE departamentos 
SET tipo = 'global' 
WHERE nombre = 'Global';
```

### 2. Modelos PHP

**Archivo:** `src/Models/Departamento.php`

Nuevos métodos:
- `getAgencias()`: Retorna departamentos tipo 'agencia'
- `getCorporativos()`: Retorna departamentos tipo 'corporativo'
- `getGlobal()`: Retorna el departamento Global
- `getStatsByTipo()`: Estadísticas agrupadas por tipo

**Archivo:** `src/Models/Area.php`

Modificaciones:
- `getAllWithStats()`: Incluye `d.tipo as departamento_tipo` en SELECT

### 3. Servicios

**Archivo:** `src/Services/MetricaCalculadaService.php`

Nuevos métodos:
- `esAreaGlobal($area_id)`: Detecta si área pertenece a dept Global
- `isAreaGlobal($area_id)`: Versión pública del método anterior

Modificaciones:
- `getMetricasDisponibles()`: Filtra métricas según tipo de área
  - Para áreas normales: solo métricas de su área
  - Para áreas globales: métricas de todas las áreas NO globales

### 4. Vistas

**Archivo nuevo:** `views/home_selector.php`

- Página de inicio con navegación por pestañas
- 3 pestañas: Agencias, Corporativo, Global
- Tarjetas de departamentos con código de colores
- Restricción visual de pestaña Global para super_admin

**Modificaciones en:** `public/index.php`

- Inicialización temprana de modelos
- Detección de vista de home selector
- Carga de home_selector.php cuando no hay área seleccionada

**Modificaciones en:** `public/admin/areas.php`

- Selector de departamento muestra tipo entre paréntesis
- Lista de áreas muestra badges por tipo
- Tooltips informativos

### 5. APIs

**Archivo:** `public/api/get-metricas-by-area.php`

Cambio:
```php
// Antes: detección por slug hardcodeado
$es_area_global = ($area['slug'] === 'metricas-consolidadas');

// Ahora: detección por tipo de departamento
$es_area_global = $calculadaService->isAreaGlobal($area_id);
```

### 6. Seeds de Datos

**Creados:**
- `009_seed_tipos_departamento_demo.sql`: Datos completos de demostración
  - 4 agencias con áreas y métricas
  - 4 áreas globales nuevas
  - 4 métricas calculadas globales
  - 306 valores de prueba
  - Metas basadas en objetivos 2026

- `010_seed_graficos_agencias.sql`: Gráficos para dashboards de agencias
  - 31 gráficos para áreas de agencias
  - Tipos: KPI cards, líneas, barras, sparklines, donuts

- `011_seed_graficos_globales.sql`: Gráficos para dashboards globales
  - 36 gráficos para áreas globales
  - Tipos: KPI con metas, gauges, bullet charts, radar, scatter, comparaciones

**Script runner:**
- `run_seed_demo.php`: Ejecuta seed y muestra verificación

### 7. Tests

**Creados:**
- `tests/DepartamentoTipoTest.php`: Tests de nuevos métodos de Departamento
- `tests/AreaGlobalDetectionTest.php`: Tests de detección de áreas globales
- `tests/MetricasDisponiblesTest.php`: Tests de filtrado de métricas
- `tests/run_all_tests.php`: Suite de pruebas
- `tests/integration_check.php`: Verificación de integración

### 8. Documentación

**Creada:**
- `docs/FEATURE-TIPOS-DEPARTAMENTO.md`: Documentación técnica completa
- `IMPLEMENTATION-SUMMARY.md`: Resumen ejecutivo
- `PLAN-DE-PRUEBAS.md`: 15 casos de prueba detallados
- `docs/TIPOS-DEPARTAMENTO-Y-FLUJO.md`: Este documento

### 9. Correcciones Post-Implementación

**Error de naming convention en tipos de gráficos:**

Problema detectado:
- Archivos PHP usan IDs con guión bajo: `kpi_card`, `gauge_with_goal`
- Seeds usaban guiones: `kpi-card`, `gauge-with-goal`
- Resultado: "Gráfico no encontrado"

Solución aplicada:
- UPDATE masivo en tabla `configuracion_graficos`
- Conversión de guiones a guiones bajos
- 13 tipos de gráficos corregidos
- 125 gráficos actualizados

---

## 📊 Datos de Demostración Creados

### Estructura

**4 Agencias:**
- San José (4 áreas, 6 métricas)
- Heredia (3 áreas, 5 métricas)
- Cartago (3 áreas, 5 métricas)
- Alajuela (2 áreas, 2 métricas)

**5 Áreas Globales:**
- Métricas Consolidadas (0 métricas - preexistente)
- Métricas Financieras (2 métricas calculadas)
- Métricas Comerciales (2 métricas calculadas)
- Métricas Operativas (0 métricas - para análisis)
- KPIs Estratégicos (0 métricas - usa de otras áreas)

**Métricas Globales Calculadas:**

1. **Total Colocación Créditos**
   - Suma: SJ + Heredia + Cartago + Alajuela
   - Valor actual: 130.3 millones
   - Meta: 81.67M/mes (980M/año)
   - Cumplimiento: 159% ✅

2. **Total Incremento Cartera**
   - Suma: SJ + Heredia + Cartago + Alajuela
   - Valor actual: 60.8 millones
   - Meta: 40.83M/mes (490M/año)
   - Cumplimiento: 148% ✅

3. **Total Asociados Nuevos**
   - Suma: SJ + Heredia + Cartago
   - Valor actual: 850 asociados
   - Meta: 500/mes (6,000/año)
   - Cumplimiento: 170% ✅

4. **Total Tarjetas Débito**
   - Suma: SJ + Heredia + Cartago
   - Valor actual: 475 tarjetas
   - Meta: 358/mes (4,300/año)
   - Cumplimiento: 132% ✅

### Gráficos Creados

**Por tipo de departamento:**
- Agencias: 31 gráficos
- Corporativo: 58 gráficos (preexistentes)
- Global: 36 gráficos

**Total: 125 gráficos activos**

**Tipos de gráficos usados:**
- kpi_card: 43
- bar: 16
- line: 12
- donut: 10
- kpi_with_goal: 5
- gauge_with_goal: 4
- multi_line: 4
- line_with_goal: 3
- bullet: 3
- sparkline: 3
- Y 14 tipos más...

---

## 🎯 Ventajas del Nuevo Sistema

### Para la Organización

1. **Claridad estructural**
   - Refleja la estructura real de la cooperativa
   - Separación clara entre agencias y departamentos centrales
   - Vista ejecutiva consolidada

2. **Escalabilidad**
   - Fácil agregar nuevas agencias
   - Fácil agregar áreas globales según necesidad
   - No hay límites hardcodeados

3. **Flexibilidad en reportes**
   - Múltiples dashboards ejecutivos
   - Agrupación por categorías
   - Métricas calculadas personalizables

### Para los Usuarios

1. **Navegación intuitiva**
   - Pestañas claras por tipo
   - Colores distintivos
   - Búsqueda visual rápida

2. **Permisos claros**
   - Super admin ve todo organizado
   - Otros roles solo ven lo relevante
   - No hay confusión de accesos

3. **Dashboards especializados**
   - Agencias ven métricas operativas
   - Ejecutivos ven consolidados
   - Cada quien ve lo que necesita

### Para el Sistema

1. **Mantenibilidad**
   - Código más limpio y organizado
   - Menos hardcoding
   - Más fácil de extender

2. **Performance**
   - Índices en campo tipo
   - Queries optimizadas
   - Filtrado eficiente

3. **Seguridad**
   - Restricciones claras por tipo
   - Validación en múltiples capas
   - Prevención de recursión

---

## 📝 Casos de Uso

### Caso 1: Agregar Nueva Agencia

**Escenario:** Se abre Agencia Limón

**Pasos:**
1. Admin > Departamentos > Nueva
2. Nombre: "Agencia Limón"
3. Tipo: **agencia**
4. Color: #10b981 (verde)
5. Guardar

6. Admin > Áreas > Nueva
7. Crear áreas operativas:
   - Caja
   - Créditos
   - Cuentas Nuevas

8. Admin > Métricas > Nueva
9. Para cada área, crear métricas:
   - Colocación de Créditos
   - Asociados Nuevos
   - etc.

10. Admin > Métricas > Editar métricas globales
11. En "Total Colocación Créditos":
    - Agregar componente: Colocación Limón
    - Meta se suma automáticamente

**Resultado:**
- Agencia Limón aparece en pestaña "Red de Agencias"
- Sus métricas se incluyen en totales globales
- Dashboards ejecutivos se actualizan automáticamente

### Caso 2: Crear Nueva Área Global

**Escenario:** Gerencia quiere dashboard de "Satisfacción del Cliente"

**Pasos:**
1. Admin > Áreas > Nueva
2. Departamento: **Global**
3. Nombre: "Satisfacción del Cliente"
4. Icono: mood-smile
5. Color: #8b5cf6 (morado)
6. Guardar

7. Admin > Métricas > Nueva Métrica Calculada
8. Área: Satisfacción del Cliente
9. Nombre: "NPS Promedio"
10. Operación: PROMEDIO
11. Componentes: Seleccionar NPS de cada agencia
12. Guardar

13. Admin > Gráficos > Nueva
14. Crear visualizaciones:
    - Gauge con promedio
    - Radar comparison entre agencias
    - Tendencia temporal

**Resultado:**
- Nueva área aparece en pestaña Global
- Dashboard con métricas de satisfacción
- Vista consolidada de todas las agencias

### Caso 3: Análisis Ejecutivo Mensual

**Escenario:** Junta directiva requiere reporte mensual

**Flujo:**
1. Gerente General (super_admin) ingresa al sistema
2. Navega a pestaña "🌍 Global"
3. Revisa cada área:

   a. **KPIs Estratégicos**
      - Vista rápida de 4 indicadores clave
      - Todos en verde (cumpliendo)
   
   b. **Métricas Financieras**
      - Colocación: 159% de meta ✅
      - Incremento: 148% de meta ✅
      - Gráfico de evolución vs meta
      - Ranking de agencias
   
   c. **Métricas Comerciales**
      - Asociados: 170% de meta ✅
      - Tarjetas: 132% de meta ✅
      - Comparación entre agencias
   
   d. **Métricas Operativas**
      - Multi-línea: tendencias por agencia
      - Radar: comparación multi-dimensional
      - Scatter: correlaciones

4. Click en "Exportar" (funcionalidad futura)
5. Genera PDF con todos los dashboards
6. Presenta en junta directiva

**Valor:**
- Toda la información consolidada en un lugar
- Vista ejecutiva sin detalle operativo
- Comparaciones claras
- Identificación rápida de tendencias

---

## 🚀 Próximos Pasos Sugeridos

### Mejoras Futuras

1. **Exportación de Dashboards**
   - PDF de dashboards globales
   - Excel con datos consolidados
   - Reportes programados

2. **Alertas Automáticas**
   - Notificación si agencia no cumple meta
   - Alertas de tendencias negativas
   - Recordatorios de carga de datos

3. **Más Tipos de Métricas Calculadas**
   - Actualmente: SUMA, PROMEDIO
   - Agregar: MÁXIMO, MÍNIMO, MEDIANA
   - Fórmulas personalizadas

4. **Comparaciones Temporales**
   - Año vs año
   - Mes vs mes del año anterior
   - Tendencias estacionales

5. **Drill-down Interactivo**
   - Click en métrica global → ver desglose por agencia
   - Click en agencia → ver detalle de área
   - Navegación fluida multi-nivel

6. **Metas Dinámicas**
   - Ajuste de metas durante el año
   - Proyecciones automáticas
   - Simulaciones de escenarios

---

## 📞 Soporte y Referencias

### Archivos Relacionados

**Documentación:**
- `docs/FEATURE-TIPOS-DEPARTAMENTO.md` - Documentación técnica
- `IMPLEMENTATION-SUMMARY.md` - Resumen ejecutivo
- `PLAN-DE-PRUEBAS.md` - Plan de pruebas completo
- `docs/TIPOS-DEPARTAMENTO-Y-FLUJO.md` - Este documento

**Código:**
- `src/Models/Departamento.php` - Modelo con métodos por tipo
- `src/Models/Area.php` - Modelo actualizado
- `src/Services/MetricaCalculadaService.php` - Lógica de áreas globales
- `views/home_selector.php` - Vista de navegación por pestañas

**Base de Datos:**
- `database/migrations/008_add_tipo_departamentos.sql` - Migración
- `database/seeds/009_seed_tipos_departamento_demo.sql` - Datos demo
- `database/seeds/010_seed_graficos_agencias.sql` - Gráficos agencias
- `database/seeds/011_seed_graficos_globales.sql` - Gráficos globales

**Tests:**
- `tests/DepartamentoTipoTest.php`
- `tests/AreaGlobalDetectionTest.php`
- `tests/MetricasDisponiblesTest.php`

### Comandos Útiles

**Ejecutar migración:**
```bash
mysql -u root metricas_sistema < database/migrations/008_add_tipo_departamentos.sql
```

**Cargar datos demo:**
```bash
php run_seed_demo.php
```

**Ejecutar tests:**
```bash
php tests/run_all_tests.php
```

**Verificar estructura:**
```bash
php tests/integration_check.php
```

### Consultas SQL Útiles

**Ver departamentos por tipo:**
```sql
SELECT tipo, COUNT(*) as total
FROM departamentos
WHERE activo = 1
GROUP BY tipo;
```

**Ver áreas globales:**
```sql
SELECT a.nombre, COUNT(m.id) as metricas
FROM areas a
JOIN departamentos d ON a.departamento_id = d.id
LEFT JOIN metricas m ON a.id = m.area_id
WHERE d.tipo = 'global' AND a.activo = 1
GROUP BY a.id;
```

**Ver métricas calculadas globales:**
```sql
SELECT m.nombre, COUNT(mc.metrica_componente_id) as componentes
FROM metricas m
JOIN areas a ON m.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
LEFT JOIN metricas_componentes mc ON m.id = mc.metrica_id
WHERE d.tipo = 'global' AND m.es_calculada = 1
GROUP BY m.id;
```

---

**Fecha de Creación:** 28 de Abril 2026  
**Última Actualización:** 28 de Abril 2026  
**Versión del Sistema:** 2.1  
**Estado:** ✅ Implementado y en Producción
