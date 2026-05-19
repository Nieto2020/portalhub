<?php
require_once __DIR__ . "/../config/config.php";
session_start();
require_once __DIR__ . "/../utils/response.php";

function checkAuth($actualizarActividad = true) {
    if (!isset($_SESSION['id_usuario'])) {
        sendResponse(401, "No autorizado. Inicie sesión Primero.");
    }

    # Bloqueo por cambio de contraseña obligatorio
    if (isset($_SESSION['require_password_change']) && $_SESSION['require_password_change'] == 1) {
        # Permitir solo si la petición es para actualizar la contraseña
        $current_script = basename($_SERVER['PHP_SELF']);
        if ($current_script !== 'update_password.php' && $current_script !== 'logout.php' && $current_script !== 'check_session.php') {
            sendResponse(403, "Cambio de contraseña obligatorio", ["require_password_change" => true]);
        }
    }

    # Control de inactividad
    if (isset($_SESSION['ultima_actividad'])) {
        $inactividad = time() - $_SESSION['ultima_actividad'];
        
        if ($inactividad > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            sendResponse(401, "Sesión expirada por inactividad. Por favor, ingrese de nuevo.");
        }
    }
    
    # Actualizar marca de tiempo de actividad solo si se solicita
    if ($actualizarActividad) {
        $_SESSION['ultima_actividad'] = time();
    }
}

function checkRole($allowedRoles) {
    checkAuth();
    if (!in_array($_SESSION['id_rol'], $allowedRoles)) {
        sendResponse(403, "Acceso denegado. Permisos insuficientes.");
    }
}
?>