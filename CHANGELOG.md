# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.0.0] - 2026-04-21

### ✨ Agregado

#### API REST
- **Sistema completo de API REST** con autenticación por Bearer Token
- **Endpoints RESTful** para: métricas, valores, períodos, áreas, departamentos y metas
- **Documentación interactiva** en HTML accesible en `/metricas/api`
- **ApiAuthService** para generación y validación de tokens SHA256
- **Tabla `api_tokens`** con tracking de uso y expiración
- **Panel de gestión de tokens** en perfil de usuario
- **Respuestas estandarizadas** con formato JSON consistente
- **Validación de permisos** integrada en todos los endpoints
- **CORS habilitado** para integración cross-origin

#### Sistema de Exportación
- **ExportService** para generación de exportaciones
- **Exportación a CSV** con UTF-8 BOM para Excel
- **Exportación a PDF** via HTML imprimible con CSS @media print
- **Modal de opciones de exportación** con configuración de períodos (6/12/24/36 meses)
- **Exportación desde dashboard** (todas las métricas visibles)
- **Exportación desde admin** (tabla filtrada de métricas)
- **Botones de exportación** integrados en UI
- **Script export.js** con funcionalidad client-side

#### Nuevos Gráficos
- **Area Chart** - Visualización de volumen acumulado
- **Sparkline** - Mini-gráficos compactos para tablas
- **Bullet Chart** - Comparación con rangos de desempeño
- **Multi-line Chart** - Comparación de 2-5 métricas
- **Stacked Area Chart** - Composición de partes del total
- **Mixed Chart** - Combinación de barras y líneas
- **Scatter Chart** - Análisis de correlación
- **Period Comparison** - Comparación entre 2 períodos específicos (3 estilos: cards, bars, gauge)

Total: **8 nuevos tipos de gráficos** (de 13 a 21 tipos)

#### Optimización de Performance
- **Sistema de caché** con TTL configurable (`src/Utils/Cache.php`)
- **Cache::remember()** para queries frecuentes con callback
- **CLI cache-manager.php** para gestión (--stats, --clean, --flush)
- **QueryOptimizer::batchLoadValores()** para prevenir N+1 queries
- **Lazy loading de gráficos** con IntersectionObserver API
- **Script lazy-charts.js** para carga bajo demanda
- **Índices de base de datos** optimizados (migración 004)

#### Documentación
- **README.md** completo (3000+ líneas) con:
  - Guía de instalación paso a paso
  - Arquitectura del sistema
  - Guía de usuario por rol
  - Guía completa de administrador
  - Catálogo de gráficos con casos de uso
  - Guía de desarrollo y extensión
  - Solución de problemas
  - Referencia técnica completa
- **docs/API_REFERENCE.md** con documentación detallada de API
- **docs/QUICKSTART.md** para configuración en 15 minutos
- **Ejemplos de integración** en Python, Node.js, Excel, Google Sheets

#### Otros
- **Atributo data-metrica-id** en gráficos para exportación
- **Detección automática de múltiples métricas** en configuración de gráficos
- **SVG previews** para gráficos con meta en catálogo
- **Guías de uso** para todos los tipos de gráficos

### 🔧 Cambiado

- **ChartRegistry** ahora registra automáticamente todos los gráficos
- **Configuración de gráficos** soporta múltiples formatos (metrica_id, metricas[], metrica_1..5)
- **Respuestas de API** usan formato consistente con `success` y `data`
- **ExportModule** global en JavaScript disponible en window
- **Dashboard** incluye export.js automáticamente

### 🐛 Corregido

- **Syntax error** en `line-with-goal.php:68` - faltaba punto y coma después de heredoc
- **Syntax error** en `period-comparison.php:159` - interpolación en string de comilla simple
- **Syntax error** en `kpi-with-goal.php:216` - ternario dentro de heredoc
- **Catálogo de gráficos** faltaban ejemplos visuales y sugerencias para gráficos con meta
- **Modal de exportación** manejo correcto de data-metrica-id con múltiples IDs

### 🗄️ Base de Datos

- **Migración 005** - Tabla `api_tokens` con índices
- **Migración 004** - Índices de performance optimizados
- **Tabla valores_metricas** - Constraint UNIQUE(metrica_id, periodo_id)

### 📦 Dependencias

No se agregaron dependencias externas. El sistema usa:
- PHP nativo para CSV
- HTML + CSS para PDF imprimible
- Sin librerías de terceros (TCPDF, PhpSpreadsheet evitadas)

---

## [1.0.0] - 2026-01-15

### ✨ Agregado - Lanzamiento Inicial

#### Core del Sistema
- **Arquitectura multi-tenant** con departamentos → áreas → métricas
- **Sistema de usuarios** con 3 roles: Super Admin, Admin Depto, Visualizador
- **AuthMiddleware** para protección de rutas
- **PermissionMiddleware** para control de acceso
- **PermissionService** para gestión de permisos granulares

#### Gestión de Métricas
- **CRUD completo de métricas** con:
  - Tipos de valor: numérico y decimal
  - Unidades personalizables
  - Iconos de Tabler
  - Métricas calculadas
  - Sistema de metas
- **Registro de valores** por período
- **Historial completo** de cambios
- **Soft delete** (campo `activo`)

#### Sistema de Metas
- **Definición de objetivos** con:
  - Valor objetivo
  - Rango de períodos
  - Sentido (mayor/menor es mejor)
- **Tracking de cumplimiento**
- **Visualización de progreso** en gráficos

#### Dashboards Interactivos
- **GridStack** para layout drag-and-drop
- **Modo edición** para administradores
- **Guardado automático** de posiciones
- **Responsive design** (desktop, tablet, móvil)
- **Navegación por pestañas** de áreas
- **Selector de departamento** (Super Admin)
- **Selector de período** global

#### Gráficos (13 tipos iniciales)
- **Line Chart** - Evolución temporal
- **Bar Chart** - Comparación por período
- **Donut Chart** - Composición actual
- **Gauge Chart** - Medidor circular
- **Progress Bar** - Barra de progreso
- **KPI Card** - Tarjeta de valor destacado
- **Line with Goal** - Línea vs objetivo
- **Gauge with Goal** - Medidor con meta
- **KPI with Goal** - KPI con cumplimiento
- **Multi-bar Chart** - Barras agrupadas
- **Comparison Chart** - Comparación de métricas
- **Mixed Chart básico** - Datos heterogéneos
- **Donut con meta** - Composición vs objetivo

#### Administración
- **Gestión de departamentos** con colores e iconos
- **Gestión de áreas** por departamento
- **Gestión de métricas** con filtros
- **Gestión de períodos** (mensual, trimestral)
- **Gestión de usuarios** con asignación de permisos
- **Gestión de metas** por métrica
- **Registro de valores** con notas

#### UI/UX
- **Tabler framework** para interfaz moderna
- **Tabler Icons** (800+ iconos)
- **Tema claro/oscuro** con toggle
- **Flash messages** con auto-hide
- **Breadcrumbs** para navegación
- **Tooltips** informativos
- **Modals** para formularios
- **Dropdowns** para filtros

#### Base de Datos
- **Schema completo** con 11 tablas:
  - departamentos
  - areas
  - metricas
  - valores_metricas
  - metas_metricas
  - periodos
  - usuarios
  - usuarios_areas
  - graficos
  - plantillas_dashboard (futuro)
  - sesiones_usuario (futuro)
- **Foreign keys** con ON DELETE CASCADE
- **Índices** en campos frecuentes
- **TIMESTAMPS** automáticos
- **Constraints** de integridad

#### Seguridad
- **Autenticación** por sesión PHP
- **Passwords hasheados** con bcrypt
- **Sanitización** de inputs
- **Prepared statements** en todas las queries
- **Validación** de permisos en cada acción
- **HTTPS recomendado** para producción

#### Utilidades
- **ChartRegistry** para auto-descubrimiento de gráficos
- **Model base class** para operaciones CRUD
- **Helper functions** globales
- **Autoload** con Composer PSR-4

### 🗄️ Base de Datos Inicial

**Schema:** `database/schema.sql`

**Migraciones:**
- 001 - Plantillas de dashboard
- 002 - Meta tracking
- 003 - Nuevos tipos de gráficos

### 📦 Dependencias

```json
{
  "require": {
    "php": ">=8.1"
  }
}
```

**Frontend:**
- Tabler Core 1.0.0-beta17 (CDN)
- Tabler Icons (CDN)
- GridStack 9.0.0 (CDN)
- ApexCharts 3.x (CDN)

---

## [Unreleased]

### 🔮 Planificado para v2.1

- [ ] **Rate limiting** para API (1000 req/hora)
- [ ] **Importación masiva** de valores via CSV
- [ ] **Webhooks** para notificaciones
- [ ] **Alertas automáticas** cuando se incumple meta
- [ ] **Reportes programados** por email
- [ ] **Plantillas de dashboard** reutilizables
- [ ] **Calculadora de métricas** para métricas calculadas
- [ ] **Audit log** completo de cambios
- [ ] **Versioning** de métricas
- [ ] **Comentarios** en valores de métricas
- [ ] **Etiquetas/tags** para métricas
- [ ] **Búsqueda avanzada** con filtros
- [ ] **Dark mode mejorado** con más temas

### 🔮 Planificado para v3.0

- [ ] **Machine Learning** para predicciones
- [ ] **Detección de anomalías** automática
- [ ] **Sugerencias inteligentes** de metas
- [ ] **Análisis de correlaciones** automático
- [ ] **Dashboard mobile app** (PWA)
- [ ] **Integración con BI tools** (Power BI, Tableau)
- [ ] **SSO/SAML** para enterprise
- [ ] **Multi-idioma** completo

---

## Formato del Changelog

### Tipos de cambios

- **✨ Agregado** - Nuevas características
- **🔧 Cambiado** - Cambios en funcionalidad existente
- **❌ Deprecado** - Características que se eliminarán
- **🗑️ Eliminado** - Características eliminadas
- **🐛 Corregido** - Bug fixes
- **🔒 Seguridad** - Vulnerabilidades corregidas
- **🗄️ Base de Datos** - Cambios en schema
- **📦 Dependencias** - Actualizaciones de librerías
- **📚 Documentación** - Cambios en docs

---

## Versionado

Este proyecto usa [Semantic Versioning](https://semver.org/lang/es/):

**MAJOR.MINOR.PATCH**

- **MAJOR** - Cambios incompatibles de API
- **MINOR** - Nueva funcionalidad compatible
- **PATCH** - Bug fixes compatibles

Ejemplo: `2.0.0`
- 2 = Segunda versión mayor (breaking changes)
- 0 = Sin nuevas features desde 2.0
- 0 = Sin patches desde 2.0

---

## Enlaces

- [Repositorio](enlace-github)
- [Issues](enlace-issues)
- [Documentación](README.md)
- [API Reference](docs/API_REFERENCE.md)
- [Guía Rápida](docs/QUICKSTART.md)

---

**Mantenido por:** [Tu Empresa]  
**Última actualización:** 2026-04-21
