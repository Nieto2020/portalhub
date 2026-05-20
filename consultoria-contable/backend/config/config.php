<?php
ini_set('session.cookie_httponly', 1);

//proteccion contra formularios, CSRF, request
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $isSecure = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' || !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] !== 'off') {
    $isSecure = true;
}
ini_set('session.cookie_secure', $isSecure ? 1 : 0); 
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

define('DB_HOST', 'localhost');
define('DB_NAME', 'prueba_1');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de sesión (30 minutos de inactividad)
define('SESSION_TIMEOUT', 1800); 

// Otros parámetros de configuración global
date_default_timezone_set('America/Mexico_City');

// Definición de Roles
define('ROL_ADMIN', 1);
define('ROL_ASESOR', 2);
define('ROL_CLIENTE', 3);
?>