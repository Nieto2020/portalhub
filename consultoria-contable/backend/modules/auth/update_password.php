<?php
require_once "../../middleware/auth.php";
require_once "../../config/conexion.php";
require_once "../../utils/validator.php";


checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['new_password', 'confirm_password'])) {
    sendResponse(400, "Faltan campos requeridos");
}

if ($data['new_password'] !== $data['confirm_password']) {
    sendResponse(400, "Las contraseñas no coinciden");
}

if (strlen($data['new_password']) < 8) {
    sendResponse(400, "La contraseña debe tener al menos 8 caracteres");
}

$id_usuario = $_SESSION['id_usuario'];
$new_password_hash = password_hash($data['new_password'], PASSWORD_BCRYPT);

try {
    $stmt = $conexion->prepare("UPDATE usuarios SET password_hash = ?, require_password_change = 0 WHERE id_usuario = ?");
    $stmt->execute([$new_password_hash, $id_usuario]);

    # Actualizar la sesión
    $_SESSION['require_password_change'] = 0;

    sendResponse(200, "Contraseña actualizada exitosamente");
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>