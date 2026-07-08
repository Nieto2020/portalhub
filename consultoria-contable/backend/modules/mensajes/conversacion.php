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
        SELECT id_usuario, id_rol 
        FROM usuarios 
        WHERE id_usuario = ?
    ");
    $validarUsuario->execute([$id_otro_usuario]);
    $otroUsuario = $validarUsuario->fetch(PDO::FETCH_ASSOC);

    if (!$otroUsuario) {
        sendResponse(404, "Usuario no encontrado");
    }

    // ── Validar permisos según rol ──
    $id_rol_actual = $_SESSION['id_rol'];
    $id_rol_otro  = $otroUsuario['id_rol'];

    if ($id_rol_actual != ROL_ADMIN) {
        if ($id_rol_actual == ROL_ASESOR) {
            if ($id_rol_otro == ROL_CLIENTE) {
                $checkAsign = $conexion->prepare("
                    SELECT id_asignacion FROM cliente_asesor 
                    WHERE id_cliente = ? AND id_asesor = ? AND estado = 'activo'
                ");
                $checkAsign->execute([$id_otro_usuario, $id_usuario_actual]);
                if (!$checkAsign->fetch()) {
                    sendResponse(403, "No tienes permiso para ver la conversación con este cliente");
                }
            }
        } elseif ($id_rol_actual == ROL_CLIENTE) {
            if ($id_rol_otro != ROL_ASESOR) {
                sendResponse(403, "Solo puedes conversar con tu asesor asignado");
            }
            $checkAsign = $conexion->prepare("
                SELECT id_asignacion FROM cliente_asesor 
                WHERE id_cliente = ? AND id_asesor = ? AND estado = 'activo'
            ");
            $checkAsign->execute([$id_usuario_actual, $id_otro_usuario]);
            if (!$checkAsign->fetch()) {
                sendResponse(403, "No tienes un asesor asignado activo");
            }
        }
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
