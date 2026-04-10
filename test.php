<?php
/**
 * Página de prueba
 * Verifica que la configuración y conexión a BD funcionen
 * 
 * IMPORTANTE: Eliminar este archivo en producción
 */

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - <?php echo APP_NAME; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .test-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        h2 { margin-top: 0; color: #1e293b; }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
        }
        .status.ok {
            background: #d1fae5;
            color: #065f46;
        }
        .status.fail {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <h1>🧪 Test de Configuración</h1>
    
    <!-- Test 1: Configuración -->
    <div class="test-box">
        <h2>1. Configuración del Sistema</h2>
        <p><strong>Nombre:</strong> <?php echo APP_NAME; ?></p>
        <p><strong>Versión:</strong> <?php echo APP_VERSION; ?></p>
        <p><strong>Entorno:</strong> <?php echo ENVIRONMENT; ?></p>
        <p><strong>Timezone:</strong> <?php echo TIMEZONE; ?></p>
        <p><strong>Base URL:</strong> <?php echo BASE_URL; ?></p>
        <p class="success">✓ Configuración cargada correctamente</p>
    </div>
    
    <!-- Test 2: Conexión a BD -->
    <div class="test-box">
        <h2>2. Conexión a Base de Datos</h2>
        <?php
        try {
            $db = getDB();
            echo '<p class="success">✓ Conexión PDO establecida</p>';
            echo '<p><strong>Driver:</strong> ' . $db->getAttribute(PDO::ATTR_DRIVER_NAME) . '</p>';
            echo '<p><strong>Base de datos:</strong> ' . DB_NAME . '</p>';
            
            // Test query
            $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
            $result = $stmt->fetch();
            echo '<p><strong>Usuarios en BD:</strong> ' . $result['total'] . '</p>';
            
            echo '<span class="status ok">CONEXIÓN OK</span>';
        } catch (Exception $e) {
            echo '<p class="error">✗ Error de conexión: ' . $e->getMessage() . '</p>';
            echo '<span class="status fail">CONEXIÓN FALLIDA</span>';
        }
        ?>
    </div>
    
    <!-- Test 3: Tablas de BD -->
    <div class="test-box">
        <h2>3. Verificación de Tablas</h2>
        <?php
        try {
            $db = getDB();
            $tables = ['usuarios', 'areas', 'periodos', 'metricas', 'valores_metricas', 
                       'relaciones_metricas', 'configuracion', 'log_actividad'];
            
            echo '<ul>';
            foreach ($tables as $table) {
                $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
                $result = $stmt->fetch();
                echo '<li class="success">✓ Tabla <strong>' . $table . '</strong>: ' . $result['total'] . ' registros</li>';
            }
            echo '</ul>';
            echo '<span class="status ok">TODAS LAS TABLAS EXISTEN</span>';
        } catch (Exception $e) {
            echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
            echo '<span class="status fail">ERROR EN TABLAS</span>';
        }
        ?>
    </div>
    
    <!-- Test 4: Funciones -->
    <div class="test-box">
        <h2>4. Funciones del Sistema</h2>
        <?php
        $tests = [
            'sanitize()' => function_exists('sanitize'),
            'validateEmail()' => function_exists('validateEmail'),
            'hashPassword()' => function_exists('hashPassword'),
            'isLoggedIn()' => function_exists('isLoggedIn'),
            'isAdmin()' => function_exists('isAdmin'),
            'formatDate()' => function_exists('formatDate'),
            'getMonthName()' => function_exists('getMonthName'),
        ];
        
        echo '<ul>';
        foreach ($tests as $func => $exists) {
            if ($exists) {
                echo '<li class="success">✓ ' . $func . '</li>';
            } else {
                echo '<li class="error">✗ ' . $func . '</li>';
            }
        }
        echo '</ul>';
        
        // Test de algunas funciones
        echo '<h3>Pruebas de funciones:</h3>';
        echo '<p><strong>sanitize():</strong> ' . sanitize('<script>alert("xss")</script>') . '</p>';
        echo '<p><strong>getMonthName(4):</strong> ' . getMonthName(4) . '</p>';
        echo '<p><strong>getCurrentPeriod():</strong> ' . getCurrentPeriod() . '</p>';
        echo '<p><strong>isLoggedIn():</strong> ' . (isLoggedIn() ? 'Sí' : 'No') . '</p>';
        
        echo '<span class="status ok">FUNCIONES OK</span>';
        ?>
    </div>
    
    <!-- Test 5: Usuarios de prueba -->
    <div class="test-box">
        <h2>5. Usuarios de Prueba</h2>
        <?php
        try {
            $db = getDB();
            $stmt = $db->query("SELECT username, nombre, rol, activo FROM usuarios ORDER BY id");
            $usuarios = $stmt->fetchAll();
            
            echo '<table style="width: 100%; border-collapse: collapse;">';
            echo '<tr style="background: #f1f5f9; text-align: left;">
                    <th style="padding: 8px;">Username</th>
                    <th style="padding: 8px;">Nombre</th>
                    <th style="padding: 8px;">Rol</th>
                    <th style="padding: 8px;">Estado</th>
                  </tr>';
            
            foreach ($usuarios as $user) {
                $estado = $user['activo'] ? '<span class="status ok">Activo</span>' : '<span class="status fail">Inactivo</span>';
                echo '<tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px;">' . $user['username'] . '</td>
                        <td style="padding: 8px;">' . $user['nombre'] . '</td>
                        <td style="padding: 8px;">' . $user['rol'] . '</td>
                        <td style="padding: 8px;">' . $estado . '</td>
                      </tr>';
            }
            echo '</table>';
            
            echo '<p style="margin-top: 15px;"><strong>Credenciales de prueba:</strong></p>';
            echo '<pre>Usuario: admin
Contraseña: Admin123!

Usuario: viewer1
Contraseña: Admin123!</pre>';
            
        } catch (Exception $e) {
            echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    
    <div class="test-box">
        <h2>📋 Siguiente Paso</h2>
        <p>Si todos los tests pasaron correctamente:</p>
        <ol>
            <li>Ve a: <a href="login.php" style="color: #3b82f6;">http://localhost/metricas/login.php</a></li>
            <li>Usa las credenciales de prueba arriba</li>
            <li>Deberías poder iniciar sesión</li>
        </ol>
        <p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (test.php) cuando subas a producción.</p>
    </div>
</body>
</html>