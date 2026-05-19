<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";
require_once "../../utils/validator.php";

// Solo Admin y Asesor pueden crear notificaciones manualmente
checkRole([ROL_ADMIN, ROL_ASESOR]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!validateRequired($data, ['id_usuario_destino', 'tipo_evento', 'mensaje_texto'])) {
    sendResponse(400, "Faltan campos requeridos");
}

$id_usuario_destino = $data['id_usuario_destino'];
$tipo_evento = $data['tipo_evento'];
$mensaje_texto = $data['mensaje_texto'];

try {
    $stmt = $conexion->prepare("INSERT INTO notificaciones (id_usuario_destino, tipo_evento, mensaje_texto) VALUES (?, ?, ?)");
    $stmt->execute([$id_usuario_destino, $tipo_evento, $mensaje_texto]);

    sendResponse(201, "Notificación creada exitosamente");
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>