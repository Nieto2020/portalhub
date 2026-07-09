<?php
/**
 * csrf_token.php - Genera y retorna un token CSRF
 * Requiere sesión activa (autenticado).
 */
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

// GET: genera un nuevo token
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, "Método no permitido");
}

// Generar token si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

sendResponse(200, "OK", [
    "csrf_token" => $_SESSION['csrf_token']
]);
