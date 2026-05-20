<?php

require_once "../../config/conexion.php";
require_once "../../middleware/auth.php";
require_once "../../utils/response.php";

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, "Método no permitido");
}

$id_usuario_actual = $_SESSION['id_usuario'];
$id_otro_usuario = $_GET['id_usuario'] ?? null;

if (!$id_otro_usuario || !is_numeric($id_otro_usuario)) {
    sendResponse(400, "ID del otro usuario no válido");
}

if ($id_usuario_actual == $id_otro_usuario) {
    sendResponse(400, "No se puede abrir conversación consigo mismo");
}

try {
    $validarUsuario = $conexion->prepare("
        SELECT id_usuario 
        FROM usuarios 
        WHERE id_usuario = ?
    ");
    $validarUsuario->execute([$id_otro_usuario]);

    if (!$validarUsuario->fetch()) {
        sendResponse(404, "Usuario no encontrado");
    }

    $stmt = $conexion->prepare("
        SELECT 
            id_mensaje,
            id_remitente,
            id_destinatario,
            tipo,
            contenido_texto,
            ruta_archivo_adjunto,
            fecha_envio
        FROM mensajes 
        WHERE 
            (id_remitente = ? AND id_destinatario = ?) 
            OR 
            (id_remitente = ? AND id_destinatario = ?)
        ORDER BY fecha_envio ASC
    ");

    $stmt->execute([
        $id_usuario_actual,
        $id_otro_usuario,
        $id_otro_usuario,
        $id_usuario_actual
    ]);

    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Conversación obtenida", $mensajes);

} catch (PDOException $e) {
    sendResponse(500, "Error en el servidor: " . $e->getMessage());
}

?>
