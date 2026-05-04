# API de Gráficos - Sistema de Métricas

Guía completa para crear nuevos tipos de gráficos en el sistema.

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Anatomía de un Gráfico](#anatomía-de-un-gráfico)
3. [Tutorial Paso a Paso](#tutorial-paso-a-paso)
4. [Ejemplos Completos](#ejemplos-completos)
5. [Best Practices](#best-practices)
6. [Debugging](#debugging)

---

## Introducción

El Sistema de Métricas usa un sistema modular para gráficos mediante **ChartRegistry**. Cada tipo de gráfico es un archivo PHP independiente que se auto-registra.

### Ventajas

✅ **Modular**: Cada gráfico en su propio archivo
✅ **Auto-descubrimiento**: No necesitas registrar manualmente
✅ **Reutilizable**: Usa la misma estructura para todos
✅ **Extensible**: Agrega nuevos tipos sin modificar el core

---

## Anatomía de un Gráfico

Un chart component es un archivo PHP que retorna un array con 5 partes:

```php
return [
    'meta' => [...],                    // 1. Metadatos
    'form' => function() {...},         // 2. Formulario de configuración
    'process' => function($post) {...}, // 3. Procesar datos del formulario
    'load_config_js' => '...',          // 4. Cargar config en edición (JS)
    'render' => function(...) {...}     // 5. Renderizar el gráfico
];
```

### 1. Meta (Metadata)

Define las características del gráfico:

```php
'meta' => [
    'id' => 'mi_grafico',              // ID único (slug, sin espacios)
    'nombre' => 'Mi Gráfico',          // Nombre para mostrar en UI
    'descripcion' => 'Descripción',     // Tooltip/ayuda
    'icono' => 'chart-line',           // Icono de Tabler
    'requiere_metricas' => 1,          // Cuántas métricas necesita (1, 2, "2-5", etc.)
    'requiere_metas' => false,         // true si requiere metas configuradas
    'version' => '1.0'                 // Versión del componente
]
```

### 2. Form (Formulario)

Retorna el HTML del formulario de configuración:

```php
'form' => function() {
    return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <label class="form-label required">Métrica</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Color</label>
        <input type="color" name="color" class="form-control form-control-color" value="#3b82f6">
    </div>
</div>
HTML;
}
```

**Importante:**
- Usa `<<<'HTML'` (heredoc) para strings multiline
- Los selects de métricas se pueblan automáticamente
- Usa clases de Bootstrap 5
- Marca campos requeridos con `required` y `.form-label.required`

### 3. Process (Procesamiento)

Procesa los datos del formulario y retorna la configuración limpia:

```php
'process' => function($post) {
    return [
        'metrica_id' => (int)$post['metrica_id'],
        'color' => sanitize($post['color'] ?? '#3b82f6'),
        'altura' => (int)($post['altura'] ?? 300),
        'mostrar_puntos' => isset($post['mostrar_puntos'])
    ];
}
```

**Importante:**
- Sanitiza y valida todas las entradas
- Usa valores por defecto con `??`
- Convierte tipos explícitamente `(int)`, `(float)`, etc.
- Para checkboxes usa `isset()`

### 4. Load Config JS (JavaScript)

Función JavaScript para cargar configuración al editar:

```php
'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="color"]').value = config.color;
    form.querySelector('[name="altura"]').value = config.altura;
    
    const checkbox = form.querySelector('[name="mostrar_puntos"]');
    if (checkbox) checkbox.checked = config.mostrar_puntos;
}
JS
```

**Importante:**
- Recibe `form` (elemento DOM) y `config` (objeto de configuración)
- Pobla todos los campos del formulario
- Maneja valores opcionales con `if`

### 5. Render (Renderizado)

Función que genera el HTML del widget:

```php
'render' => function($config, $metrica_data, $area_color, $periodo = null) {
    // 1. Validaciones
    if (!isset($config['metrica_id'])) {
        return '<div class="alert alert-warning m-3">Configura la métrica</div>';
    }
    
    if (!$periodo) {
        return '<div class="alert alert-warning m-3">Selecciona un período</div>';
    }
    
    // 2. Instanciar modelos
    $valorMetricaModel = new \App\Models\ValorMetrica();
    $metricaModel = new \App\Models\Metrica();
    
    // 3. Obtener datos
    $metrica_id = $config['metrica_id'];
    $metrica = $metricaModel->find($metrica_id);
    $valor = $valorMetricaModel->getValor($metrica_id, $periodo['id']);
    
    if (!$valor) {
        return '<div class="alert alert-info m-3">No hay datos disponibles</div>';
    }
    
    // 4. Extraer valor según tipo
    $val = $metrica['tipo_valor'] === 'decimal' 
        ? (float)$valor['valor_decimal'] 
        : (int)$valor['valor_numero'];
    
    // 5. Generar ID único
    $chart_id = 'chart_' . uniqid();
    
    // 6. Renderizar HTML + Script
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

**Importante:**
- Siempre valida datos antes de renderizar
- Usa namespace completo para modelos `\App\Models\`
- Genera IDs únicos con `uniqid()`
- Usa `setTimeout(200)` para esperar DOM
- Marca con `data-chart-rendered` para evitar duplicados
- Retorna HTML como string

---

## Tutorial Paso a Paso

Vamos a crear un gráfico de **Termómetro** que muestra el % de cumplimiento de meta.

### Paso 1: Crear el Archivo

```bash
touch views/components/charts/thermometer.php
```

### Paso 2: Definir Metadata

```php
<?php
/**
 * GRÁFICO: Termómetro
 * Muestra % de cumplimiento con estilo de termómetro
 */

return [
    'meta' => [
        'id' => 'thermometer',
        'nombre' => 'Termómetro',
        'descripcion' => 'Muestra cumplimiento de meta como termómetro',
        'icono' => 'temperature',
        'requiere_metricas' => 1,
        'requiere_metas' => true,  // Requiere meta
        'version' => '1.0'
    ],
```

### Paso 3: Crear Formulario

```php
    'form' => function() {
        return <<<'HTML'
<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Requiere una métrica con meta configurada
        </div>
    </div>
    
    <div class="col-12">
        <label class="form-label required">Métrica con Meta</label>
        <select name="metrica_id" class="form-select" required>
            <option value="">Seleccionar métrica...</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Color cuando cumple</label>
        <input type="color" name="color_success" class="form-control form-control-color" value="#10b981">
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Color cuando no cumple</label>
        <input type="color" name="color_danger" class="form-control form-control-color" value="#ef4444">
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Altura (px)</label>
        <input type="number" name="altura" class="form-control" value="300" min="200" max="500">
    </div>
</div>
HTML;
    },
```

### Paso 4: Procesar Formulario

```php
    'process' => function($post) {
        return [
            'metrica_id' => (int)$post['metrica_id'],
            'color_success' => sanitize($post['color_success'] ?? '#10b981'),
            'color_danger' => sanitize($post['color_danger'] ?? '#ef4444'),
            'altura' => (int)($post['altura'] ?? 300)
        ];
    },
```

### Paso 5: Cargar Configuración

```php
    'load_config_js' => <<<'JS'
function(form, config) {
    form.querySelector('[name="metrica_id"]').value = config.metrica_id;
    form.querySelector('[name="color_success"]').value = config.color_success;
    form.querySelector('[name="color_danger"]').value = config.color_danger;
    form.querySelector('[name="altura"]').value = config.altura;
}
JS,
```

### Paso 6: Renderizar Gráfico

```php
    'render' => function($config, $metrica_data, $area_color, $periodo = null) {
        // Validaciones
        if (!isset($config['metrica_id'])) {
            return '<div class="alert alert-warning m-3">Configura la métrica</div>';
        }
        
        if (!$periodo) {
            return '<div class="alert alert-warning m-3">Selecciona un período</div>';
        }
        
        // Modelos
        $valorMetricaModel = new \App\Models\ValorMetrica();
        $metricaModel = new \App\Models\Metrica();
        $metaModel = new \App\Models\Meta();
        
        $metrica_id = $config['metrica_id'];
        
        // Obtener datos
        $metrica = $metricaModel->find($metrica_id);
        $valor = $valorMetricaModel->getValor($metrica_id, $periodo['id']);
        $meta = $metaModel->getMetaAplicable($metrica_id, $periodo['id']);
        
        if (!$valor || !$meta) {
            return '<div class="alert alert-info m-3">No hay datos o meta para este período</div>';
        }
        
        // Calcular cumplimiento
        $val = $metrica['tipo_valor'] === 'decimal' 
            ? (float)$valor['valor_decimal'] 
            : (int)$valor['valor_numero'];
        
        $valor_objetivo = (float)$meta['valor_objetivo'];
        $cumplimiento = $metaModel->calcularCumplimiento($val, $valor_objetivo, $meta['tipo_comparacion']);
        
        // Determinar color
        $color = $cumplimiento >= 100 ? $config['color_success'] : $config['color_danger'];
        
        // IDs
        $chart_id = 'thermometer_' . uniqid();
        
        // Renderizar
        return <<<HTML
<div class="thermometer-widget p-3">
    <div class="text-center mb-3">
        <h3 class="mb-1">{$metrica['nombre']}</h3>
        <div class="text-muted">Meta: {$valor_objetivo} {$metrica['unidad']}</div>
    </div>
    
    <div id="{$chart_id}" style="height: {$config['altura']}px;"></div>
    
    <div class="text-center mt-3">
        <div class="h2 mb-0" style="color: {$color};">{$cumplimiento}%</div>
        <div class="text-muted">Cumplimiento</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const container = document.getElementById('{$chart_id}');
        if (!container || container.hasAttribute('data-chart-rendered')) return;
        
        const options = {
            series: [{$cumplimiento}],
            chart: {
                type: 'radialBar',
                height: {$config['altura']}
            },
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '70%'
                    },
                    dataLabels: {
                        show: false
                    },
                    track: {
                        background: '#e7e7e7'
                    }
                }
            },
            colors: ['{$color}'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'vertical',
                    gradientToColors: ['{$color}'],
                    stops: [0, 100]
                }
            }
        };
        
        const chart = new ApexCharts(container, options);
        chart.render();
        container.setAttribute('data-chart-rendered', 'true');
    }, 200);
});
</script>
HTML;
    }
];
```

### Paso 7: Probar

1. El chart se auto-registra automáticamente
2. Ve a un dashboard en modo edición
3. Click "+ Agregar Gráfico"
4. Verás "Termómetro" en la lista
5. Configúralo y guarda

---

## Ejemplos Completos

### Gráfico Simple (KPI Card)

Ver: `views/components/charts/kpi-card.php`

**Características:**
- 1 métrica
- Comparación con período anterior
- Sin ApexCharts (HTML puro)

### Gráfico de Línea

Ver: `views/components/charts/line.php`

**Características:**
- 1 métrica
- Histórico (6, 12, 24 meses)
- ApexCharts tipo 'line'

### Gráfico Múltiple

Ver: `views/components/charts/multi-bar.php`

**Características:**
- 3-6 métricas
- Barras agrupadas
- Loop sobre métricas

### Con Metas

Ver: `views/components/charts/bullet.php`

**Características:**
- 1 métrica con meta
- Calcula cumplimiento
- Muestra rangos

---

## Best Practices

### 1. Validación de Datos

✅ **Hacer:**
```php
if (!isset($config['metrica_id'])) {
    return '<div class="alert alert-warning">...</div>';
}

if (!$periodo) {
    return '<div class="alert alert-warning">...</div>';
}

if (!$valor) {
    return '<div class="alert alert-info">No hay datos</div>';
}
```

❌ **Evitar:**
```php
// No asumir que los datos existen
$val = $valor['valor_numero']; // Puede causar error
```

### 2. IDs Únicos

✅ **Hacer:**
```php
$chart_id = 'chart_' . uniqid();
```

❌ **Evitar:**
```php
// IDs estáticos pueden causar conflictos
$chart_id = 'my-chart';
```

### 3. Prevenir Re-render

✅ **Hacer:**
```javascript
const container = document.getElementById('{$chart_id}');
if (!container || container.hasAttribute('data-chart-rendered')) return;

// ... renderizar ...

container.setAttribute('data-chart-rendered', 'true');
```

### 4. Manejo de Tipos de Valor

✅ **Hacer:**
```php
$val = $metrica['tipo_valor'] === 'decimal' 
    ? (float)$valor['valor_decimal'] 
    : (int)$valor['valor_numero'];
```

### 5. Sanitización

✅ **Hacer:**
```php
'color' => sanitize($post['color'] ?? '#3b82f6')
```

### 6. Namespace Completo

✅ **Hacer:**
```php
$model = new \App\Models\ValorMetrica();
```

❌ **Evitar:**
```php
// Sin namespace no funcionará dentro de función render
$model = new ValorMetrica();
```

### 7. Escapar HTML

✅ **Hacer:**
```php
<h3><?php echo htmlspecialchars($metrica['nombre']); ?></h3>
// o con función helper
<h3><?php echo e($metrica['nombre']); ?></h3>
```

---

## Debugging

### Problemas Comunes

#### 1. Gráfico no aparece en la lista

**Causa:** Error de sintaxis en el archivo

**Solución:**
```bash
php -l views/components/charts/mi-grafico.php
```

#### 2. Gráfico no renderiza

**Causa:** Error en función render

**Solución:** Agrega debugging:
```php
'render' => function($config, $metrica_data, $area_color, $periodo = null) {
    error_log('Render config: ' . print_r($config, true));
    error_log('Periodo: ' . print_r($periodo, true));
    
    // ... resto del código
}
```

#### 3. Configuración no se guarda

**Causa:** Función process retorna formato incorrecto

**Solución:** Verifica que process retorne array asociativo:
```php
'process' => function($post) {
    $result = [
        'metrica_id' => (int)$post['metrica_id'],
        // ...
    ];
    error_log('Process result: ' . print_r($result, true));
    return $result;
}
```

#### 4. JavaScript no ejecuta

**Causa:** DOMContentLoaded dispara antes de insertar el chart

**Solución:** Siempre usa setTimeout:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        // código aquí
    }, 200);
});
```

---

## Recursos

- **ApexCharts**: https://apexcharts.com/docs/
- **Tabler Icons**: https://tabler-icons.io/
- **Bootstrap 5**: https://getbootstrap.com/docs/5.0/
- **Gráficos existentes**: `views/components/charts/`

---

**¡Crea gráficos increíbles!** 📊✨
