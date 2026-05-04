# Sistema de Métricas Empresariales

> Sistema integral para gestión, visualización y análisis de métricas departamentales con dashboards interactivos, API REST y control de acceso basado en roles.

**Versión:** 2.0  
**Última actualización:** Abril 2026

---

## Tabla de Contenidos

1. [Características Principales](#características-principales)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Instalación](#instalación)
4. [Arquitectura del Sistema](#arquitectura-del-sistema)
5. [Guía de Usuario](#guía-de-usuario)
6. [Guía de Administrador](#guía-de-administrador)
7. [API REST](#api-rest)
8. [Catálogo de Gráficos](#catálogo-de-gráficos)
9. [Desarrollo y Extensión](#desarrollo-y-extensión)
10. [Solución de Problemas](#solución-de-problemas)
11. [Referencia Técnica](#referencia-técnica)

---

## Características Principales

### 🎯 **Gestión de Métricas**
- Definición de métricas por área y departamento
- Tipos de valor: numérico entero o decimal
- Metas y objetivos configurables
- Historial completo de valores por período

### 📊 **Dashboards Interactivos**
- 21+ tipos de gráficos especializados
- Drag & drop para personalizar layout (GridStack)
- Modo edición para administradores
- Lazy loading para optimización de carga
- Responsive design (móvil, tablet, desktop)

### 👥 **Control de Acceso**
- 3 roles: Super Admin, Admin Departamento, Visualizador
- Permisos granulares por departamento/área
- Multi-tenancy completo

### 🔌 **API REST**
- Autenticación por Bearer Token (SHA256)
- Endpoints RESTful para todas las entidades
- Documentación interactiva incluida
- Rate limiting (futuro)

### 📥 **Exportación**
- Excel (CSV con UTF-8 BOM)
- PDF (HTML imprimible)
- Exportación desde dashboard o admin
- Configuración de períodos a incluir

### ⚡ **Optimización de Performance**
- Sistema de caché con TTL configurable
- Batch loading (prevención de N+1 queries)
- Lazy loading de gráficos
- CLI de gestión de caché

---

## Requisitos del Sistema

### **Servidor**
- PHP 8.1 o superior
- MySQL 5.7+ / MariaDB 10.3+
- Apache 2.4+ o Nginx
- Extensiones PHP requeridas:
  - PDO y pdo_mysql
  - mbstring
  - json
  - openssl

### **Cliente**
- Navegador moderno (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- JavaScript habilitado
- Resolución mínima: 1024x768

### **Desarrollo (opcional)**
- Composer 2.x
- Git
- Node.js 16+ (para herramientas de desarrollo)

---

## 🚀 Instalación Rápida

**Para instrucciones completas de despliegue, ver [DEPLOYMENT.md](DEPLOYMENT.md)**

### Resumen:

1. **Clonar proyecto**
2. **Crear archivo `.env`** desde `.env.example`
3. **Configurar base de datos** en `.env`
4. **Instalar dependencias**: `composer install`
5. **Importar BD**: `database/metricas_sistema.sql`

**Única configuración necesaria:** Editar `.env` con tus credenciales y ruta base.

### **2. Instalar Dependencias (obsoleto - ver DEPLOYMENT.md)**

```bash
composer install
```

### **3. Configurar Base de Datos**

Crear archivo `config/database.php`:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'metricas_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### **4. Ejecutar Migraciones**

```bash
# Opción 1: Via MySQL CLI
mysql -u root -p metricas_db < database/schema.sql

# Opción 2: Via phpMyAdmin
# Importar archivo: database/schema.sql

# Ejecutar migraciones adicionales
mysql -u root -p metricas_db < database/migrations/001_create_plantillas.sql
mysql -u root -p metricas_db < database/migrations/002_add_meta_tracking.sql
mysql -u root -p metricas_db < database/migrations/003_add_chart_types.sql
mysql -u root -p metricas_db < database/migrations/004_performance_indexes.sql
mysql -u root -p metricas_db < database/migrations/005_create_api_tokens.sql
```

### **5. Configurar Apache**

Agregar a `httpd.conf` o crear archivo `.htaccess` en `/public`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/(.*)$ /metricas/api/index.php [QSA,L]
</IfModule>
```

### **6. Permisos de Carpetas**

```bash
# Linux/Mac
chmod -R 755 storage/
chmod -R 755 storage/cache/

# Windows
# Dar permisos de escritura a IIS_IUSRS o NETWORK SERVICE
```

### **7. Usuario Inicial**

El sistema crea automáticamente un super admin:

- **Usuario:** admin@sistema.com
- **Contraseña:** admin123

**⚠️ Cambiar la contraseña inmediatamente después del primer acceso**

### **8. Verificar Instalación**

Acceder a:
- **Dashboard:** `http://localhost/metricas/public/index.php`
- **API Docs:** `http://localhost/metricas/api`

---

## Arquitectura del Sistema

### **Estructura de Carpetas**

```
metricas/
├── api/                          # API REST
│   ├── endpoints/                # Endpoints por recurso
│   │   ├── areas.php
│   │   ├── departamentos.php
│   │   ├── metricas.php
│   │   ├── metas.php
│   │   ├── periodos.php
│   │   └── valores.php
│   ├── docs.php                  # Documentación HTML
│   └── index.php                 # Router principal
│
├── config/
│   └── database.php              # Configuración de BD
│
├── database/
│   ├── migrations/               # Migraciones SQL
│   └── schema.sql                # Schema base
│
├── public/                       # Raíz web (document root)
│   ├── admin/                    # Panel de administración
│   │   ├── api-tokens.php        # Gestión de tokens API
│   │   ├── areas.php
│   │   ├── departamentos.php
│   │   ├── export.php            # Controlador de exportación
│   │   ├── graficos.php          # Catálogo de gráficos
│   │   ├── metas.php
│   │   ├── metricas.php
│   │   ├── periodos.php
│   │   └── usuarios.php
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   │       ├── export.js         # Módulo de exportación
│   │       └── lazy-charts.js    # Lazy loading
│   ├── index.php                 # Dashboard principal
│   ├── login.php
│   ├── logout.php
│   └── perfil.php
│
├── src/                          # Código fuente PHP
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── PermissionMiddleware.php
│   ├── Models/                   # Modelos de datos
│   │   ├── Area.php
│   │   ├── Departamento.php
│   │   ├── Grafico.php
│   │   ├── Meta.php
│   │   ├── Metrica.php
│   │   ├── Model.php             # Clase base
│   │   ├── Periodo.php
│   │   ├── Usuario.php
│   │   └── ValorMetrica.php
│   ├── Services/
│   │   ├── ApiAuthService.php    # Autenticación API
│   │   ├── ExportService.php     # Exportación PDF/Excel
│   │   └── PermissionService.php # Control de permisos
│   └── Utils/
│       ├── Cache.php             # Sistema de caché
│       ├── ChartRegistry.php     # Registro de gráficos
│       └── QueryOptimizer.php    # Optimización de queries
│
├── storage/
│   └── cache/                    # Archivos de caché
│
├── vendor/                       # Dependencias Composer
│
├── views/                        # Vistas y componentes
│   ├── components/
│   │   └── charts/               # 21 tipos de gráficos
│   │       ├── area.php
│   │       ├── bar.php
│   │       ├── bullet.php
│   │       ├── donut.php
│   │       ├── gauge-with-goal.php
│   │       ├── gauge.php
│   │       ├── kpi-card.php
│   │       ├── kpi-with-goal.php
│   │       ├── line-with-goal.php
│   │       ├── line.php
│   │       ├── mixed.php
│   │       ├── multi-bar.php
│   │       ├── multi-line.php
│   │       ├── period-comparison.php
│   │       ├── progress.php
│   │       ├── scatter.php
│   │       ├── sparkline.php
│   │       └── stacked-area.php
│   └── layouts/
│       ├── footer.php
│       └── header.php
│
├── cache-manager.php             # CLI para gestión de caché
├── composer.json
└── README.md                     # Este archivo
```

### **Modelo de Datos**

```
departamentos (1) ──→ (N) areas
areas (1) ──→ (N) metricas
metricas (1) ──→ (N) valores_metricas
metricas (1) ──→ (N) metas_metricas
metricas (1) ──→ (N) graficos
periodos (1) ──→ (N) valores_metricas
usuarios (1) ──→ (N) api_tokens
```

### **Flujo de Datos**

```
Usuario → Login → Middleware Auth → Dashboard
                                   ↓
                          PermissionService
                                   ↓
                    Áreas permitidas → Métricas
                                   ↓
                          ChartRegistry → Renderizar gráficos
                                   ↓
                          Cache (si disponible)
                                   ↓
                          ValorMetrica → Base de datos
```

---

## Guía de Usuario

### **Roles y Permisos**

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Super Admin** | Control total del sistema | Crear/editar todo, gestionar usuarios, acceso a todos los departamentos |
| **Admin Departamento** | Administrador de un departamento específico | Crear/editar áreas y métricas de su departamento, gestionar usuarios del departamento |
| **Visualizador** | Usuario de consulta | Solo visualizar dashboards de áreas asignadas |

### **Acceso al Sistema**

1. Navegar a `http://[servidor]/metricas/public/login.php`
2. Ingresar credenciales
3. Acceso automático al dashboard principal

### **Navegación del Dashboard**

#### **Selector de Departamento** (Solo Super Admin)
- Dropdown en la parte superior
- Permite filtrar áreas por departamento
- Opción "Todos los Departamentos" para vista global

#### **Pestañas de Áreas**
- Navegación horizontal
- Click en área para cambiar dashboard
- Badge muestra departamento (en vista global)

#### **Selector de Período**
- Dropdown en el header
- Últimos 12 períodos disponibles
- Cambia datos de todos los gráficos

#### **Botón Exportar**
- Abre modal con opciones:
  - **Formato:** CSV (Excel) o HTML (PDF)
  - **Períodos:** 6, 12, 24 o 36 meses
- Exporta todas las métricas visibles en dashboard

### **Interpretación de Gráficos**

#### **Códigos de Color**
- 🟢 **Verde:** Meta cumplida / Tendencia positiva
- 🟡 **Amarillo:** Cerca de la meta (80-100%)
- 🔴 **Rojo:** Por debajo de meta / Tendencia negativa
- 🔵 **Azul:** Informativo / Sin meta definida

#### **Indicadores de Tendencia**
- ↗️ **Flecha arriba:** Incremento vs período anterior
- ↘️ **Flecha abajo:** Decremento vs período anterior
- → **Flecha horizontal:** Sin cambio significativo (<5%)

### **Personalización del Dashboard** (Solo Admins)

1. Click en **"Modo Edición"**
2. **Agregar gráfico:**
   - Click en "Agregar Gráfico"
   - Seleccionar tipo de gráfico del catálogo
   - Configurar métrica(s) y opciones
   - Guardar
3. **Reorganizar:** Arrastrar y soltar gráficos
4. **Redimensionar:** Arrastrar desde esquina inferior derecha
5. **Eliminar:** Click en ícono de papelera
6. Click en **"Guardar y Salir"**

---

## Guía de Administrador

### **Gestión de Departamentos**

**Ubicación:** `Administración → Departamentos`

#### **Crear Departamento**
1. Click en "Nuevo Departamento"
2. Completar formulario:
   - **Nombre:** Nombre del departamento
   - **Descripción:** (opcional)
   - **Ícono:** Seleccionar de Tabler Icons
   - **Color:** Color para identificación visual
3. Guardar

#### **Editar/Eliminar**
- Click en ícono de lápiz para editar
- Click en ícono de papelera para desactivar

### **Gestión de Áreas**

**Ubicación:** `Administración → Áreas`

#### **Crear Área**
1. Seleccionar departamento
2. Click en "Nueva Área"
3. Completar formulario:
   - **Nombre:** Nombre del área
   - **Departamento:** Asignación
   - **Descripción:** (opcional)
   - **Ícono:** Tabler Icon
   - **Color:** Heredado del departamento
   - **Orden:** Posición en navegación
4. Guardar

### **Gestión de Métricas**

**Ubicación:** `Administración → Métricas`

#### **Crear Métrica**
1. Filtrar por área (opcional)
2. Click en "Nueva Métrica"
3. Completar formulario:
   - **Nombre:** Nombre descriptivo
   - **Área:** Área a la que pertenece
   - **Descripción:** Explicación de qué mide
   - **Unidad:** ej. "usuarios", "%", "horas"
   - **Tipo de valor:** Numérico o Decimal
   - **Ícono:** Tabler Icon
   - **Es calculada:** Marcar si se calcula automáticamente
   - **Tiene meta:** Marcar si se definirán objetivos
4. Guardar

#### **Definir Metas**
1. Click en "Metas" en la fila de la métrica
2. Click en "Nueva Meta"
3. Completar:
   - **Valor objetivo:** Número a alcanzar
   - **Período inicio/fin:** Rango de vigencia
   - **Sentido:** "Mayor es mejor" o "Menor es mejor"
4. Guardar

**Tipos de sentido:**
- **Mayor es mejor:** ej. ventas, clientes, producción
- **Menor es mejor:** ej. errores, tiempo de respuesta, costos

### **Gestión de Períodos**

**Ubicación:** `Administración → Períodos`

Los períodos representan intervalos de tiempo para medición (mensual, trimestral, etc.)

#### **Crear Período**
1. Click en "Nuevo Período"
2. Completar:
   - **Nombre:** ej. "Enero 2026", "Q1 2026"
   - **Ejercicio:** Año fiscal
   - **Período:** Número de mes/trimestre
   - **Fecha inicio/fin:** Rango exacto
   - **Activo:** Marcar para activar
   - **Es actual:** Marcar como período predeterminado
3. Guardar

### **Gestión de Usuarios**

**Ubicación:** `Administración → Usuarios`

#### **Crear Usuario**
1. Click en "Nuevo Usuario"
2. Completar formulario:
   - **Nombre:** Nombre completo
   - **Email:** Email corporativo (usado para login)
   - **Contraseña:** Mínimo 8 caracteres
   - **Rol:** Super Admin / Admin Depto / Visualizador
   - **Departamento:** Asignación (si aplica)
   - **Áreas:** Áreas a las que tiene acceso
3. Guardar

#### **Permisos por Rol**

**Super Admin:**
- Asignación: Sin departamento específico
- Acceso: Todos los departamentos y áreas
- Puede: Crear/editar cualquier entidad

**Admin Departamento:**
- Asignación: Un departamento específico
- Acceso: Solo áreas de su departamento
- Puede: Crear/editar en su departamento

**Visualizador:**
- Asignación: Áreas específicas
- Acceso: Solo áreas asignadas
- Puede: Solo visualizar dashboards

### **Carga de Valores**

**Ubicación:** `Administración → Métricas → [Métrica] → Valores`

#### **Registrar Valor**
1. Seleccionar métrica
2. Click en "Registrar Valor"
3. Completar:
   - **Período:** Período al que corresponde
   - **Valor:** Número medido
   - **Nota:** (opcional) Contexto o aclaración
4. Guardar

#### **Importación Masiva** (Futuro)
- CSV con formato: `metrica_id,periodo_id,valor,nota`
- Validación automática de tipos
- Reporte de errores

### **Configuración de Gráficos**

**Ubicación:** Dashboard → Modo Edición → Agregar Gráfico

Ver [Catálogo de Gráficos](#catálogo-de-gráficos) para tipos disponibles y casos de uso.

### **Exportación desde Admin**

En `Administración → Métricas`:
1. Filtrar métricas (por área, activas/todas)
2. Click en "Exportar"
3. Seleccionar opciones
4. Exporta tabla actual con histórico

---

## API REST

### **Autenticación**

La API usa autenticación por **Bearer Token**.

#### **Generar Token**

**Ubicación:** `Panel de Usuario → Tokens de API`

1. Click en "Generar Token"
2. Ingresar:
   - **Nombre:** Identificador (ej. "App Móvil")
   - **Expiración:** 30/90/365/730 días
3. **⚠️ Copiar token inmediatamente** (no se volverá a mostrar)

#### **Uso del Token**

```bash
curl -H "Authorization: Bearer {tu-token-aquí}" \
     http://localhost/metricas/api/metricas
```

### **Endpoints Disponibles**

**Base URL:** `/metricas/api`

#### **Métricas**

```http
GET    /metricas                # Listar todas
GET    /metricas?area_id=1      # Filtrar por área
GET    /metricas/{id}           # Obtener una
POST   /metricas                # Crear (admin)
PUT    /metricas/{id}           # Actualizar (admin)
DELETE /metricas/{id}           # Eliminar (admin)
```

**Ejemplo - Listar métricas:**

```bash
curl -H "Authorization: Bearer abc123..." \
     http://localhost/metricas/api/metricas?area_id=1
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Proyectos Activos",
      "area_id": 1,
      "unidad": "proyectos",
      "tipo_valor": "numero",
      "activo": 1
    }
  ]
}
```

**Ejemplo - Crear métrica:**

```bash
curl -X POST \
  -H "Authorization: Bearer abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Nuevos Clientes",
    "area_id": 2,
    "unidad": "clientes",
    "tipo_valor": "numero",
    "tiene_meta": true,
    "icono": "user-plus"
  }' \
  http://localhost/metricas/api/metricas
```

#### **Valores de Métricas**

```http
GET  /valores?metrica_id={id}&periodo_id={id}  # Obtener valor
GET  /valores/historico?metrica_id={id}&periodos=12  # Histórico
POST /valores                                   # Crear/actualizar
```

**Ejemplo - Obtener histórico:**

```bash
curl -H "Authorization: Bearer abc123..." \
     "http://localhost/metricas/api/valores/historico?metrica_id=1&periodos=6"
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "periodo_id": 10,
      "periodo_nombre": "Octubre 2025",
      "valor": 15,
      "nota": null
    },
    {
      "periodo_id": 11,
      "periodo_nombre": "Noviembre 2025",
      "valor": 18,
      "nota": "Incremento por campaña"
    }
  ]
}
```

**Ejemplo - Crear valor:**

```bash
curl -X POST \
  -H "Authorization: Bearer abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "metrica_id": 1,
    "periodo_id": 12,
    "valor": 22,
    "nota": "Cierre de año"
  }' \
  http://localhost/metricas/api/valores
```

#### **Períodos**

```http
GET /periodos              # Listar todos
GET /periodos/{id}         # Obtener uno
GET /periodos/actual       # Período actual
```

#### **Áreas**

```http
GET /areas                 # Listar todas
GET /areas/{id}            # Obtener una
GET /areas/{id}/metricas   # Métricas del área
```

#### **Departamentos**

```http
GET /departamentos              # Listar todos
GET /departamentos/{id}         # Obtener uno
GET /departamentos/{id}/areas   # Áreas del departamento
```

#### **Metas**

```http
GET    /metas?metrica_id={id}   # Metas de una métrica
POST   /metas                   # Crear (admin)
PUT    /metas/{id}              # Actualizar (admin)
DELETE /metas/{id}              # Eliminar (admin)
```

### **Respuestas de Error**

```json
// 401 Unauthorized
{
  "error": "Unauthorized",
  "message": "Token de API inválido o expirado"
}

// 403 Forbidden
{
  "error": "Forbidden",
  "message": "No tienes acceso a esta área"
}

// 404 Not Found
{
  "error": "Not Found",
  "message": "Métrica no encontrada"
}

// 400 Bad Request
{
  "error": "Bad Request",
  "message": "Campo 'nombre' requerido"
}

// 500 Internal Server Error
{
  "error": "Internal Server Error",
  "message": "Error al procesar la solicitud"
}
```

### **Rate Limiting**

**Estado actual:** Deshabilitado

**Futuro:** 1000 requests por hora por token

### **Documentación Interactiva**

Acceder a `/metricas/api` para ver:
- Lista completa de endpoints
- Ejemplos de request/response
- Esquemas de datos
- Guía de autenticación

---

## Catálogo de Gráficos

El sistema incluye 21 tipos de gráficos especializados:

### **Básicos**

| Gráfico | Descripción | Casos de Uso | Métricas |
|---------|-------------|--------------|----------|
| **Line** | Línea simple | Evolución temporal | 1 |
| **Bar** | Barras verticales | Comparación por período | 1 |
| **Area** | Área sombreada | Volumen acumulado | 1 |
| **Donut** | Anillo circular | Composición actual | 1 |

### **Con Meta**

| Gráfico | Descripción | Casos de Uso | Métricas |
|---------|-------------|--------------|----------|
| **Line with Goal** | Línea vs objetivo | Progreso hacia meta | 1 |
| **Gauge with Goal** | Medidor circular | % de cumplimiento | 1 |
| **KPI with Goal** | Tarjeta con indicador | Valor destacado + meta | 1 |

### **Comparación**

| Gráfico | Descripción | Casos de Uso | Métricas |
|---------|-------------|--------------|----------|
| **Multi-line** | Múltiples líneas | Comparar tendencias | 2-5 |
| **Multi-bar** | Barras agrupadas | Comparar categorías | 2-5 |
| **Stacked Area** | Áreas apiladas | Partes del total | 2-5 |
| **Mixed** | Barras + líneas | Datos heterogéneos | 2-5 |
| **Period Comparison** | 2 períodos específicos | Antes/Después | 1 |

### **Análisis**

| Gráfico | Descripción | Casos de Uso | Métricas |
|---------|-------------|--------------|----------|
| **Scatter** | Dispersión | Correlación | 2 |
| **Bullet** | Barra con rangos | Desempeño vs rangos | 1 |

### **Compactos**

| Gráfico | Descripción | Casos de Uso | Métricas |
|---------|-------------|--------------|----------|
| **KPI Card** | Valor + tendencia | Dashboard ejecutivo | 1 |
| **Sparkline** | Mini-gráfico | Tablas densas | 1 |
| **Progress** | Barra de progreso | % de avance | 1 |
| **Gauge** | Medidor | Velocímetro visual | 1 |

### **Configuraciones Especiales**

#### **Line with Goal**
```json
{
  "metrica_id": 1,
  "periodos": 12,          // 6, 12 o 24
  "mostrar_puntos": true,
  "curva_suave": true
}
```

#### **Period Comparison**
```json
{
  "metrica_id": 1,
  "periodo_1": 10,         // ID período 1
  "periodo_2": 11,         // ID período 2
  "estilo": "cards"        // cards, bars o gauge
}
```

#### **Multi-line**
```json
{
  "metricas": [1, 2, 3],   // 2-5 métricas
  "periodos": 12,
  "leyenda": "bottom"
}
```

#### **Scatter**
```json
{
  "metrica_x": 1,          // Métrica eje X
  "metrica_y": 2,          // Métrica eje Y
  "periodos": 12
}
```

---

## Desarrollo y Extensión

### **Crear un Nuevo Tipo de Gráfico**

#### **1. Crear Archivo del Componente**

`views/components/charts/mi-grafico.php`:

```php
<?php
return [
    'meta' => [
        'id' => 'mi-grafico',
        'nombre' => 'Mi Gráfico Personalizado',
        'descripcion' => 'Descripción breve',
        'icono' => 'chart-line',  // Tabler Icon
        'requiere_metricas' => 1,  // Número de métricas
        'categoria' => 'basico'     // basico, comparacion, analisis
    ],

    'config_form' => function($metrica = null) {
        ob_start();
        ?>
        <!-- Formulario de configuración -->
        <div class="mb-3">
            <label class="form-label">Opción 1</label>
            <input type="text" name="config[opcion1]" class="form-control">
        </div>
        <?php
        return ob_get_clean();
    },

    'process' => function($post) {
        // Procesar datos del formulario
        return [
            'metrica_id' => (int)$post['metrica_id'],
            'opcion1' => sanitize($post['config']['opcion1'] ?? '')
        ];
    },

    'render' => function($config, $metrica_data, $color) {
        // Obtener datos
        $valor = $metrica_data['valor_numero'] ?? $metrica_data['valor_decimal'] ?? 0;
        
        ob_start();
        ?>
        <div id="chart-<?php echo uniqid(); ?>"></div>
        <script>
        // Renderizar con ApexCharts
        new ApexCharts(document.getElementById('chart-...'), {
            series: [{
                data: [<?php echo $valor; ?>]
            }],
            chart: {
                type: 'line'
            }
        }).render();
        </script>
        <?php
        return ob_get_clean();
    }
];
```

#### **2. El Sistema lo Registra Automáticamente**

`ChartRegistry::load()` escanea automáticamente `/views/components/charts/*.php`

#### **3. Aparece en el Catálogo**

Inmediatamente disponible en:
- Dashboard → Modo Edición → Agregar Gráfico
- Administración → Gráficos

### **Extender la API**

#### **Crear Nuevo Endpoint**

`api/endpoints/mi-recurso.php`:

```php
<?php
use App\Models\MiModelo;
use App\Services\PermissionService;

$modelo = new MiModelo();
$id = $segments[1] ?? null;

// GET /mi-recurso
if ($method === 'GET' && !$id) {
    $items = $modelo->getAll();
    echo json_encode(['success' => true, 'data' => $items]);
    exit;
}

// GET /mi-recurso/{id}
if ($method === 'GET' && $id) {
    $item = $modelo->find($id);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'data' => $item]);
    exit;
}

// POST /mi-recurso (admin only)
if ($method === 'POST') {
    if (!in_array($user['rol'], ['super_admin', 'dept_admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validación
    if (empty($input['nombre'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request', 'message' => 'Campo nombre requerido']);
        exit;
    }
    
    $id = $modelo->create($input);
    
    http_response_code(201);
    echo json_encode(['success' => true, 'data' => ['id' => $id]]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
```

#### **Registrar en Router**

`api/index.php`:

```php
case 'mi-recurso':
    require __DIR__ . '/endpoints/mi-recurso.php';
    break;
```

### **Crear Modelo Personalizado**

`src/Models/MiModelo.php`:

```php
<?php
namespace App\Models;

class MiModelo extends Model
{
    protected $table = 'mi_tabla';
    
    public function findByCustomField($value)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE custom_field = ?
        ");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
}
```

### **Sistema de Caché**

#### **Usar Caché en Queries**

```php
use App\Utils\Cache;

// Cachear por 5 minutos (300 segundos)
$metricas = Cache::remember('metricas_area_' . $area_id, 300, function() use ($area_id) {
    return $metricaModel->getByArea($area_id);
});
```

#### **Invalidar Caché**

```php
// Invalidar clave específica
Cache::forget('metricas_area_1');

// Limpiar toda la caché
Cache::flush();

// Limpiar caché expirada
Cache::cleanup();
```

#### **CLI de Gestión**

```bash
# Ver estadísticas
php cache-manager.php --stats

# Limpiar expirada
php cache-manager.php --clean

# Limpiar toda
php cache-manager.php --flush
```

### **Optimización de Queries**

#### **Batch Loading (prevenir N+1)**

```php
use App\Utils\QueryOptimizer;

// Cargar valores de múltiples métricas en una sola query
$valores = QueryOptimizer::batchLoadValores($db, [1, 2, 3, 4], $periodo_id);

// $valores = [
//   1 => ['valor_numero' => 10],
//   2 => ['valor_numero' => 20],
//   ...
// ]
```

### **Agregar Migración**

`database/migrations/006_mi_cambio.sql`:

```sql
-- Descripción del cambio

ALTER TABLE metricas ADD COLUMN nuevo_campo VARCHAR(100) DEFAULT NULL;

CREATE INDEX idx_nuevo_campo ON metricas(nuevo_campo);
```

Ejecutar:

```bash
mysql -u root -p metricas_db < database/migrations/006_mi_cambio.sql
```

---

## Solución de Problemas

### **Problemas de Acceso**

#### **No puedo iniciar sesión**

1. Verificar credenciales
2. Comprobar estado del usuario:
   ```sql
   SELECT * FROM usuarios WHERE email = 'usuario@example.com';
   ```
3. Resetear contraseña:
   ```sql
   UPDATE usuarios 
   SET password = '$2y$10$...nuevo-hash...' 
   WHERE email = 'usuario@example.com';
   ```

#### **No veo ningún departamento/área**

- **Visualizadores:** Deben tener áreas asignadas
  ```sql
  SELECT * FROM usuarios_areas WHERE usuario_id = X;
  ```
- **Admin Depto:** Deben tener departamento asignado
  ```sql
  SELECT departamento_id FROM usuarios WHERE id = X;
  ```

### **Problemas de Performance**

#### **Dashboard carga lento**

1. **Habilitar caché:**
   ```php
   // En el controlador del dashboard
   use App\Utils\Cache;
   
   $metricas = Cache::remember('dashboard_metricas_' . $area_id, 300, function() {
       // Query
   });
   ```

2. **Activar lazy loading:**
   - Verificar que `/public/assets/js/lazy-charts.js` esté incluido
   - Comprobar que gráficos tengan atributo `data-lazy="true"`

3. **Reducir número de gráficos:**
   - Máximo recomendado: 12 gráficos por dashboard

4. **Optimizar queries:**
   ```sql
   -- Agregar índices
   CREATE INDEX idx_valores_metrica_periodo ON valores_metricas(metrica_id, periodo_id);
   CREATE INDEX idx_metricas_area ON metricas(area_id, activo);
   ```

#### **API lenta**

1. **Verificar caché:**
   ```bash
   php cache-manager.php --stats
   ```

2. **Revisar queries lentas:**
   ```sql
   SHOW PROCESSLIST;
   ```

3. **Habilitar índices:**
   ```bash
   mysql -u root -p metricas_db < database/migrations/004_performance_indexes.sql
   ```

### **Problemas de API**

#### **401 Unauthorized**

- Token inválido o expirado
- Verificar header: `Authorization: Bearer {token}`
- Regenerar token en panel de usuario

#### **403 Forbidden**

- Usuario no tiene permisos para ese recurso
- Verificar rol y asignaciones de área

#### **500 Internal Server Error**

- Ver logs de Apache: `/xampp/apache/logs/error.log`
- Ver logs de PHP: comprobar `error_log` en `php.ini`

### **Problemas de Gráficos**

#### **Gráfico no se muestra**

1. **Verificar consola del navegador** (F12)
2. **Comprobar que ApexCharts esté cargado:**
   ```javascript
   console.log(typeof ApexCharts); // debe ser 'function'
   ```
3. **Verificar datos de métrica:**
   ```sql
   SELECT * FROM valores_metricas 
   WHERE metrica_id = X AND periodo_id = Y;
   ```

#### **Gráfico muestra "Sin datos"**

- No hay valores registrados para el período actual
- Registrar valores en: Administración → Métricas → [Métrica] → Valores

### **Problemas de Exportación**

#### **CSV se abre con caracteres raros en Excel**

- El archivo usa UTF-8 con BOM (correcto)
- En Excel: Datos → Obtener datos externos → Desde texto → UTF-8

#### **PDF no imprime correctamente**

- Usar Chrome/Edge para imprimir
- Verificar configuración: Orientación horizontal para tablas anchas

### **Logs y Debugging**

#### **Habilitar modo debug**

`config/database.php`:

```php
'options' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]
```

#### **Ver logs de queries**

Agregar a `includes/db.php`:

```php
class DebugPDO extends PDO {
    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args) {
        error_log("QUERY: " . $statement);
        return parent::query($statement, $mode, ...$fetch_mode_args);
    }
}
```

---

## Referencia Técnica

### **Tecnologías**

| Componente | Tecnología | Versión |
|------------|------------|---------|
| Backend | PHP | 8.1+ |
| Base de datos | MySQL/MariaDB | 5.7+ / 10.3+ |
| Frontend CSS | Tabler | 1.0.0-beta17 |
| Gráficos | ApexCharts | 3.x |
| Grid | GridStack | 9.0.0 |
| Icons | Tabler Icons | Latest |
| Autoload | Composer | 2.x |

### **Estructura de Base de Datos**

#### **Tabla: departamentos**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| nombre | VARCHAR(100) | Nombre del departamento |
| descripcion | TEXT | Descripción |
| icono | VARCHAR(50) | Tabler icon |
| color | VARCHAR(7) | Color hex |
| activo | TINYINT(1) | Estado |
| created_at | TIMESTAMP | Fecha creación |
| updated_at | TIMESTAMP | Última actualización |

#### **Tabla: areas**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| departamento_id | INT FK | Departamento padre |
| nombre | VARCHAR(100) | Nombre del área |
| descripcion | TEXT | Descripción |
| icono | VARCHAR(50) | Tabler icon |
| color | VARCHAR(7) | Color hex |
| orden | INT | Orden de visualización |
| activo | TINYINT(1) | Estado |

#### **Tabla: metricas**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| area_id | INT FK | Área |
| nombre | VARCHAR(200) | Nombre |
| descripcion | TEXT | Descripción |
| slug | VARCHAR(200) | URL-friendly |
| unidad | VARCHAR(50) | Unidad de medida |
| tipo_valor | ENUM | 'numero' o 'decimal' |
| icono | VARCHAR(50) | Tabler icon |
| es_calculada | TINYINT(1) | Si se calcula automáticamente |
| tiene_meta | TINYINT(1) | Si tiene objetivos |
| orden | INT | Orden |
| activo | TINYINT(1) | Estado |

#### **Tabla: valores_metricas**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| metrica_id | INT FK | Métrica |
| periodo_id | INT FK | Período |
| valor_numero | INT | Valor entero |
| valor_decimal | DECIMAL(15,4) | Valor decimal |
| nota | TEXT | Observaciones |
| usuario_registro_id | INT FK | Quien registró |
| created_at | TIMESTAMP | Fecha registro |

**Constraint:** UNIQUE(metrica_id, periodo_id) - Solo un valor por métrica por período

#### **Tabla: metas_metricas**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| metrica_id | INT FK | Métrica |
| valor_objetivo | DECIMAL(15,4) | Meta a alcanzar |
| periodo_inicio_id | INT FK | Desde período |
| periodo_fin_id | INT FK | Hasta período (NULL = indefinido) |
| sentido | ENUM | 'mayor_mejor' o 'menor_mejor' |
| activo | TINYINT(1) | Estado |

#### **Tabla: graficos**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| area_id | INT FK | Área |
| tipo | VARCHAR(50) | Tipo de gráfico (line, bar, etc.) |
| titulo | VARCHAR(200) | Título del gráfico |
| configuracion | JSON | Config específica del tipo |
| grid_x | INT | Posición X en grid |
| grid_y | INT | Posición Y en grid |
| grid_w | INT | Ancho en grid |
| grid_h | INT | Alto en grid |
| orden | INT | Orden |
| activo | TINYINT(1) | Estado |

#### **Tabla: periodos**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| nombre | VARCHAR(100) | Nombre (ej. "Enero 2026") |
| ejercicio | INT | Año fiscal |
| periodo | INT | Número de mes/trimestre |
| fecha_inicio | DATE | Inicio |
| fecha_fin | DATE | Fin |
| es_actual | TINYINT(1) | Período predeterminado |
| activo | TINYINT(1) | Estado |

#### **Tabla: usuarios**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| departamento_id | INT FK | Departamento (admin_depto) |
| nombre | VARCHAR(100) | Nombre completo |
| email | VARCHAR(100) UNIQUE | Email (login) |
| password | VARCHAR(255) | Hash bcrypt |
| rol | ENUM | 'super_admin', 'dept_admin', 'dept_viewer' |
| avatar_icono | VARCHAR(50) | Ícono avatar |
| avatar_color | VARCHAR(7) | Color avatar |
| tema | VARCHAR(10) | 'light' o 'dark' |
| activo | TINYINT(1) | Estado |

#### **Tabla: usuarios_areas**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| usuario_id | INT FK | Usuario |
| area_id | INT FK | Área con acceso |

**PK Compuesta:** (usuario_id, area_id)

#### **Tabla: api_tokens**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | ID único |
| usuario_id | INT FK | Propietario |
| token | VARCHAR(64) UNIQUE | Hash SHA256 |
| nombre | VARCHAR(100) | Identificador |
| expires_at | DATETIME | Expiración |
| ultimo_uso | DATETIME | Último uso |
| total_usos | INT | Contador |
| activo | TINYINT(1) | Estado |

### **Funciones Helper**

Disponibles globalmente en `includes/functions.php`:

```php
// Autenticación
isLoggedIn()              // bool - Usuario autenticado
getCurrentUser()          // array - Datos del usuario actual
isDeptAdmin()             // bool - Es admin de departamento
isSuperAdmin()            // bool - Es super admin

// Base de datos
getDB()                   // PDO - Conexión a BD

// Utilidades
sanitize($string)         // string - Limpia input
e($string)                // string - Escapa HTML (alias htmlspecialchars)
baseUrl($path)            // string - URL completa
redirect($url)            // void - Redirige y termina

// Flash messages
setFlash($type, $message) // void - Guarda mensaje en sesión
getFlash()                // array|null - Obtiene y elimina mensaje
```

### **Clases de Servicio**

#### **PermissionService**

```php
use App\Services\PermissionService;

// Verificar acceso
PermissionService::canViewDepartamento($user, $dept_id)  // bool
PermissionService::canViewArea($user, $area_id)          // bool
PermissionService::canEditArea($user, $area_id)          // bool

// Obtener recursos permitidos
PermissionService::getDepartamentosPermitidos($user)     // array
PermissionService::getAreasPermitidas($user, $dept_id)   // array
```

#### **ApiAuthService**

```php
use App\Services\ApiAuthService;

$service = new ApiAuthService($db);

// Generar token
$token_data = $service->generateToken($user_id, $nombre, $expires_days);
// Retorna: ['token' => 'abc123...', 'expires_at' => '2027-01-01 00:00:00']

// Validar token
$user = $service->validateToken($token);  // array|null

// Revocar token
$service->revokeToken($token_id);
```

#### **ExportService**

```php
use App\Services\ExportService;

$service = new ExportService($db);

// Exportar CSV (envía headers y termina)
$service->exportToCSV($metrica_ids, $periodos, $area_id);

// Generar HTML imprimible
$html = $service->exportToPrintableHTML($metrica_ids, $periodos, $area_id);
```

#### **Cache**

```php
use App\Utils\Cache;

// Cachear con callback
$data = Cache::remember($key, $ttl, function() {
    return expensiveQuery();
});

// Operaciones básicas
Cache::set($key, $value, $ttl);
$value = Cache::get($key);
Cache::forget($key);
Cache::flush();
Cache::cleanup();  // Elimina expirados
```

### **Constantes**

```php
// Rutas
define('BASE_PATH', '/metricas');
define('STORAGE_PATH', __DIR__ . '/storage');
define('CACHE_PATH', STORAGE_PATH . '/cache');

// Caché
define('CACHE_TTL_DEFAULT', 300);  // 5 minutos
```

### **Seguridad**

#### **Protección CSRF** (Futuro)

Actualmente no implementado. Para agregar:

```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verificar en POST
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token inválido');
}
```

#### **Sanitización de Input**

```php
// Siempre usar
$safe = sanitize($_POST['input']);

// Para queries, usar prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

#### **Passwords**

```php
// Hash (al crear usuario)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verificar (al login)
if (password_verify($password, $hash)) {
    // Correcto
}
```

### **Performance**

#### **Tiempos de Respuesta Objetivo**

| Operación | Objetivo | Observaciones |
|-----------|----------|---------------|
| Login | < 200ms | Sin caché |
| Dashboard carga | < 1s | Con 8 gráficos, caché habilitado |
| API /metricas | < 150ms | Con caché |
| Exportación CSV | < 2s | 100 métricas, 12 períodos |
| Búsqueda | < 300ms | Con índices |

#### **Optimizaciones Aplicadas**

- **Índices en FK:** Todas las foreign keys tienen índice
- **Índices compuestos:** 
  - `(metrica_id, periodo_id)` en valores_metricas
  - `(area_id, activo)` en metricas
- **Caché de queries frecuentes:** 5 min TTL
- **Lazy loading:** Gráficos cargan al scroll
- **Batch loading:** Una query para múltiples métricas

---

## Licencia

**Uso Interno Corporativo**

Este sistema es propiedad de [Tu Empresa]. Todos los derechos reservados.

---

## Soporte

Para soporte técnico o reportar bugs:

- **Email:** soporte@tuempresa.com
- **Issue Tracker:** [GitHub Issues](enlace)
- **Documentación:** Este archivo README.md

---

## Changelog

### **v2.0** - Abril 2026
- ✨ API REST completa con autenticación por tokens
- ✨ Sistema de exportación PDF/Excel
- ✨ Gráfico de comparación de períodos
- ✨ Sistema de caché con CLI de gestión
- ✨ Lazy loading de gráficos
- ✨ 8 nuevos tipos de gráficos
- 🔧 Optimización de queries (batch loading)
- 🔧 Mejoras de performance (índices)
- 📚 Documentación completa

### **v1.0** - Enero 2026
- 🎉 Lanzamiento inicial
- ✨ Gestión de métricas, áreas y departamentos
- ✨ Dashboards con GridStack
- ✨ 13 tipos de gráficos básicos
- ✨ Sistema de usuarios y permisos
- ✨ Gestión de metas y períodos

---

**Desarrollado con ❤️ para [Tu Empresa]**
