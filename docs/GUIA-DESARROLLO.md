# Guía de Desarrollo - Sistema de Métricas

Documentación técnica para desarrolladores que trabajarán en el sistema de métricas.

## Tabla de Contenidos

1. [Arquitectura](#arquitectura)
2. [Estructura de Archivos](#estructura-de-archivos)
3. [Stack Tecnológico](#stack-tecnológico)
4. [Configuración del Entorno](#configuración-del-entorno)
5. [Modelos y Base de Datos](#modelos-y-base-de-datos)
6. [ChartRegistry](#chartregistry)
7. [Crear Nuevo Tipo de Gráfico](#crear-nuevo-tipo-de-gráfico)
8. [Middleware y Servicios](#middleware-y-servicios)
9. [Frontend](#frontend)
10. [Testing](#testing)
11. [Convenciones de Código](#convenciones-de-código)

---

## Arquitectura

### Patrón MVC Simplificado

```
┌─────────────┐
│  VIEWS      │  PHP + HTML (templates)
│  (public/)  │
└──────┬──────┘
       │
┌──────▼──────┐
│ CONTROLLERS │  Lógica de negocio
│ (public/*)  │  Routing
└──────┬──────┘
       │
┌──────▼──────┐
│  MODELS     │  Acceso a datos
│  (src/)     │  Validación
└──────┬──────┘
       │
┌──────▼──────┐
│  DATABASE   │  MySQL
│  (MySQL)    │
└─────────────┘
```

### Flujo de Request

```
User Request
    ↓
index.php (Entry Point)
    ↓
AuthMiddleware::handle()
    ↓
PermissionService::check()
    ↓
Models (fetch data)
    ↓
Views (render HTML)
    ↓
ChartRegistry::render()
    ↓
Response
```

---

## Estructura de Archivos

```
metricas/
├── config/
│   └── database.php          # Config de BD
├── database/
│   ├── schema.sql            # Esquema completo
│   └── migrations/           # Migraciones SQL
├── docs/                     # Documentación
├── public/                   # Punto de entrada web
│   ├── index.php            # Dashboard principal
│   ├── login.php            # Login
│   └── admin/               # Panel admin
│       ├── graficos.php
│       ├── metricas.php
│       └── usuarios.php
├── src/
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   ├── Models/              # Modelos de datos
│   │   ├── Model.php        # Base model
│   │   ├── Usuario.php
│   │   ├── Departamento.php
│   │   ├── Area.php
│   │   ├── Metrica.php
│   │   ├── ValorMetrica.php
│   │   ├── Meta.php
│   │   └── Grafico.php
│   ├── Services/
│   │   └── PermissionService.php
│   └── Utils/
│       └── ChartRegistry.php
├── views/
│   ├── components/
│   │   └── charts/          # 24 tipos de gráficos
│   │       ├── kpi-card.php
│   │       ├── line.php
│   │       └── ...
│   ├── layouts/
│   │   ├── header.php
│   │   └── footer.php
│   └── partials/
├── assets/
│   ├── css/
│   │   ├── custom.css
│   │   └── dashboard-grid.css
│   └── js/
│       └── app.js
├── uploads/
│   └── avatars/
├── vendor/                   # Composer
├── composer.json
├── config.php               # Config principal
└── includes/
    ├── db.php              # Database connection
    └── functions.php       # Helper functions
```

---

## Stack Tecnológico

### Backend

**PHP 8.0+**
- Namespaces (PSR-4)
- Type hints
- Arrow functions
- Null coalescing operator

**PDO (PHP Data Objects)**
- Prepared statements
- Named parameters
- Fetch modes

**Composer**
- Autoloading PSR-4
- Dependency management

### Frontend

**HTML5 + Bootstrap 5**
- Responsive grid
- Components
- Utilities

**JavaScript ES6+**
- Async/await
- Destructuring
- Template literals
- Modules (future)

**Libraries:**
- ApexCharts 3.44+
- GridStack.js 9.x
- SweetAlert2 11.x
- Tabler Icons

---

## Configuración del Entorno

### Requisitos

```bash
php -v  # >= 8.0
mysql --version  # >= 5.7
composer --version  # >= 2.0
```

### Instalación

```bash
# Clonar repo
git clone https://github.com/tu-usuario/metricas.git
cd metricas

# Instalar dependencias
composer install

# Copiar config
cp config.example.php config.php

# Editar config.php con tus credenciales

# Crear BD
mysql -u root -p -e "CREATE DATABASE metricas_sistema"

# Importar esquema
mysql -u root -p metricas_sistema < database/schema.sql

# Permisos
chmod 755 uploads/
chmod 755 uploads/avatars/
```

### Variables de Entorno

`config.php`:
```php
<?php
// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'metricas_sistema');
define('DB_USER', 'root');
define('DB_PASS', '');

// App
define('APP_ENV', 'development'); // development, production
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost/metricas');

// Session
define('SESSION_LIFETIME', 3600); // 1 hora
```

---

## Modelos y Base de Datos

### Modelo Base

Todos los modelos extienden `Model`:

```php
namespace App\Models;

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = getDB();
    }

    // CRUD básico
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function all() {
        return $this->db->query("SELECT * FROM {$this->table}")->fetchAll();
    }

    // ... más métodos
}
```

### Crear Nuevo Modelo

```php
namespace App\Models;

class NuevoModelo extends Model {
    protected $table = 'nombre_tabla';

    // Métodos específicos
    public function getByCustomField($value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE custom_field = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
}
```

### Relaciones

**Ejemplo: Métrica con Área**

```php
class Metrica extends Model {
    // Obtener métrica con información del área
    public function findWithArea($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, a.nombre as area_nombre, a.color as area_color
            FROM {$this->table} m
            JOIN areas a ON m.area_id = a.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
```

---

## ChartRegistry

Sistema de registro dinámico de gráficos.

### Estructura de un Chart Component

Cada tipo de gráfico es un archivo PHP que retorna un array:

```php
// views/components/charts/mi-grafico.php
return [
    'meta' => [...],      // Metadata
    'form' => function() {...},  // Formulario de configuración
    'process' => function($post) {...},  // Procesar form data
    'load_config_js' => '...',  // Cargar config en edición (JS)
    'render' => function($config, $metrica_data, $area_color, $periodo) {...}  // Renderizar
];
```

### Metadata

```php
'meta' => [
    'id' => 'mi_grafico',  // ID único
    'nombre' => 'Mi Gráfico',  // Nombre para mostrar
    'descripcion' => 'Descripción breve',
    'icono' => 'chart-line',  // Icono de Tabler
    'requiere_metricas' => 1,  // Cuántas métricas necesita
    'requiere_metas' => false,  // Si requiere metas
    'version' => '1.0'
]
```

### Formulario

```php
'form' => function() {
    return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <label class="form-label required">Métrica</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar...</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="300" min="200" max="600">
    </div>
</div>
HTML;
}
```

### Process

```php
'process' => function($post) {
    return [
        'metrica_id' => (int)$post['metrica_id'],
        'color' => sanitize($post['color'] ?? '#3b82f6'),
        'altura' => (int)($post['altura'] ?? 300)
    ];
}
```

### Load Config JS

```php
'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="color"]').value = config.color;
    form.querySelector('[name="altura"]').value = config.altura;
}
JS
```

### Render

```php
'render' => function($config, $metrica_data, $area_color, $periodo = null) {
    // Validaciones
    if (!isset($config['metrica_id'])) {
        return '<div class="alert alert-warning">Configura la métrica</div>';
    }
    
    if (!$periodo) {
        return '<div class="alert alert-warning">Selecciona un período</div>';
    }
    
    // Obtener datos
    $valorMetricaModel = new \App\Models\ValorMetrica();
    $metricaModel = new \App\Models\Metrica();
    
    $metrica_id = $config['metrica_id'];
    $metrica = $metricaModel->find($metrica_id);
    $valor = $valorMetricaModel->getValor($metrica_id, $periodo['id']);
    
    if (!$valor) {
        return '<div class="alert alert-info">No hay datos</div>';
    }
    
    // Extraer valor
    $val = $metrica['tipo_valor'] === 'decimal' 
        ? (float)$valor['valor_decimal'] 
        : (int)$valor['valor_numero'];
    
    // Generar ID único
    $chart_id = 'chart_' . uniqid();
    
    // Renderizar
    return <<<HTML
<div id="{$chart_id}" style="height: {$config['altura']}px;"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;
        
        const options = {
            series: [{$val}],
            chart: {
                type: 'radialBar',
                height: {$config['altura']}
            },
            colors: ['{$config['color']}']
        };
        
        const chart = new ApexCharts(container, options);
        chart.render();
        container.setAttribute('data-chart-rendered', 'true');
    }, 200);
});
</script>
HTML;
}
```

### Registro

ChartRegistry carga automáticamente todos los charts:

```php
// src/Utils/ChartRegistry.php
public static function load() {
    $chartFiles = glob(__DIR__ . '/../../views/components/charts/*.php');
    foreach ($chartFiles as $file) {
        $chart = require $file;
        self::$charts[$chart['meta']['id']] = $chart;
    }
}
```

---

## Crear Nuevo Tipo de Gráfico

Ver [API-GRAFICOS.md](API-GRAFICOS.md) para tutorial completo paso a paso.

**Resumen:**

1. Crear archivo en `views/components/charts/mi-grafico.php`
2. Definir estructura con meta, form, process, load_config_js, render
3. El sistema lo detecta automáticamente
4. Aparece en el selector de tipos

---

## Middleware y Servicios

### AuthMiddleware

```php
namespace App\Middleware;

class AuthMiddleware {
    public static function handle() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
    }
}
```

### PermissionService

```php
namespace App\Services;

class PermissionService {
    public static function canViewArea($user, $area_id) {
        if ($user['rol'] === 'super_admin') return true;
        
        $areaModel = new \App\Models\Area();
        $area = $areaModel->find($area_id);
        
        if ($user['rol'] === 'dept_admin') {
            return $area['departamento_id'] == $user['departamento_id'];
        }
        
        if ($user['rol'] === 'dept_viewer') {
            return $area_id == $user['area_id'];
        }
        
        return false;
    }
}
```

---

## Frontend

### GridStack

```javascript
// Inicializar grid
const grid = GridStack.init({
    cellHeight: 80,
    margin: 10,
    resizable: {
        handles: 'se' // Solo esquina inferior derecha
    },
    draggable: {
        handle: '.card-header' // Solo desde header
    }
});

// Modo edición
grid.enable();  // Activar
grid.disable(); // Desactivar

// Guardar layout
const layout = grid.save();
// layout = [{x, y, w, h, id}, ...]
```

### ApexCharts

```javascript
const options = {
    series: [44, 55, 67],
    chart: {
        type: 'donut',
        height: 350
    },
    labels: ['Apple', 'Mango', 'Orange']
};

const chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();
```

### SweetAlert2

```javascript
Swal.fire({
    title: '¿Eliminar gráfico?',
    text: "Esta acción no se puede deshacer",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
}).then((result) => {
    if (result.isConfirmed) {
        // Eliminar
    }
});
```

---

## Testing

### Verificación Manual

```bash
# Verificar base de datos
php verify_database.php

# Limpiar datos de prueba
php cleanup_test_data.php
```

### Testing de Gráficos

```bash
# Crear entorno de pruebas
php create_test_environment.php

# Acceder a dashboard de pruebas
http://localhost/metricas/public/?area=22
```

### Debugging

```php
// Habilitar errores en desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Dump and die
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}
```

---

## Convenciones de Código

### PHP

**Naming:**
- Clases: `PascalCase`
- Métodos: `camelCase`
- Variables: `snake_case`
- Constantes: `UPPER_SNAKE_CASE`

**Ejemplo:**
```php
class ValorMetrica extends Model {
    protected $table = 'valores_metricas';
    
    public function getValor($metrica_id, $periodo_id) {
        $stmt = $this->db->prepare("...");
        return $stmt->fetch();
    }
}
```

### SQL

- Nombres de tablas: plural, snake_case (`valores_metricas`)
- Nombres de columnas: snake_case (`metrica_id`)
- Primary keys: siempre `id`
- Foreign keys: `{tabla_singular}_id`
- Timestamps: `created_at`, `updated_at`

### JavaScript

- Variables: `camelCase`
- Constantes: `UPPER_SNAKE_CASE`
- Funciones: `camelCase`

### CSS

- Clases: `kebab-case`
- IDs: `camelCase` o `kebab-case`
- Variables CSS: `--variable-name`

---

## Git Workflow

### Branches

```
main (producción)
  ├── develop (desarrollo)
  │   ├── feature/nueva-funcionalidad
  │   ├── bugfix/arreglar-bug
  │   └── hotfix/parche-urgente
```

### Commits

```bash
# Formato
[Tipo]: Descripción breve

# Tipos
Add: Nueva funcionalidad
Fix: Corrección de bug
Update: Actualización de feature existente
Refactor: Refactorización de código
Docs: Cambios en documentación
Style: Formato, estilo
Test: Tests

# Ejemplos
Add: nuevo tipo de gráfico radar
Fix: error en cálculo de metas anuales
Update: mejorar rendimiento de dashboard
```

---

## Recursos

- [ApexCharts Docs](https://apexcharts.com/docs/)
- [GridStack Docs](https://gridstackjs.com/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.0/)
- [Tabler Icons](https://tabler-icons.io/)
- [PHP PSR-4](https://www.php-fig.org/psr/psr-4/)

---

**Happy Coding!** 🚀
