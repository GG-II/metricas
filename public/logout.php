<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Destruir sesión
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirigir al login
redirect('/login.php');
