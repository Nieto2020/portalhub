<?php
require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

# Solo Administradores pueden dar de baja usuarios
checkRole([ROL_ADMIN]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, "Método no permitido");
}

$data = json_decode(file_get_contents("php://input"), true);
$id_usuario = isset($data['id_usuario']) ? $data['id_usuario'] : null;
$accion = isset($data['accion']) ? $data['accion'] : 'desactivar'; # 'desactivar' o 'activar'

if (!$id_usuario) {
    sendResponse(400, "ID de usuario no proporcionado");
}

$nuevo_estado = ($accion === 'activar') ? 'activo' : 'inactivo';

try {
    $stmt = $conexion->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
    $stmt->execute([$nuevo_estado, $id_usuario]);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, "Estado del usuario actualizado a: $nuevo_estado");
    } else {
        sendResponse(404, "Usuario no encontrado o ya se encontraba en ese estado");
    }
} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}
?>