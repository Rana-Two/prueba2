<?php
// Verificar si la sesión ya está iniciada
$session_started = session_status() === PHP_SESSION_ACTIVE;

// Configuración de la sesión - solo si la sesión no está iniciada
if (!$session_started) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}

// Configuración general
define('SITE_NAME', 'Sistema de Asistencia Escolar');
define('SITE_URL', 'http://localhost/scanclass3');

// Configuración de correo
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tu_correo@gmail.com');
define('SMTP_PASSWORD', 'tu_contraseña');
define('SMTP_FROM', 'tu_correo@gmail.com');
define('SMTP_FROM_NAME', 'Sistema de Asistencia');

// Configuración de zona horaria
date_default_timezone_set('America/Lima');

// Función para redireccionar
function redirect($path) {
    header("Location: " . SITE_URL . $path);
    exit();
}

// Función para establecer mensajes
function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

// Función para verificar autenticación
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        setMessage('error', 'Debes iniciar sesión para acceder a esta página');
        redirect('/login.php');
    }
}

// Función para sanitizar entradas
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        setMessage('error', 'Error de seguridad: Token CSRF inválido');
        redirect('/login.php');
    }
    return true;
}
?> 