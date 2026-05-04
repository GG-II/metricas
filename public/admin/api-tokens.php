<?php
/**
 * Gestor de Tokens de API
 */

session_start();
require_once '../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\ApiAuthService;

AuthMiddleware::handle();

$user = getCurrentUser();
$db = getDB();
$authService = new ApiAuthService($db);

// Generar nuevo token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $nombre = sanitize($_POST['nombre'] ?? 'API Token');
    $expires_days = (int)($_POST['expires_days'] ?? 365);

    $token_data = $authService->generateToken($user['id'], $nombre, $expires_days);

    $_SESSION['new_token'] = $token_data;
    header('Location: api-tokens.php?success=1');
    exit;
}

// Revocar token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revoke'])) {
    $token_id = (int)$_POST['token_id'];
    $authService->revokeToken($token_id);
    header('Location: api-tokens.php?revoked=1');
    exit;
}

// Obtener tokens del usuario
$tokens = $authService->getUserTokens($user['id']);

// Mostrar token recién generado (solo una vez)
$new_token = $_SESSION['new_token'] ?? null;
unset($_SESSION['new_token']);

require_once '../../views/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Tokens de API</h2>
                <div class="text-muted mt-1">Gestiona tus tokens de acceso para la API REST</div>
            </div>
            <div class="col-auto">
                <a href="<?php echo baseUrl('/api'); ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="ti ti-book me-1"></i>
                    Documentación
                </a>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            <?php if ($new_token): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <h4 class="alert-title">✓ Token generado exitosamente</h4>
                <div class="mb-2">
                    <strong>⚠️ Copia este token ahora. No podrás verlo nuevamente:</strong>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control font-monospace" value="<?php echo htmlspecialchars($new_token['token']); ?>" id="new-token" readonly>
                    <button class="btn btn-primary" type="button" onclick="copyToken()">
                        <i class="ti ti-copy"></i> Copiar
                    </button>
                </div>
                <div class="mt-2 small">
                    <strong>Nombre:</strong> <?php echo htmlspecialchars($new_token['nombre']); ?><br>
                    <strong>Expira:</strong> <?php echo date('d/m/Y', strtotime($new_token['expires_at'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['revoked'])): ?>
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                Token revocado exitosamente
            </div>
            <?php endif; ?>

            <div class="row g-3">
                <!-- Generar nuevo token -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Generar Token</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nombre del token</label>
                                    <input type="text" name="nombre" class="form-control" placeholder="Mi App" required>
                                    <div class="form-hint">Identifica para qué usarás este token</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Expiración</label>
                                    <select name="expires_days" class="form-select">
                                        <option value="30">30 días</option>
                                        <option value="90">90 días</option>
                                        <option value="365" selected>1 año</option>
                                        <option value="730">2 años</option>
                                    </select>
                                </div>

                                <button type="submit" name="generate" class="btn btn-primary w-100">
                                    <i class="ti ti-plus me-1"></i>
                                    Generar Token
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista de tokens -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tus Tokens</h3>
                        </div>
                        <div class="card-table table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Token</th>
                                        <th>Creado</th>
                                        <th>Último uso</th>
                                        <th>Usos</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tokens)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No tienes tokens generados
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($tokens as $token): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($token['nombre']); ?></td>
                                            <td>
                                                <code class="small"><?php echo $token['token_preview']; ?>...</code>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo date('d/m/Y', strtotime($token['created_at'])); ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $token['ultimo_uso'] ? date('d/m/Y H:i', strtotime($token['ultimo_uso'])) : '-'; ?>
                                            </td>
                                            <td><?php echo number_format($token['total_usos']); ?></td>
                                            <td>
                                                <?php if (!$token['activo']): ?>
                                                    <span class="badge bg-secondary">Revocado</span>
                                                <?php elseif ($token['expires_at'] && strtotime($token['expires_at']) < time()): ?>
                                                    <span class="badge bg-danger">Expirado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($token['activo']): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Revocar este token?')">
                                                    <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                                    <button type="submit" name="revoke" class="btn btn-sm btn-ghost-danger">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function copyToken() {
    const input = document.getElementById('new-token');
    input.select();
    document.execCommand('copy');

    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="ti ti-check"></i> Copiado';

    setTimeout(() => {
        btn.innerHTML = originalHTML;
    }, 2000);
}
</script>

<?php require_once '../../views/layouts/footer.php'; ?>
