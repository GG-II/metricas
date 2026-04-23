<?php
/**
 * Documentación de API REST
 */

$docs = [
    'name' => 'Sistema de Métricas API',
    'version' => '1.0',
    'base_url' => '/metricas/api',
    'authentication' => [
        'type' => 'Bearer Token',
        'header' => 'Authorization: Bearer {your-token}',
        'note' => 'Genera tu token en el panel de usuario'
    ],
    'endpoints' => [
        [
            'resource' => 'Métricas',
            'routes' => [
                'GET /metricas' => 'Listar todas las métricas',
                'GET /metricas/{id}' => 'Obtener una métrica',
                'POST /metricas' => 'Crear métrica (admin)',
                'PUT /metricas/{id}' => 'Actualizar métrica (admin)',
                'DELETE /metricas/{id}' => 'Eliminar métrica (admin)'
            ]
        ],
        [
            'resource' => 'Valores',
            'routes' => [
                'GET /valores?metrica_id={id}&periodo_id={id}' => 'Obtener valores',
                'GET /valores/{id}' => 'Obtener un valor',
                'POST /valores' => 'Crear/actualizar valor',
                'GET /valores/historico?metrica_id={id}&periodos={n}' => 'Histórico de valores'
            ]
        ],
        [
            'resource' => 'Períodos',
            'routes' => [
                'GET /periodos' => 'Listar períodos',
                'GET /periodos/{id}' => 'Obtener período',
                'GET /periodos/actual' => 'Obtener período actual'
            ]
        ],
        [
            'resource' => 'Áreas',
            'routes' => [
                'GET /areas' => 'Listar áreas',
                'GET /areas/{id}' => 'Obtener área',
                'GET /areas/{id}/metricas' => 'Métricas de un área'
            ]
        ],
        [
            'resource' => 'Departamentos',
            'routes' => [
                'GET /departamentos' => 'Listar departamentos',
                'GET /departamentos/{id}' => 'Obtener departamento',
                'GET /departamentos/{id}/areas' => 'Áreas de un departamento'
            ]
        ],
        [
            'resource' => 'Metas',
            'routes' => [
                'GET /metas?metrica_id={id}' => 'Metas de una métrica',
                'POST /metas' => 'Crear meta (admin)',
                'PUT /metas/{id}' => 'Actualizar meta (admin)',
                'DELETE /metas/{id}' => 'Eliminar meta (admin)'
            ]
        ]
    ],
    'examples' => [
        [
            'title' => 'Listar métricas de un área',
            'request' => 'GET /metricas?area_id=1',
            'response' => [
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'nombre' => 'Proyectos Activos',
                        'area_id' => 1,
                        'unidad' => 'proyectos'
                    ]
                ]
            ]
        ],
        [
            'title' => 'Crear valor de métrica',
            'request' => 'POST /valores',
            'body' => [
                'metrica_id' => 1,
                'periodo_id' => 5,
                'valor' => 18
            ],
            'response' => [
                'success' => true,
                'data' => [
                    'id' => 123,
                    'metrica_id' => 1,
                    'periodo_id' => 5,
                    'valor_numero' => 18
                ]
            ]
        ]
    ],
    'rate_limiting' => [
        'enabled' => false,
        'limit' => 1000,
        'period' => '1 hour',
        'note' => 'Implementación futura'
    ]
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $docs['name']; ?> - Documentación</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
        .header { background: #3b82f6; color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .section { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { margin: 0; }
        h2 { color: #3b82f6; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        code { background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-size: 0.9em; }
        pre { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; }
        .endpoint { margin: 15px 0; padding: 12px; background: #f8fafc; border-left: 3px solid #3b82f6; }
        .method { display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: 600; margin-right: 10px; }
        .get { background: #10b981; color: white; }
        .post { background: #3b82f6; color: white; }
        .put { background: #f59e0b; color: white; }
        .delete { background: #ef4444; color: white; }
        .badge { display: inline-block; padding: 4px 10px; background: #e2e8f0; border-radius: 12px; font-size: 0.85em; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo $docs['name']; ?></h1>
        <p>Versión <?php echo $docs['version']; ?> | Base URL: <code><?php echo $docs['base_url']; ?></code></p>
    </div>

    <div class="section">
        <h2>🔐 Autenticación</h2>
        <p><strong>Tipo:</strong> <?php echo $docs['authentication']['type']; ?></p>
        <p><strong>Header:</strong> <code><?php echo $docs['authentication']['header']; ?></code></p>
        <p><span class="badge">ℹ️ <?php echo $docs['authentication']['note']; ?></span></p>
    </div>

    <div class="section">
        <h2>📡 Endpoints</h2>
        <?php foreach ($docs['endpoints'] as $endpoint): ?>
            <h3><?php echo $endpoint['resource']; ?></h3>
            <?php foreach ($endpoint['routes'] as $route => $description): ?>
                <div class="endpoint">
                    <?php
                    list($method, $path) = explode(' ', $route);
                    $method_class = strtolower($method);
                    ?>
                    <span class="method <?php echo $method_class; ?>"><?php echo $method; ?></span>
                    <code><?php echo $path; ?></code>
                    <div style="margin-top: 5px; color: #64748b;"><?php echo $description; ?></div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>💡 Ejemplos</h2>
        <?php foreach ($docs['examples'] as $example): ?>
            <h3><?php echo $example['title']; ?></h3>
            <p><strong>Request:</strong></p>
            <pre><?php echo $example['request']; ?>
<?php if (isset($example['body'])): ?>

Body:
<?php echo json_encode($example['body'], JSON_PRETTY_PRINT); ?>
<?php endif; ?></pre>
            <p><strong>Response:</strong></p>
            <pre><?php echo json_encode($example['response'], JSON_PRETTY_PRINT); ?></pre>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>⚡ Rate Limiting</h2>
        <p><strong>Estado:</strong> <?php echo $docs['rate_limiting']['enabled'] ? 'Activo' : 'Desactivado'; ?></p>
        <?php if ($docs['rate_limiting']['enabled']): ?>
            <p>Límite: <?php echo $docs['rate_limiting']['limit']; ?> requests por <?php echo $docs['rate_limiting']['period']; ?></p>
        <?php else: ?>
            <p><span class="badge">ℹ️ <?php echo $docs['rate_limiting']['note']; ?></span></p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>🚀 Quick Start</h2>
        <pre>curl -X GET "<?php echo $docs['base_url']; ?>/metricas" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"</pre>
    </div>
</body>
</html>
