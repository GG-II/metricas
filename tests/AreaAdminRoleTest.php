<?php
/**
 * Tests para el rol area_admin
 * Verifica permisos, validaciones y funcionalidad del nuevo rol
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Usuario;
use App\Services\PermissionService;

class AreaAdminRoleTest {
    private $db;
    private $usuarioModel;
    private $testUser;

    public function __construct() {
        require_once __DIR__ . '/../config.php';
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
        $this->usuarioModel = new Usuario();
    }

    public function run() {
        echo "\n==============================================\n";
        echo "TESTS: Rol area_admin\n";
        echo "==============================================\n\n";

        $tests = [
            'testUsuarioAreaAdminExiste',
            'testValidacionAreaAdmin',
            'testCanAccessArea',
            'testCanEditArea',
            'testCanNotAccessOtherAreas',
            'testGetAccessibleAreas',
            'testPermissionService',
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $test) {
            try {
                $this->$test();
                echo "✅ PASS: $test\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ FAIL: $test\n";
                echo "   Error: " . $e->getMessage() . "\n";
                $failed++;
            }
        }

        echo "\n==============================================\n";
        echo "Resultado: $passed passed, $failed failed\n";
        echo "==============================================\n\n";

        return $failed === 0;
    }

    private function testUsuarioAreaAdminExiste() {
        $stmt = $this->db->query("
            SELECT u.*, a.nombre as area_nombre
            FROM usuarios u
            LEFT JOIN areas a ON u.area_id = a.id
            WHERE u.username = 'area_admin_test'
        ");
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Usuario area_admin_test no existe");
        }

        if ($user['rol'] !== 'area_admin') {
            throw new Exception("Usuario no tiene rol area_admin: " . $user['rol']);
        }

        if (empty($user['area_id'])) {
            throw new Exception("Usuario area_admin no tiene area_id asignado");
        }

        if (empty($user['departamento_id'])) {
            throw new Exception("Usuario area_admin no tiene departamento_id asignado");
        }

        $this->testUser = $user;
    }

    private function testValidacionAreaAdmin() {
        // Test 1: area_admin CON area_id debe ser válido
        $dataValido = [
            'rol' => 'area_admin',
            'area_id' => 1
        ];
        $result = $this->usuarioModel->validateAreaAdmin($dataValido);
        if (!$result['valid']) {
            throw new Exception("Validación falló con area_id presente");
        }

        // Test 2: area_admin SIN area_id debe ser inválido
        $dataInvalido = [
            'rol' => 'area_admin',
            'area_id' => null
        ];
        $result = $this->usuarioModel->validateAreaAdmin($dataInvalido);
        if ($result['valid']) {
            throw new Exception("Validación pasó sin area_id");
        }

        // Test 3: otros roles no deben requerir area_id
        $dataOtro = [
            'rol' => 'dept_admin',
            'area_id' => null
        ];
        $result = $this->usuarioModel->validateAreaAdmin($dataOtro);
        if (!$result['valid']) {
            throw new Exception("Validación falló para dept_admin sin area_id");
        }
    }

    private function testCanAccessArea() {
        // area_admin debe poder acceder a su área asignada
        $canAccess = $this->usuarioModel->canAccessArea($this->testUser, $this->testUser['area_id']);
        if (!$canAccess) {
            throw new Exception("area_admin no puede acceder a su área asignada");
        }
    }

    private function testCanEditArea() {
        // area_admin debe poder editar su área asignada
        $canEdit = $this->usuarioModel->canEditArea($this->testUser, $this->testUser['area_id']);
        if (!$canEdit) {
            throw new Exception("area_admin no puede editar su área asignada");
        }
    }

    private function testCanNotAccessOtherAreas() {
        // Obtener un área diferente a la asignada
        $stmt = $this->db->prepare("
            SELECT id FROM areas
            WHERE id != ? AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$this->testUser['area_id']]);
        $otraArea = $stmt->fetch();

        if (!$otraArea) {
            // Si no hay otras áreas, el test pasa
            return;
        }

        // area_admin NO debe poder acceder a otras áreas
        $canAccess = $this->usuarioModel->canAccessArea($this->testUser, $otraArea['id']);
        if ($canAccess) {
            throw new Exception("area_admin puede acceder a áreas no asignadas");
        }

        // area_admin NO debe poder editar otras áreas
        $canEdit = $this->usuarioModel->canEditArea($this->testUser, $otraArea['id']);
        if ($canEdit) {
            throw new Exception("area_admin puede editar áreas no asignadas");
        }
    }

    private function testGetAccessibleAreas() {
        // area_admin solo debe ver su área asignada
        $areas = $this->usuarioModel->getAccessibleAreas($this->testUser);

        if (count($areas) !== 1) {
            throw new Exception("area_admin ve " . count($areas) . " áreas (debería ver solo 1)");
        }

        if ($areas[0]['id'] != $this->testUser['area_id']) {
            throw new Exception("área accesible no coincide con área asignada");
        }
    }

    private function testPermissionService() {
        // Test canViewArea
        $canView = PermissionService::canViewArea($this->testUser, $this->testUser['area_id']);
        if (!$canView) {
            throw new Exception("PermissionService::canViewArea falló");
        }

        // Test canEditArea
        $canEdit = PermissionService::canEditArea($this->testUser, $this->testUser['area_id']);
        if (!$canEdit) {
            throw new Exception("PermissionService::canEditArea falló");
        }

        // Test canCreateInArea
        $canCreate = PermissionService::canCreateInArea($this->testUser, $this->testUser['area_id']);
        if (!$canCreate) {
            throw new Exception("PermissionService::canCreateInArea falló");
        }

        // Test getAreasPermitidas
        $areas = PermissionService::getAreasPermitidas($this->testUser, null);
        if (count($areas) !== 1) {
            throw new Exception("PermissionService::getAreasPermitidas retornó " . count($areas) . " áreas");
        }

        // Test canAccessGlobal (debe ser false)
        $canAccessGlobal = $this->usuarioModel->canAccessGlobal($this->testUser);
        if ($canAccessGlobal) {
            throw new Exception("area_admin puede acceder a áreas globales");
        }
    }
}

// Ejecutar tests
$test = new AreaAdminRoleTest();
$success = $test->run();

exit($success ? 0 : 1);
