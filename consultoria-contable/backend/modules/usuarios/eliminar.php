<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendResponse(405, "Método no permitido");
}

$id_usuario = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_usuario) {
    sendResponse(400, "ID de usuario no proporcionado");
}

try {
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, "Usuario eliminado exitosamente");
    } else {
        sendResponse(404, "Usuario no encontrado");
    }
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>