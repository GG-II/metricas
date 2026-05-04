<?php
/**
 * Verificación Integral: Rol area_admin
 * Verifica toda la implementación end-to-end
 */

require_once __DIR__ . '/../vendor/autoload.php';

class AreaAdminIntegrationCheck {
    private $db;

    public function __construct() {
        require_once __DIR__ . '/../config.php';
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public function run() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════╗\n";
        echo "║  VERIFICACIÓN INTEGRAL - Rol area_admin           ║\n";
        echo "╚════════════════════════════════════════════════════╝\n";
        echo "\n";

        $checks = [
            '1. Base de Datos' => 'checkDatabase',
            '2. Usuario de Prueba' => 'checkTestUser',
            '3. Modelos PHP' => 'checkModels',
            '4. Servicios' => 'checkServices',
            '5. Archivos de Vista' => 'checkViews',
            '6. Tests Unitarios' => 'checkTests',
        ];

        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($checks as $name => $method) {
            echo "══════════════════════════════════════════════════\n";
            echo "$name\n";
            echo "══════════════════════════════════════════════════\n\n";

            $result = $this->$method();
            $totalChecks += $result['total'];
            $passedChecks += $result['passed'];

            echo "\n";
        }

        echo "╔════════════════════════════════════════════════════╗\n";
        echo "║  RESUMEN FINAL                                     ║\n";
        echo "╚════════════════════════════════════════════════════╝\n";
        echo "\n";
        printf("Total de verificaciones: %d\n", $totalChecks);
        printf("Pasadas: %d\n", $passedChecks);
        printf("Fallidas: %d\n", $totalChecks - $passedChecks);
        echo "\n";

        if ($passedChecks === $totalChecks) {
            echo "✅ TODAS LAS VERIFICACIONES PASARON\n";
            echo "✅ Sistema listo para usar rol area_admin\n";
        } else {
            echo "⚠️  ALGUNAS VERIFICACIONES FALLARON\n";
            echo "⚠️  Revisar errores arriba\n";
        }

        echo "\n";

        return $passedChecks === $totalChecks;
    }

    private function checkDatabase() {
        $checks = [];
        $passed = 0;
        $total = 0;

        // Check 1: ENUM incluye area_admin
        $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
        $column = $stmt->fetch();
        $enumValues = $column['Type'];

        $total++;
        if (strpos($enumValues, 'area_admin') !== false) {
            echo "✅ ENUM de rol incluye 'area_admin'\n";
            $passed++;
        } else {
            echo "❌ ENUM de rol NO incluye 'area_admin'\n";
        }

        // Check 2: Campo area_id existe
        $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'area_id'");
        $column = $stmt->fetch();

        $total++;
        if ($column) {
            echo "✅ Campo 'area_id' existe en tabla usuarios\n";
            $passed++;
        } else {
            echo "❌ Campo 'area_id' NO existe en tabla usuarios\n";
        }

        // Check 3: Índice en area_id
        $stmt = $this->db->query("SHOW INDEX FROM usuarios WHERE Column_name = 'area_id'");
        $index = $stmt->fetch();

        $total++;
        if ($index) {
            echo "✅ Índice en 'area_id' existe\n";
            $passed++;
        } else {
            echo "⚠️  Índice en 'area_id' no existe (recomendado pero no crítico)\n";
        }

        return ['total' => $total, 'passed' => $passed];
    }

    private function checkTestUser() {
        $checks = [];
        $passed = 0;
        $total = 0;

        // Check 1: Usuario existe
        $stmt = $this->db->query("
            SELECT u.*, a.nombre as area_nombre, d.nombre as dept_nombre
            FROM usuarios u
            LEFT JOIN areas a ON u.area_id = a.id
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE u.username = 'area_admin_test'
        ");
        $user = $stmt->fetch();

        $total++;
        if ($user) {
            echo "✅ Usuario 'area_admin_test' existe\n";
            $passed++;

            // Sub-check: tiene rol correcto
            $total++;
            if ($user['rol'] === 'area_admin') {
                echo "✅ Usuario tiene rol 'area_admin'\n";
                $passed++;
            } else {
                echo "❌ Usuario NO tiene rol 'area_admin': " . $user['rol'] . "\n";
            }

            // Sub-check: tiene area_id
            $total++;
            if (!empty($user['area_id'])) {
                echo "✅ Usuario tiene area_id asignado: " . $user['area_nombre'] . "\n";
                $passed++;
            } else {
                echo "❌ Usuario NO tiene area_id asignado\n";
            }

            // Sub-check: tiene departamento_id
            $total++;
            if (!empty($user['departamento_id'])) {
                echo "✅ Usuario tiene departamento_id asignado: " . $user['dept_nombre'] . "\n";
                $passed++;
            } else {
                echo "❌ Usuario NO tiene departamento_id asignado\n";
            }

            // Sub-check: área existe y está activa
            $total++;
            if ($user['area_nombre']) {
                $stmt = $this->db->prepare("SELECT activo FROM areas WHERE id = ?");
                $stmt->execute([$user['area_id']]);
                $area = $stmt->fetch();

                if ($area && $area['activo']) {
                    echo "✅ Área asignada existe y está activa\n";
                    $passed++;
                } else {
                    echo "❌ Área asignada no existe o está inactiva\n";
                }
            } else {
                echo "❌ Área asignada no encontrada\n";
            }

        } else {
            echo "❌ Usuario 'area_admin_test' NO existe\n";
        }

        return ['total' => $total, 'passed' => $passed];
    }

    private function checkModels() {
        $passed = 0;
        $total = 0;

        // Check 1: Clase Usuario existe
        $total++;
        if (class_exists('App\Models\Usuario')) {
            echo "✅ Clase App\Models\Usuario existe\n";
            $passed++;

            $usuarioModel = new App\Models\Usuario();

            // Check métodos
            $methods = ['validateAreaAdmin', 'canAccessArea', 'canEditArea', 'getAccessibleAreas', 'canAccessGlobal'];
            foreach ($methods as $method) {
                $total++;
                if (method_exists($usuarioModel, $method)) {
                    echo "✅ Método Usuario::$method() existe\n";
                    $passed++;
                } else {
                    echo "❌ Método Usuario::$method() NO existe\n";
                }
            }
        } else {
            echo "❌ Clase App\Models\Usuario NO existe\n";
        }

        return ['total' => $total, 'passed' => $passed];
    }

    private function checkServices() {
        $passed = 0;
        $total = 0;

        // Check 1: PermissionService existe
        $total++;
        if (class_exists('App\Services\PermissionService')) {
            echo "✅ Clase App\Services\PermissionService existe\n";
            $passed++;

            // Check métodos
            $methods = ['canViewArea', 'canEditArea', 'getAreasPermitidas', 'canCreateInArea'];
            foreach ($methods as $method) {
                $total++;
                if (method_exists('App\Services\PermissionService', $method)) {
                    echo "✅ Método PermissionService::$method() existe\n";
                    $passed++;
                } else {
                    echo "❌ Método PermissionService::$method() NO existe\n";
                }
            }
        } else {
            echo "❌ Clase App\Services\PermissionService NO existe\n";
        }

        return ['total' => $total, 'passed' => $passed];
    }

    private function checkViews() {
        $passed = 0;
        $total = 0;

        // Check archivos principales
        $files = [
            'public/index.php' => 'Controlador principal',
            'public/admin/usuarios.php' => 'Administración de usuarios',
        ];

        foreach ($files as $file => $desc) {
            $total++;
            $fullPath = __DIR__ . '/../' . $file;
            if (file_exists($fullPath)) {
                echo "✅ Archivo $desc existe\n";
                $passed++;

                // Check contenido para area_admin
                $content = file_get_contents($fullPath);
                $total++;
                if (strpos($content, 'area_admin') !== false) {
                    echo "✅ Archivo contiene referencias a 'area_admin'\n";
                    $passed++;
                } else {
                    echo "⚠️  Archivo NO contiene referencias a 'area_admin' (puede ser normal)\n";
                }
            } else {
                echo "❌ Archivo $desc NO existe\n";
            }
        }

        return ['total' => $total, 'passed' => $passed];
    }

    private function checkTests() {
        $passed = 0;
        $total = 0;

        // Check 1: Archivo de test existe
        $total++;
        $testFile = __DIR__ . '/AreaAdminRoleTest.php';
        if (file_exists($testFile)) {
            echo "✅ Archivo de test existe\n";
            $passed++;

            // Check 2: Ejecutar tests
            $total++;
            ob_start();
            $result = require $testFile;
            $output = ob_get_clean();

            if ($result === 0 || strpos($output, '7 passed') !== false) {
                echo "✅ Tests unitarios pasando (7/7)\n";
                $passed++;
            } else {
                echo "❌ Tests unitarios fallando\n";
                echo "Output:\n" . $output . "\n";
            }
        } else {
            echo "❌ Archivo de test NO existe\n";
        }

        return ['total' => $total, 'passed' => $passed];
    }
}

// Ejecutar verificación
$check = new AreaAdminIntegrationCheck();
$success = $check->run();

exit($success ? 0 : 1);
