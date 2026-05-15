<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);
$id_notificacion = isset($data['id_notificacion']) ? $data['id_notificacion'] : null;

if (!$id_notificacion) {
    sendResponse(400, "ID de notificación no proporcionado");
}

try {
    $stmt = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ? AND id_usuario_destino = ?");
    $stmt->execute([$id_notificacion, $_SESSION['id_usuario']]);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, "Notificación marcada como leída");
    } else {
        sendResponse(404, "Notificación no encontrada o ya leída");
    }
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>