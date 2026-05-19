<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // se deja en 0 para pruebas Locales
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