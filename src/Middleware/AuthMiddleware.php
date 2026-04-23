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
            redirect('/public/login.php');
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
            redirect('/public/index.php');
        }
    }
}
