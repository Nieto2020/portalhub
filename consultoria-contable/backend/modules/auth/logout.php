<?php
require_once "../../config/config.php";
session_start();
session_destroy();

// Limpiar la cookie de sesión
if (ini_get("session.use_cookie")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 4200,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

require_once "../../utils/response.php";

sendResponse(200, "Sesión cerrada exitosamente");
?>