<?php
namespace App\Middleware;

/**
 * Middleware de autenticación
 * Verifica que el usuario esté logueado
 */
class AuthMiddleware {

    /**
     * Verificar autenticación
     */
    public static function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isLoggedIn()) {
            redirect('/login.php');
        }

        // 🔒 SEGURIDAD: Verificar si debe cambiar contraseña
        if (isset($_SESSION['debe_cambiar_password']) && $_SESSION['debe_cambiar_password'] === true) {
            // Permitir acceso solo a cambiar-password.php y logout.php
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page !== 'cambiar-password.php' && $current_page !== 'logout.php') {
                redirect('/cambiar-password.php');
            }
        }
    }

    /**
     * Verificar que NO esté autenticado (para login)
     */
    public static function guest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isLoggedIn()) {
            redirect('/index.php');
        }
    }
}
