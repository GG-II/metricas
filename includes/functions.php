<?php
/**
 * Funciones auxiliares del sistema
 */

/**
 * Redirigir a una URL
 * 
 * @param string $url URL de destino
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Verificar si el usuario está logueado
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar si el usuario es administrador
 * 
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

/**
 * Proteger una página (requiere login)
 * Redirige a login si no está autenticado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

/**
 * Proteger una página (requiere rol admin)
 * Redirige a dashboard si no es admin
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        $_SESSION['error'] = 'No tienes permisos para acceder a esta página.';
        redirect('/index.php');
    }
}

/**
 * Sanitizar entrada de texto
 * 
 * @param string $data Dato a sanitizar
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 * 
 * @param string $email Email a validar
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash de contraseña
 * 
 * @param string $password Contraseña en texto plano
 * @return string Hash bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verificar contraseña
 * 
 * @param string $password Contraseña en texto plano
 * @param string $hash Hash almacenado
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generar token CSRF
 * 
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 * 
 * @param string $token Token a verificar
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formatear fecha en español
 * 
 * @param string $date Fecha en formato SQL
 * @param string $format Formato de salida
 * @return string
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formatear fecha y hora en español
 * 
 * @param string $datetime Fecha/hora en formato SQL
 * @return string
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '';
    
    return formatDate($datetime, 'd/m/Y H:i');
}

/**
 * Obtener nombre del mes en español
 * 
 * @param int $month Número de mes (1-12)
 * @return string
 */
function getMonthName($month) {
    $months = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    return $months[$month] ?? '';
}

/**
 * Obtener período actual (ejercicio-periodo)
 * Formato: "2026-4" para Abril 2026
 * 
 * @return string
 */
function getCurrentPeriod() {
    return date('Y') . '-' . (int)date('m');
}

/**
 * Obtener nombre del período
 * Ej: "Abril 2026"
 * 
 * @param int $ejercicio Año
 * @param int $periodo Mes (1-12)
 * @return string
 */
function getPeriodName($ejercicio, $periodo) {
    return getMonthName($periodo) . ' ' . $ejercicio;
}

/**
 * Registrar actividad en log
 * 
 * @param string $accion Acción realizada
 * @param string $tabla Tabla afectada (opcional)
 * @param int $registro_id ID del registro afectado (opcional)
 * @param string $descripcion Descripción adicional (opcional)
 */
function logActivity($accion, $tabla = null, $registro_id = null, $descripcion = null) {
    if (!isLoggedIn()) return;
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO log_actividad 
            (usuario_id, accion, tabla_afectada, registro_id, descripcion, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $accion,
            $tabla,
            $registro_id,
            $descripcion,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        // No interrumpir el flujo si falla el log
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Mostrar mensaje flash y luego eliminarlo
 * 
 * @param string $type Tipo de mensaje (success, error, warning, info)
 * @return string|null
 */
function getFlashMessage($type = 'success') {
    if (isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        unset($_SESSION[$type]);
        return $message;
    }
    return null;
}

/**
 * Establecer mensaje flash
 * 
 * @param string $message Mensaje
 * @param string $type Tipo (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION[$type] = $message;
}

/**
 * Procesar upload de foto de perfil
 * 
 * @param array $file Archivo $_FILES['foto_perfil']
 * @return string Nombre del archivo guardado
 * @throws Exception Si hay error en el upload
 */
function procesarUploadFoto($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errores = [
            UPLOAD_ERR_INI_SIZE => 'El archivo supera el tamaño máximo permitido por PHP.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande.',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente. Intenta de nuevo.',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco.',
        ];
        throw new Exception($errores[$file['error']] ?? 'Error desconocido al subir archivo (código: ' . $file['error'] . ')');
    }
    
    // Validar tamaño
    if ($file['size'] > $max_size) {
        throw new Exception('La imagen no debe superar 2MB. Tamaño actual: ' . round($file['size'] / 1024 / 1024, 2) . 'MB');
    }
    
    // Validar tipo por extensión
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        throw new Exception('Solo se permiten archivos JPG, PNG y GIF. Tipo recibido: ' . $ext);
    }
    
    // Validar tipo MIME (más seguro)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime, $allowed_mimes)) {
        throw new Exception('El archivo no es una imagen válida. Tipo detectado: ' . $mime);
    }
    
    // Verificar que la carpeta existe y tiene permisos
    $dir_avatars = BASE_PATH . '/uploads/avatars';
    if (!file_exists($dir_avatars)) {
        if (!mkdir($dir_avatars, 0755, true)) {
            throw new Exception('No se pudo crear la carpeta de avatars. Verifica los permisos.');
        }
    }
    
    if (!is_writable($dir_avatars)) {
        throw new Exception('La carpeta uploads/avatars no tiene permisos de escritura.');
    }
    
    // Generar nombre único
    $nuevo_nombre = uniqid('avatar_' . time() . '_') . '.' . $ext;
    $ruta_destino = $dir_avatars . '/' . $nuevo_nombre;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $ruta_destino)) {
        throw new Exception('Error al mover el archivo a uploads/avatars. Verifica permisos de escritura en la carpeta.');
    }
    
    // Verificar que se creó correctamente
    if (!file_exists($ruta_destino)) {
        throw new Exception('El archivo no se guardó correctamente.');
    }
    
    return $nuevo_nombre;
}

/**
 * Eliminar foto de perfil del servidor
 * 
 * @param string $nombre_archivo Nombre del archivo a eliminar
 * @return bool True si se eliminó correctamente
 */
function eliminarFotoPerfil($nombre_archivo) {
    if (empty($nombre_archivo)) {
        return false;
    }
    
    $ruta = BASE_PATH . '/uploads/avatars/' . $nombre_archivo;
    
    if (file_exists($ruta)) {
        return unlink($ruta);
    }
    
    return false;
}